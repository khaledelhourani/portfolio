<?php

/**
 * Multi-Project Hosting Engine settings. Tunable per environment via .env —
 * the defaults target local XAMPP; the Railway container overrides paths and
 * the DB mode. See app/Services/* for consumers.
 */
return [

    // Master on/off. Lets the whole engine be disabled on hosts where it can't
    // run safely (e.g. Railway free tier) without touching code.
    'enabled' => (bool) env('HOSTING_ENABLED', true),

    // Largest ZIP accepted, in megabytes. Keep <= php.ini upload_max_filesize /
    // post_max_size (currently 10240M / 10300M) or PHP rejects the upload first.
    'max_upload_mb' => (int) env('HOSTING_MAX_UPLOAD_MB', 10240),

    // Largest SQL dump accepted for import, in megabytes.
    'max_sql_mb' => (int) env('HOSTING_MAX_SQL_MB', 10240),

    // Per-admin upload throttle (uploads per hour).
    'uploads_per_hour' => (int) env('HOSTING_UPLOADS_PER_HOUR', 10),

    /*
    | Database provisioning mode:
    |   auto      – probe the server once; use 'dedicated' if CREATE USER/DATABASE
    |               is permitted (local MariaDB/MySQL root), else 'disabled'.
    |   dedicated – force per-project database + restricted user.
    |   disabled  – never provision; DB-backed projects are rejected with a clear
    |               message (managed hosts like Railway that forbid CREATE USER).
    */
    'db_mode' => env('HOSTING_DB_MODE', 'auto'),

    // Connection used to run privileged DDL (CREATE DATABASE/USER, GRANT).
    // Defaults to the app's main connection (XAMPP root locally).
    'db_admin_connection' => env('HOSTING_DB_ADMIN_CONNECTION', env('DB_CONNECTION', 'mysql')),

    // CLI binaries for robust import/export of real-world dumps (triggers,
    // DELIMITER blocks, etc.). Windows/XAMPP defaults; just the bare command on Linux.
    'mysql_path' => env('HOSTING_MYSQL_PATH', 'D:\\xampp\\mysql\\bin\\mysql.exe'),
    'mysqldump_path' => env('HOSTING_MYSQLDUMP_PATH', env('MYSQLDUMP_PATH', 'D:\\xampp\\mysql\\bin\\mysqldump.exe')),

    // Composer binary used to auto-install Laravel project dependencies.
    'composer_path' => env('HOSTING_COMPOSER_PATH', 'composer'),

    // php-cgi binary used by HostedController to execute PHP projects locally
    // (where there's no nginx+fpm). On the Railway container nginx serves .php
    // directly, so this path is only exercised in dev / on Apache.
    'php_cgi_path' => env('HOSTING_PHP_CGI_PATH', 'D:\\xampp\\php\\php-cgi.exe'),

    // Grants handed to each project's restricted MySQL user.
    'db_grants' => 'SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP',

    /*
    | Filesystem layout. On Railway these should point inside the mounted
    | Volume so projects survive redeploys (e.g. STORAGE on a persistent disk).
    */
    'paths' => [
        'storage' => storage_path('app/hosted'),     // extracted project files
        'public' => public_path('hosted'),           // served webroots (symlinks)
        'quarantine' => storage_path('app/quarantine'), // rejected uploads, kept for review
        'nginx' => storage_path('nginx/hosted'),     // generated per-project *.conf
        'tmp' => storage_path('app/hosted-tmp'),     // isolated extraction scratch
    ],

    // PHP versions offered in the upload form (FPM must provide a matching pool
    // on the server; informational locally).
    'php_versions' => ['8.0', '8.1', '8.2', '8.3'],

    /*
    | Serving layer. nginx configs are generated as files and applied at
    | container boot (no live `nginx -s reload` from a web request — the web
    | user can't signal nginx's master, and Railway is single-container).
    */
    'serving' => [
        // URL path prefix every hosted project lives under (…/hosted/{slug}/).
        'base_uri' => 'hosted',

        // php-fpm upstream the generated configs forward .php to. In the Docker
        // image fpm + nginx share the container, so loopback:9000.
        'php_fpm' => env('HOSTING_PHP_FPM', '127.0.0.1:9000'),

        // Absolute app path INSIDE the deployment container. Used when building
        // nginx `alias`/`root` so configs generated on Windows still reference
        // the correct Linux path at runtime.
        'container_app_path' => env('HOSTING_CONTAINER_APP_PATH', '/var/www/html'),
    ],
];
