<?php

namespace App\Services;

use App\Models\HostedProject;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use ZipArchive;

/**
 * Filesystem orchestrator for a hosted project: extract the ZIP into
 * storage/app/hosted/{slug}, auto-detect the project type, resolve its
 * document root, publish a public symlink (for local Apache / artisan serve),
 * generate the right config file (.env / wp-config.php / config.php) and
 * compute disk stats. Security scanning lives in ShellScannerService; DB work
 * in DatabaseManagerService; nginx in NginxConfigService. This class only
 * touches files.
 */
class ProjectHostingService
{
    /* -------------------------------------------------------------- paths */

    public function basePath(string $slug): string
    {
        return config('hosting.paths.storage', storage_path('app/hosted')) . DIRECTORY_SEPARATOR . $slug;
    }

    public function publicLinkPath(string $slug): string
    {
        return config('hosting.paths.public', public_path('hosted')) . DIRECTORY_SEPARATOR . $slug;
    }

    /** Absolute document root actually served (project root or its /public). */
    public function docRoot(string $slug): string
    {
        $base = $this->basePath($slug);

        return is_dir($base . '/public') ? $base . '/public' : $base;
    }

    /* ----------------------------------------------------------- extract */

    /**
     * Extract a validated ZIP into storage/app/hosted/{slug}, replacing any
     * prior contents, and unwrap a single top-level folder. Returns base path.
     */
    public function extract(string $zipPath, string $slug): string
    {
        $target = $this->basePath($slug);
        $this->rrmdir($target);
        @mkdir($target, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('تعذّر فتح ملف ZIP.');
        }
        $zip->extractTo($target);
        $zip->close();

        $this->flattenWrapper($target);

        return $target;
    }

    /* ----------------------------------------------------- type detection */

    /**
     * Best-effort runtime detection from the extracted tree.
     * Order matters: Laravel & WordPress are checked before generic PHP.
     */
    public function detectType(string $slug): string
    {
        $base = $this->basePath($slug);

        if (is_file($base . '/artisan') && is_dir($base . '/app')) {
            return 'laravel';
        }
        if (is_file($base . '/wp-config.php') || is_file($base . '/wp-config-sample.php') || is_dir($base . '/wp-content')) {
            return 'wordpress';
        }
        if (is_file($base . '/index.php') || ! empty($this->glob($base, 'php', 1))) {
            return 'php';
        }
        if (is_file($base . '/package.json')) {
            return 'nodejs'; // served statically — only built/exported output works
        }
        if (is_file($base . '/index.html') || is_file($base . '/public/index.html') || is_file($base . '/dist/index.html')) {
            return 'static';
        }

        return 'static';
    }

    /**
     * Relative webroot (under the app path) for nginx + the model. Laravel
     * serves from /public; built front-ends from dist|build|out|public.
     */
    public function resolveWebrootPath(string $slug, string $type): string
    {
        $base = $this->basePath($slug);
        $rel = 'storage/app/hosted/' . $slug;

        if ($type === 'laravel' && is_dir($base . '/public')) {
            return $rel . '/public';
        }
        if (in_array($type, ['static', 'nodejs'], true)) {
            foreach (['dist', 'build', 'out', 'public'] as $sub) {
                if (is_dir($base . '/' . $sub) && is_file($base . '/' . $sub . '/index.html')) {
                    return $rel . '/' . $sub;
                }
            }
        }

        return $rel;
    }

    /* ----------------------------------------------------------- publish */

    /**
     * Publish public/hosted/{slug} → the project's document root. Tries a real
     * symlink, then a Windows directory junction, then a full copy. Needed for
     * local Apache / `artisan serve`; on the nginx container the alias handles
     * routing, but the link is harmless and keeps static serving uniform.
     */
    public function publish(string $slug): void
    {
        $source = $this->docRoot($slug);
        $link = $this->publicLinkPath($slug);

        @mkdir(dirname($link), 0755, true);
        $this->removeLink($link);

        if (@symlink($source, $link)) {
            return;
        }
        if (stripos(PHP_OS, 'WIN') === 0) {
            @exec('cmd /c mklink /J ' . escapeshellarg($link) . ' ' . escapeshellarg($source) . ' 2>nul', $o, $code);
            if ($code === 0) {
                return;
            }
        }
        $this->rcopy($source, $link);
    }

    /* ------------------------------------------------------- config files */

    /**
     * Write the runtime config the project expects, filled with DB credentials
     * (from DatabaseManagerService::provision) and the admin's custom env vars.
     *
     * @param  array{database?:string,username?:string,password?:string,host?:string,port?:int}  $db
     */
    public function writeConfig(HostedProject $project, array $db = []): void
    {
        $extra = (array) ($project->env_vars ?? []);

        match ($project->type) {
            'laravel' => $this->writeLaravelEnv($project, $db, $extra),
            'wordpress' => $this->writeWordpressConfig($project, $db),
            'php' => $this->writePhpConfig($project, $db, $extra),
            default => $this->writeDotenv($project, $db, $extra), // static/nodejs: harmless .env
        };
    }

    private function writeLaravelEnv(HostedProject $project, array $db, array $extra): void
    {
        $lines = [
            'APP_NAME=' . $this->q($project->displayName('en') ?: $project->slug),
            'APP_ENV=production',
            'APP_DEBUG=false',
            'APP_URL=' . $project->liveUrl(),
            '',
        ];
        if (! empty($db['database'])) {
            $lines = array_merge($lines, [
                'DB_CONNECTION=mysql',
                'DB_HOST=' . ($db['host'] ?? '127.0.0.1'),
                'DB_PORT=' . ($db['port'] ?? 3306),
                'DB_DATABASE=' . $db['database'],
                'DB_USERNAME=' . ($db['username'] ?? 'root'),
                'DB_PASSWORD=' . $this->q($db['password'] ?? ''),
                '',
            ]);
        }
        foreach ($extra as $k => $v) {
            $lines[] = strtoupper($k) . '=' . $this->q((string) $v);
        }

        @file_put_contents($this->basePath($project->slug) . '/.env', implode("\n", $lines) . "\n");
    }

    private function writeDotenv(HostedProject $project, array $db, array $extra): void
    {
        $lines = ['APP_URL=' . $project->liveUrl()];
        foreach ($extra as $k => $v) {
            $lines[] = strtoupper($k) . '=' . $this->q((string) $v);
        }
        @file_put_contents($this->basePath($project->slug) . '/.env', implode("\n", $lines) . "\n");
    }

    private function writePhpConfig(HostedProject $project, array $db, array $extra): void
    {
        if (empty($db['database'])) {
            return;
        }
        $php = "<?php\n// Auto-generated by the hosting engine.\n"
            . "define('DB_HOST', " . var_export($db['host'] ?? '127.0.0.1', true) . ");\n"
            . "define('DB_PORT', " . var_export((int) ($db['port'] ?? 3306), true) . ");\n"
            . "define('DB_NAME', " . var_export($db['database'], true) . ");\n"
            . "define('DB_USER', " . var_export($db['username'] ?? 'root', true) . ");\n"
            . "define('DB_PASS', " . var_export($db['password'] ?? '', true) . ");\n";
        foreach ($extra as $k => $v) {
            $php .= 'define(' . var_export(strtoupper($k), true) . ', ' . var_export((string) $v, true) . ");\n";
        }
        @file_put_contents($this->basePath($project->slug) . '/config.php', $php);
    }

    private function writeWordpressConfig(HostedProject $project, array $db): void
    {
        if (empty($db['database'])) {
            return;
        }
        $salts = '';
        foreach (['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'] as $k) {
            $salts .= "define('{$k}', " . var_export(\Illuminate\Support\Str::random(64), true) . ");\n";
        }
        $cfg = "<?php\n// Auto-generated by the hosting engine.\n"
            . "define('DB_NAME', " . var_export($db['database'], true) . ");\n"
            . "define('DB_USER', " . var_export($db['username'] ?? 'root', true) . ");\n"
            . "define('DB_PASSWORD', " . var_export($db['password'] ?? '', true) . ");\n"
            . "define('DB_HOST', " . var_export(($db['host'] ?? '127.0.0.1') . ':' . ($db['port'] ?? 3306), true) . ");\n"
            . "define('DB_CHARSET', 'utf8mb4');\n\$table_prefix = 'wp_';\n"
            . $salts
            . "if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');\nrequire_once ABSPATH . 'wp-settings.php';\n";

        @file_put_contents($this->basePath($project->slug) . '/wp-config.php', $cfg);
    }

    /* -------------------------------------------------------- composer */

    /**
     * Auto-install Laravel dependencies. Best-effort: a failure is reported but
     * doesn't abort the deploy (the project may ship its own vendor/).
     *
     * @return array{ran:bool, ok:bool, output:string}
     */
    public function composerInstall(string $slug): array
    {
        $base = $this->basePath($slug);
        if (! is_file($base . '/composer.json') || is_dir($base . '/vendor')) {
            return ['ran' => false, 'ok' => true, 'output' => 'skipped (no composer.json or vendor/ present)'];
        }

        $composer = config('hosting.composer_path', 'composer');
        try {
            $result = Process::path($base)->timeout(300)->run([
                $composer, 'install', '--no-dev', '--no-interaction', '--prefer-dist', '--optimize-autoloader',
            ]);

            return ['ran' => true, 'ok' => $result->successful(), 'output' => trim($result->errorOutput() ?: $result->output())];
        } catch (\Throwable $e) {
            return ['ran' => true, 'ok' => false, 'output' => $e->getMessage()];
        }
    }

    /* ----------------------------------------------- base-href (static) */

    /**
     * Inject <base href="/hosted/{slug}/"> into static HTML so relative asset
     * paths resolve regardless of a trailing slash. PHP projects are skipped
     * (they manage their own URLs).
     */
    public function injectBaseHref(string $slug): void
    {
        $docRoot = $this->docRoot($slug);
        $base = '/' . trim(config('hosting.serving.base_uri', 'hosted'), '/') . '/' . trim($slug, '/') . '/';
        $tag = '<base href="' . $base . '">';

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($docRoot, \FilesystemIterator::SKIP_DOTS)
        );
        $touched = 0;
        foreach ($it as $file) {
            if ($touched >= 300) {
                break;
            }
            if (! $file->isFile() || ! in_array(strtolower($file->getExtension()), ['html', 'htm'], true)) {
                continue;
            }
            $html = @file_get_contents($file->getPathname());
            if ($html === false || ! preg_match('/<head\b[^>]*>/i', $html) || stripos($html, '<base') !== false) {
                continue;
            }
            $patched = preg_replace('/(<head\b[^>]*>)/i', '$1' . "\n    " . $tag, $html, 1);
            if ($patched !== null && $patched !== $html) {
                @file_put_contents($file->getPathname(), $patched);
                $touched++;
            }
        }
    }

    /* ------------------------------------------------------------- stats */

    /** @return array{disk_usage:int, file_count:int} */
    public function stats(string $slug): array
    {
        $base = $this->basePath($slug);
        if (! is_dir($base)) {
            return ['disk_usage' => 0, 'file_count' => 0];
        }
        $bytes = 0;
        $count = 0;
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if ($file->isFile()) {
                $bytes += $file->getSize();
                $count++;
            }
        }

        return ['disk_usage' => $bytes, 'file_count' => $count];
    }

    public function destroy(string $slug): void
    {
        $this->removeLink($this->publicLinkPath($slug));
        $this->rrmdir($this->basePath($slug));
    }

    /* ------------------------------------------------------------- helpers */

    /** Lift a single wrapping folder (e.g. "MyProject/…") up to the base. */
    private function flattenWrapper(string $target): void
    {
        $entries = array_values(array_diff(scandir($target) ?: [], ['.', '..']));
        if (count($entries) !== 1) {
            return;
        }
        $inner = $target . '/' . $entries[0];
        if (! is_dir($inner)) {
            return;
        }
        foreach (array_diff(scandir($inner) ?: [], ['.', '..']) as $item) {
            @rename($inner . '/' . $item, $target . '/' . $item);
        }
        @rmdir($inner);
    }

    /** Shallow glob for files of an extension up to $depth levels deep. */
    private function glob(string $base, string $ext, int $depth = 1): array
    {
        $hits = glob($base . '/*.' . $ext) ?: [];
        if ($depth > 1) {
            foreach (glob($base . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
                $hits = array_merge($hits, $this->glob($dir, $ext, $depth - 1));
            }
        }

        return $hits;
    }

    private function removeLink(string $path): void
    {
        if (is_link($path)) {
            @unlink($path);
        } elseif (is_dir($path)) {
            if (stripos(PHP_OS, 'WIN') === 0) {
                @rmdir($path);
                if (is_dir($path)) {
                    $this->rrmdir($path);
                }
            } else {
                $this->rrmdir($path);
            }
        }
    }

    private function rrmdir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getRealPath()) : @unlink($item->getRealPath());
        }
        @rmdir($dir);
    }

    private function rcopy(string $src, string $dst): void
    {
        @mkdir($dst, 0755, true);
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($items as $item) {
            $dest = $dst . DIRECTORY_SEPARATOR . $items->getSubPathname();
            $item->isDir() ? @mkdir($dest, 0755, true) : @copy($item->getRealPath(), $dest);
        }
    }

    /** Quote a value for a .env line if it contains whitespace/special chars. */
    private function q(string $v): string
    {
        return preg_match('/\s|#|"|\'/', $v) ? '"' . str_replace('"', '\"', $v) . '"' : $v;
    }
}
