<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Provisions and manages a MySQL database per hosted project.
 *
 * Three provisioning modes (config hosting.db_mode):
 *   dedicated     – CREATE DATABASE + a restricted per-project user (GRANT).
 *                   Strongest isolation, but requires a healthy server that
 *                   permits CREATE USER/GRANT. Opt-in only.
 *   database_only – CREATE DATABASE; the project connects with the MAIN MySQL
 *                   account scoped to its own schema. No GRANT, so it works on
 *                   managed hosts (Railway forbids CREATE USER) and on servers
 *                   whose grant tables are damaged. This is the default.
 *   disabled      – never provision; DB-backed projects are rejected clearly.
 *
 * 'auto' resolves to database_only when CREATE DATABASE works, else disabled.
 * It deliberately NEVER probes GRANT — a fragile grant subsystem can crash the
 * whole server on GRANT, which would take the portfolio down with it.
 */
class DatabaseManagerService
{
    /** Cached CREATE-DATABASE capability probe (per process). */
    private static ?bool $probeCache = null;

    /* ------------------------------------------------------------ capability */

    /** 'dedicated' | 'database_only' | 'disabled'. */
    public function mode(): string
    {
        $configured = config('hosting.db_mode', 'auto');
        if (in_array($configured, ['dedicated', 'database_only', 'disabled'], true)) {
            return $configured;
        }

        // auto — safe path only (no GRANT probe).
        return $this->canCreateDatabase() ? 'database_only' : 'disabled';
    }

    /** Can we provision a database at all (any non-disabled mode)? */
    public function isAvailable(): bool
    {
        return $this->mode() !== 'disabled';
    }

    /** Does the active mode create a dedicated restricted user per project? */
    public function usesDedicatedUser(): bool
    {
        return $this->mode() === 'dedicated';
    }

    /** Probe: can the admin connection CREATE then DROP a database? (Safe.) */
    private function canCreateDatabase(): bool
    {
        if (self::$probeCache !== null) {
            return self::$probeCache;
        }

        $probe = '__hosting_probe_' . Str::lower(Str::random(8));
        try {
            $conn = DB::connection($this->adminConnection());
            $conn->statement("CREATE DATABASE `{$probe}`");
            $conn->statement("DROP DATABASE `{$probe}`");

            return self::$probeCache = true;
        } catch (\Throwable $e) {
            return self::$probeCache = false;
        }
    }

    /* -------------------------------------------------------------- naming */

    /** Derive safe, collision-resistant MySQL identifiers from a slug. */
    public function names(string $slug): array
    {
        $safe = substr(preg_replace('/[^a-z0-9_]/', '_', strtolower($slug)), 0, 24);

        return [
            'database' => 'proj_' . $safe,
            'username' => 'usr_' . $safe,   // only used in dedicated mode
        ];
    }

    /* ----------------------------------------------------------- provision */

    /**
     * Create the project database (idempotent). In dedicated mode also creates
     * a restricted user; otherwise returns the main connection's credentials
     * scoped to the new database.
     *
     * @return array{database:string, username:string, password:string, host:string, port:int, mode:string}
     *
     * @throws RuntimeException when the host can't provision databases.
     */
    public function provision(string $slug): array
    {
        $mode = $this->mode();
        if ($mode === 'disabled') {
            throw new RuntimeException(
                'استضافة قواعد البيانات غير متاحة على هذا الخادم. '
                . 'انشر المشروع بدون قاعدة بيانات، أو فعّل وضع توفير القواعد.'
            );
        }

        ['database' => $db, 'username' => $user] = $this->names($slug);
        $main = config('database.connections.' . $this->adminConnection());
        $conn = DB::connection($this->adminConnection());

        $this->dropDatabaseAndUser($db, $user);
        $conn->statement("CREATE DATABASE `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        if ($mode === 'dedicated') {
            $password = Str::random(24);
            $grants = config('hosting.db_grants', 'SELECT, INSERT, UPDATE, DELETE');
            foreach (['localhost', '127.0.0.1'] as $host) {
                $conn->statement("CREATE USER '{$user}'@'{$host}' IDENTIFIED BY '{$password}'");
                $conn->statement("GRANT {$grants} ON `{$db}`.* TO '{$user}'@'{$host}'");
            }
            $conn->statement('FLUSH PRIVILEGES');

            return [
                'database' => $db, 'username' => $user, 'password' => $password,
                'host' => $main['host'] ?? '127.0.0.1', 'port' => (int) ($main['port'] ?? 3306),
                'mode' => 'dedicated',
            ];
        }

        // database_only — project uses the main account, scoped to its schema.
        return [
            'database' => $db,
            'username' => $main['username'] ?? 'root',
            'password' => $main['password'] ?? '',
            'host' => $main['host'] ?? '127.0.0.1',
            'port' => (int) ($main['port'] ?? 3306),
            'mode' => 'database_only',
        ];
    }

    /** Drop a project's database (+ its user in dedicated mode). Safe on partial state. */
    public function dropDatabaseAndUser(?string $database, ?string $user): void
    {
        if ($this->mode() === 'disabled') {
            return;
        }
        $conn = DB::connection($this->adminConnection());

        if ($database) {
            $conn->statement("DROP DATABASE IF EXISTS `{$database}`");
        }
        if ($user && $this->usesDedicatedUser()) {
            foreach (['localhost', '127.0.0.1'] as $host) {
                $conn->statement("DROP USER IF EXISTS '{$user}'@'{$host}'");
            }
            $conn->statement('FLUSH PRIVILEGES');
        }
    }

    /* -------------------------------------------------------- import/export */

    /**
     * Import a .sql file into a database. Prefers the mysql CLI (handles
     * DELIMITER blocks, triggers and very large dumps that unprepared() chokes
     * on); falls back to a transactional unprepared() when no binary is found.
     */
    public function import(string $database, string $sqlPath): void
    {
        if (! is_file($sqlPath)) {
            throw new RuntimeException('ملف SQL غير موجود.');
        }
        if (filesize($sqlPath) === 0) {
            throw new RuntimeException('ملف SQL فارغ.');
        }

        if ($this->importViaCli($database, $sqlPath)) {
            return;
        }

        // Fallback only (no mysql CLI present): load + run in-process. Suitable
        // for small dumps; large files always take the streaming CLI path above.
        $conn = $this->adminConnectionTo($database);
        DB::connection($conn)->unprepared((string) file_get_contents($sqlPath));
        DB::purge($conn);
    }

    /** @return bool true if the CLI handled it; false to trigger the fallback. */
    private function importViaCli(string $database, string $sqlPath): bool
    {
        $bin = config('hosting.mysql_path');
        if (! $bin || ! $this->binaryExists($bin)) {
            return false;
        }

        $conn = DB::connection($this->adminConnection());

        // Clean slate before importing — this is what makes import idempotent
        // and immune to "Tablespace ... exists" (error 1813): a previously
        // failed import can leave orphaned InnoDB .ibd files that block the next
        // CREATE TABLE. We drop+recreate the project schema AND drop every
        // database the dump itself creates/uses (e.g. a phpMyAdmin export that
        // hardcodes its own DB name), so nothing collides.
        $this->resetDatabase($database);
        foreach ($this->databasesReferenced($sqlPath) as $dumpDb) {
            if ($dumpDb !== $database) {
                $conn->statement("DROP DATABASE IF EXISTS `{$dumpDb}`");
            }
        }

        $cfg = config('database.connections.' . $this->adminConnection());
        $args = [$bin, '-h', $cfg['host'] ?? '127.0.0.1', '-P', (string) ($cfg['port'] ?? 3306), '-u', $cfg['username'] ?? 'root'];
        if (! empty($cfg['password'])) {
            $args[] = '-p' . $cfg['password'];
        }
        $args[] = '--default-character-set=utf8mb4';
        $args[] = $database; // default schema for dumps that ship tables only

        // Stream the file as a resource → constant memory, so multi-GB dumps
        // import without exhausting PHP's memory_limit.
        $input = fopen($sqlPath, 'r');
        try {
            $result = Process::timeout(1800)->env($this->processEnv())->input($input)->run($args);
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
        }

        if (! $result->successful()) {
            throw new RuntimeException('فشل استيراد قاعدة البيانات: ' . trim($result->errorOutput()));
        }

        return true;
    }

    /** Drop + recreate a schema so an import starts from a guaranteed-clean state. */
    private function resetDatabase(string $database): void
    {
        $conn = DB::connection($this->adminConnection());
        $conn->statement("DROP DATABASE IF EXISTS `{$database}`");
        $conn->statement("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * Scan a dump for the database names it creates/uses (CREATE DATABASE / USE)
     * so we can clear them before importing. System schemas are never returned.
     * Reads line-by-line to stay memory-flat on huge dumps.
     *
     * @return array<int,string>
     */
    private function databasesReferenced(string $sqlPath): array
    {
        $system = ['mysql', 'information_schema', 'performance_schema', 'sys'];
        $found = [];

        $handle = fopen($sqlPath, 'r');
        if ($handle === false) {
            return [];
        }
        try {
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/^\s*(?:CREATE\s+DATABASE(?:\s+IF\s+NOT\s+EXISTS)?|USE)\s+`?([A-Za-z0-9_]+)`?/i', $line, $m)) {
                    $name = $m[1];
                    if (! in_array(strtolower($name), $system, true)) {
                        $found[$name] = true;
                    }
                }
            }
        } finally {
            fclose($handle);
        }

        return array_keys($found);
    }

    /** Run mysqldump and return the dump as a string. */
    public function dump(string $database): string
    {
        $bin = config('hosting.mysqldump_path', 'mysqldump');
        if (! $this->binaryExists($bin)) {
            throw new RuntimeException('أداة mysqldump غير متوفرة على الخادم.');
        }

        $cfg = config('database.connections.' . $this->adminConnection());
        $args = [$bin, '-h', $cfg['host'] ?? '127.0.0.1', '-P', (string) ($cfg['port'] ?? 3306), '-u', $cfg['username'] ?? 'root'];
        if (! empty($cfg['password'])) {
            $args[] = '-p' . $cfg['password'];
        }
        $args[] = '--default-character-set=utf8mb4';
        $args[] = '--single-transaction';
        $args[] = $database;

        $result = Process::timeout(120)->env($this->processEnv())->run($args);
        if (! $result->successful()) {
            throw new RuntimeException('فشل تصدير قاعدة البيانات: ' . trim($result->errorOutput()));
        }

        return $result->output();
    }

    /* ------------------------------------------------------------- metrics */

    /** @return array<int, array{name:string, rows:int, size:int}> */
    public function tables(string $database): array
    {
        $rows = DB::connection($this->adminConnection())->select(
            'SELECT table_name AS name, table_rows AS row_count, (data_length + index_length) AS size '
            . 'FROM information_schema.tables WHERE table_schema = ? ORDER BY table_name',
            [$database]
        );

        return array_map(fn ($r) => [
            'name' => $r->name,
            'rows' => (int) $r->row_count,
            'size' => (int) $r->size,
        ], $rows);
    }

    public function size(string $database): int
    {
        $row = DB::connection($this->adminConnection())->selectOne(
            'SELECT COALESCE(SUM(data_length + index_length), 0) AS size '
            . 'FROM information_schema.tables WHERE table_schema = ?',
            [$database]
        );

        return (int) ($row->size ?? 0);
    }

    /* ------------------------------------------------------------- helpers */

    private function adminConnection(): string
    {
        return config('hosting.db_admin_connection') ?: config('database.default');
    }

    /** A transient admin connection bound to a specific database (for imports). */
    private function adminConnectionTo(string $database): string
    {
        $name = 'hosted_admin';
        config(["database.connections.{$name}" => array_merge(
            config('database.connections.' . $this->adminConnection()),
            ['database' => $database]
        )]);
        DB::purge($name);

        return $name;
    }

    private function binaryExists(string $bin): bool
    {
        if (str_contains($bin, '/') || str_contains($bin, '\\')) {
            return is_file($bin);
        }

        return true; // bare command — assume on PATH
    }

    /**
     * Environment handed to spawned mysql/mysqldump processes.
     *
     * On Windows a child process MUST have SystemRoot in its environment or
     * Winsock can't initialise — mysql/mysqldump then die with
     * "Can't create TCP/IP socket (10106)". Under a web-server context these
     * vars are sometimes stripped, so set them explicitly (with a hard
     * SystemRoot fallback). On Linux this is a harmless no-op subset.
     *
     * @return array<string,string>
     */
    private function processEnv(): array
    {
        if (stripos(PHP_OS, 'WIN') !== 0) {
            return []; // inherit parent env unchanged on Linux/macOS
        }

        $env = [];
        foreach (['SystemRoot', 'SystemDrive', 'WINDIR', 'TEMP', 'TMP', 'PATH', 'APPDATA', 'COMSPEC'] as $key) {
            $value = getenv($key);
            if ($value !== false && $value !== '') {
                $env[$key] = $value;
            }
        }
        // Guarantee the one var Winsock cannot live without.
        $env['SystemRoot'] = $env['SystemRoot'] ?? ($env['WINDIR'] ?? 'C:\\Windows');

        return $env;
    }
}
