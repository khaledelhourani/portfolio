-- ============================================================
--  database_schema.sql
--  تشغيل هذا الملف في TiDB Cloud SQL Editor
--  لإنشاء قاعدة البيانات والجداول
-- ============================================================

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS blog_backend
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE blog_backend;

-- ─── جدول المستخدمين ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    passwerd   VARCHAR(255) NOT NULL,          -- bcrypt hash (255 حرف كافية)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── جدول التصنيفات ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    slug       VARCHAR(110) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── جدول المقالات ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS blogs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    slug        VARCHAR(270) NOT NULL UNIQUE,
    content     LONGTEXT     NOT NULL,
    image       VARCHAR(255) DEFAULT NULL,
    category_id INT UNSIGNED DEFAULT NULL,
    user_id     INT UNSIGNED DEFAULT NULL,
    status      ENUM('draft','published') DEFAULT 'draft',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── مستخدم افتراضي للاختبار ─────────────────────────────────
-- كلمة المرور: Admin@1234  (bcrypt hash)
INSERT IGNORE INTO users (username, email, passwerd) VALUES (
    'admin',
    'admin@example.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);
