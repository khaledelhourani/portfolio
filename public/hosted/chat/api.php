<?php
// =============================================
// api.php - Main API Handler
// =============================================
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    // ─── AUTH ───────────────────────────────────
    case 'register':   handleRegister(); break;
    case 'login':      handleLogin();    break;
    case 'logout':     handleLogout();   break;
    case 'me':         handleMe();       break;

    // ─── MESSAGES ───────────────────────────────
    case 'send_message':      handleSendMessage();    break;
    case 'get_messages':      handleGetMessages();    break;
    case 'get_conversations': handleGetConversations(); break;
    case 'mark_read':         handleMarkRead();       break;
    case 'poll_messages':     handlePollMessages();   break;

    // ─── USERS ──────────────────────────────────
    case 'search_users':   handleSearchUsers();  break;
    case 'add_friend':     handleAddFriend();    break;
    case 'get_friends':    handleGetFriends();   break;
    case 'update_profile': handleUpdateProfile(); break;

    // ─── GROUPS ─────────────────────────────────
    case 'create_group':    handleCreateGroup();   break;
    case 'get_groups':      handleGetGroups();     break;
    case 'join_group':      handleJoinGroup();     break;
    case 'group_members':   handleGroupMembers();  break;

    default:
        jsonResponse(['error' => 'Unknown action'], 400);
}

// =============================================
// AUTH HANDLERS
// =============================================

function handleRegister() {
    $username = clean($_POST['username'] ?? '');
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$email || !$password)
        jsonResponse(['error' => 'جميع الحقول مطلوبة'], 400);

    if (strlen($password) < 6)
        jsonResponse(['error' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'], 400);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        jsonResponse(['error' => 'البريد الإلكتروني غير صحيح'], 400);

    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) jsonResponse(['error' => 'المستخدم أو البريد موجود مسبقاً'], 409);

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hash]);
    $userId = $db->lastInsertId();

    $_SESSION['user_id'] = $userId;
    jsonResponse(['success' => true, 'user_id' => $userId, 'username' => $username]);
}

function handleLogin() {
    $login    = clean($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$login || !$password)
        jsonResponse(['error' => 'أدخل بيانات الدخول'], 400);

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password']))
        jsonResponse(['error' => 'بيانات الدخول غير صحيحة'], 401);

    $_SESSION['user_id'] = $user['id'];
    updateUserStatus($user['id'], 'online');

    jsonResponse([
        'success'  => true,
        'user_id'  => $user['id'],
        'username' => $user['username'],
        'avatar'   => $user['avatar'],
        'email'    => $user['email']
    ]);
}

function handleLogout() {
    if (isLoggedIn()) updateUserStatus($_SESSION['user_id'], 'offline');
    session_destroy();
    jsonResponse(['success' => true]);
}

function handleMe() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);
    $user = getCurrentUser();
    jsonResponse($user);
}

// =============================================
// MESSAGE HANDLERS
// =============================================

function handleSendMessage() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $senderId   = $_SESSION['user_id'];
    $receiverId = intval($_POST['receiver_id'] ?? 0);
    $groupId    = intval($_POST['group_id'] ?? 0);
    $content    = clean($_POST['content'] ?? '');
    $type       = 'text';
    $filePath   = null;

    if (!$content && empty($_FILES['file']))
        jsonResponse(['error' => 'الرسالة فارغة'], 400);

    if (!$receiverId && !$groupId)
        jsonResponse(['error' => 'حدد المستلم'], 400);

    // Handle file upload
    if (!empty($_FILES['file']['name'])) {
        $uploadResult = handleFileUpload($_FILES['file']);
        if (isset($uploadResult['error'])) jsonResponse($uploadResult, 400);
        $filePath = $uploadResult['path'];
        $type = $uploadResult['type'];
        if (!$content) $content = $_FILES['file']['name'];
    }

    $db = getDB();

    // Check group membership
    if ($groupId) {
        $stmt = $db->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->execute([$groupId, $senderId]);
        if (!$stmt->fetch()) jsonResponse(['error' => 'لست عضواً في المجموعة'], 403);
    }

    $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, group_id, content, type, file_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $senderId,
        $receiverId ?: null,
        $groupId ?: null,
        $content,
        $type,
        $filePath
    ]);

    jsonResponse([
        'success'    => true,
        'message_id' => $db->lastInsertId(),
        'content'    => $content,
        'type'       => $type,
        'file_path'  => $filePath,
        'created_at' => date('Y-m-d H:i:s')
    ]);
}

function handleGetMessages() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $userId     = $_SESSION['user_id'];
    $chatWith   = intval($_GET['with'] ?? 0);
    $groupId    = intval($_GET['group_id'] ?? 0);
    $lastId     = intval($_GET['last_id'] ?? 0);
    $limit      = 50;

    $db = getDB();

    if ($groupId) {
        $stmt = $db->prepare("
            SELECT m.*, u.username, u.avatar
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.group_id = ? AND m.id > ?
            ORDER BY m.created_at ASC
            LIMIT ?
        ");
        $stmt->execute([$groupId, $lastId, $limit]);
    } elseif ($chatWith) {
        $stmt = $db->prepare("
            SELECT m.*, u.username, u.avatar
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
            AND m.group_id IS NULL AND m.id > ?
            ORDER BY m.created_at ASC
            LIMIT ?
        ");
        $stmt->execute([$userId, $chatWith, $chatWith, $userId, $lastId, $limit]);

        // Mark as read
        $db->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0")
           ->execute([$chatWith, $userId]);
    } else {
        jsonResponse(['error' => 'حدد المحادثة'], 400);
    }

    jsonResponse(['messages' => $stmt->fetchAll()]);
}

function handleGetConversations() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $userId = $_SESSION['user_id'];
    $db = getDB();

    // Personal conversations
    $stmt = $db->prepare("
        SELECT 
            u.id, u.username, u.avatar, u.status,
            m.content AS last_message, m.created_at AS last_time,
            m.sender_id AS last_sender,
            (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) AS unread_count
        FROM users u
        JOIN messages m ON (
            (m.sender_id = ? AND m.receiver_id = u.id) OR
            (m.sender_id = u.id AND m.receiver_id = ?)
        ) AND m.group_id IS NULL
        WHERE u.id != ?
        AND m.created_at = (
            SELECT MAX(m2.created_at)
            FROM messages m2
            WHERE ((m2.sender_id = ? AND m2.receiver_id = u.id) OR
                   (m2.sender_id = u.id AND m2.receiver_id = ?))
            AND m2.group_id IS NULL
        )
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
    $personal = $stmt->fetchAll();

    // Group conversations
    $stmt = $db->prepare("
        SELECT 
            g.id, g.name AS username, g.avatar, 'group' AS type,
            m.content AS last_message, m.created_at AS last_time,
            (SELECT COUNT(*) FROM messages WHERE group_id = g.id AND sender_id != ? AND is_read = 0) AS unread_count
        FROM groups_table g
        JOIN group_members gm ON g.id = gm.group_id AND gm.user_id = ?
        LEFT JOIN messages m ON m.group_id = g.id
        AND m.created_at = (SELECT MAX(m2.created_at) FROM messages m2 WHERE m2.group_id = g.id)
        ORDER BY last_time DESC
    ");
    $stmt->execute([$userId, $userId]);
    $groups = $stmt->fetchAll();

    foreach ($groups as &$g) $g['is_group'] = true;

    jsonResponse(['personal' => $personal, 'groups' => $groups]);
}

function handleMarkRead() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $userId   = $_SESSION['user_id'];
    $senderId = intval($_POST['sender_id'] ?? 0);
    $groupId  = intval($_POST['group_id'] ?? 0);
    $db = getDB();

    if ($senderId) {
        $db->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?")
           ->execute([$senderId, $userId]);
    } elseif ($groupId) {
        $db->prepare("UPDATE messages SET is_read = 1 WHERE group_id = ? AND sender_id != ?")
           ->execute([$groupId, $userId]);
    }

    jsonResponse(['success' => true]);
}

function handlePollMessages() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $userId   = $_SESSION['user_id'];
    $lastId   = intval($_GET['last_id'] ?? 0);
    $chatWith = intval($_GET['with'] ?? 0);
    $groupId  = intval($_GET['group_id'] ?? 0);
    $db = getDB();

    if ($groupId) {
        $stmt = $db->prepare("
            SELECT m.*, u.username, u.avatar
            FROM messages m JOIN users u ON m.sender_id = u.id
            WHERE m.group_id = ? AND m.id > ?
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$groupId, $lastId]);
    } elseif ($chatWith) {
        $stmt = $db->prepare("
            SELECT m.*, u.username, u.avatar
            FROM messages m JOIN users u ON m.sender_id = u.id
            WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
            AND m.group_id IS NULL AND m.id > ?
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$userId, $chatWith, $chatWith, $userId, $lastId]);
    } else {
        jsonResponse(['messages' => []]);
    }

    $messages = $stmt->fetchAll();

    // Update last seen
    updateUserStatus($userId, 'online');

    jsonResponse(['messages' => $messages]);
}

// =============================================
// USER HANDLERS
// =============================================

function handleSearchUsers() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $q  = clean($_GET['q'] ?? '');
    $me = $_SESSION['user_id'];

    if (strlen($q) < 2) jsonResponse(['users' => []]);

    $db = getDB();
    $stmt = $db->prepare("
        SELECT id, username, avatar, status
        FROM users
        WHERE (username LIKE ? OR email LIKE ?) AND id != ?
        LIMIT 20
    ");
    $stmt->execute(["%$q%", "%$q%", $me]);
    jsonResponse(['users' => $stmt->fetchAll()]);
}

function handleAddFriend() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $userId   = $_SESSION['user_id'];
    $friendId = intval($_POST['friend_id'] ?? 0);

    if (!$friendId || $friendId === $userId)
        jsonResponse(['error' => 'معرف غير صالح'], 400);

    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM friendships WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $stmt->execute([$userId, $friendId, $friendId, $userId]);
    if ($stmt->fetch()) jsonResponse(['error' => 'طلب موجود مسبقاً']);

    // Auto-accept for simplicity
    $db->prepare("INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, 'accepted')")->execute([$userId, $friendId]);
    $db->prepare("INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, 'accepted')")->execute([$friendId, $userId]);

    jsonResponse(['success' => true]);
}

function handleGetFriends() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $userId = $_SESSION['user_id'];
    $db = getDB();
    $stmt = $db->prepare("
        SELECT u.id, u.username, u.avatar, u.status, u.last_seen
        FROM users u
        JOIN friendships f ON (f.friend_id = u.id)
        WHERE f.user_id = ? AND f.status = 'accepted'
        ORDER BY u.status DESC, u.username ASC
    ");
    $stmt->execute([$userId]);
    jsonResponse(['friends' => $stmt->fetchAll()]);
}

function handleUpdateProfile() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $userId = $_SESSION['user_id'];
    $bio    = clean($_POST['bio'] ?? '');
    $db = getDB();

    if (!empty($_FILES['avatar']['name'])) {
        $uploadResult = handleFileUpload($_FILES['avatar'], true);
        if (!isset($uploadResult['error'])) {
            $db->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$uploadResult['path'], $userId]);
        }
    }

    $db->prepare("UPDATE users SET bio = ? WHERE id = ?")->execute([$bio, $userId]);
    jsonResponse(['success' => true]);
}

// =============================================
// GROUP HANDLERS
// =============================================

function handleCreateGroup() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $userId = $_SESSION['user_id'];
    $name   = clean($_POST['name'] ?? '');
    $desc   = clean($_POST['description'] ?? '');
    $members = json_decode($_POST['members'] ?? '[]', true);

    if (!$name) jsonResponse(['error' => 'اسم المجموعة مطلوب'], 400);

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO groups_table (name, description, created_by) VALUES (?, ?, ?)");
    $stmt->execute([$name, $desc, $userId]);
    $groupId = $db->lastInsertId();

    // Add creator as admin
    $db->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'admin')")->execute([$groupId, $userId]);

    // Add members
    foreach ($members as $memberId) {
        if ($memberId != $userId) {
            $db->prepare("INSERT IGNORE INTO group_members (group_id, user_id) VALUES (?, ?)")->execute([$groupId, intval($memberId)]);
        }
    }

    jsonResponse(['success' => true, 'group_id' => $groupId, 'name' => $name]);
}

function handleGetGroups() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $userId = $_SESSION['user_id'];
    $db = getDB();
    $stmt = $db->prepare("
        SELECT g.*, gm.role, 
               (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) AS member_count
        FROM groups_table g
        JOIN group_members gm ON g.id = gm.group_id AND gm.user_id = ?
        ORDER BY g.created_at DESC
    ");
    $stmt->execute([$userId]);
    jsonResponse(['groups' => $stmt->fetchAll()]);
}

function handleJoinGroup() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $userId  = $_SESSION['user_id'];
    $groupId = intval($_POST['group_id'] ?? 0);
    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM groups_table WHERE id = ?");
    $stmt->execute([$groupId]);
    if (!$stmt->fetch()) jsonResponse(['error' => 'المجموعة غير موجودة'], 404);

    $db->prepare("INSERT IGNORE INTO group_members (group_id, user_id) VALUES (?, ?)")->execute([$groupId, $userId]);
    jsonResponse(['success' => true]);
}

function handleGroupMembers() {
    if (!isLoggedIn()) jsonResponse(['error' => 'غير مسجل'], 401);

    $groupId = intval($_GET['group_id'] ?? 0);
    $db = getDB();
    $stmt = $db->prepare("
        SELECT u.id, u.username, u.avatar, u.status, gm.role
        FROM group_members gm
        JOIN users u ON gm.user_id = u.id
        WHERE gm.group_id = ?
        ORDER BY gm.role DESC, u.username ASC
    ");
    $stmt->execute([$groupId]);
    jsonResponse(['members' => $stmt->fetchAll()]);
}

// =============================================
// FILE UPLOAD HELPER
// =============================================

function handleFileUpload($file, $isAvatar = false) {
    if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);

    $allowed = $isAvatar
        ? ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
        : ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf',
           'text/plain', 'application/zip'];

    if (!in_array($file['type'], $allowed)) return ['error' => 'نوع الملف غير مسموح'];
    if ($file['size'] > MAX_FILE_SIZE) return ['error' => 'الملف كبير جداً (الحد 5MB)'];

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('file_', true) . '.' . $ext;
    $dest     = UPLOAD_PATH . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) return ['error' => 'فشل رفع الملف'];

    $type = strpos($file['type'], 'image') !== false ? 'image' : 'file';
    return ['path' => UPLOAD_URL . $filename, 'type' => $type];
}
