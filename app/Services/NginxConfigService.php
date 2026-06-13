<?php

namespace App\Services;

use App\Models\HostedProject;
use Illuminate\Support\Facades\Process;

/**
 * Generates one nginx config snippet per hosted project under
 * storage/nginx/hosted/{slug}.conf. The main server block `include`s that
 * directory, so each project gets its own `location /hosted/{slug}/`.
 *
 * Applied at container boot (see hosting:regenerate-nginx + supervisord), NOT
 * via live reload from a web request: the php-fpm worker can't signal nginx's
 * master process, and Railway runs a single container. reload() exists for
 * environments where it IS possible, but the engine never depends on it —
 * static projects are served instantly by HostedController regardless.
 */
class NginxConfigService
{
    public function configDir(): string
    {
        return config('hosting.paths.nginx', storage_path('nginx/hosted'));
    }

    public function path(string $slug): string
    {
        return $this->configDir() . DIRECTORY_SEPARATOR . $this->safe($slug) . '.conf';
    }

    /** Build + write the project's config file. Returns its path. */
    public function generate(HostedProject $project): string
    {
        $this->ensureDir();
        $conf = $project->isPhpRuntime()
            ? $this->phpBlock($project)
            : $this->staticBlock($project);

        $path = $this->path($project->slug);
        file_put_contents($path, $conf);

        return $path;
    }

    /** Remove a project's config file (called on delete). */
    public function remove(string $slug): void
    {
        $path = $this->path($slug);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Regenerate configs for every project that should be live. Run at boot so
     * the ephemeral container rebuilds all snippets from the database.
     *
     * @return int number of configs written
     */
    public function regenerateAll(): int
    {
        $this->ensureDir();

        // Clear stale snippets first so deleted projects don't linger.
        foreach (glob($this->configDir() . DIRECTORY_SEPARATOR . '*.conf') ?: [] as $file) {
            @unlink($file);
        }

        $count = 0;
        HostedProject::query()
            ->whereIn('status', ['active', 'maintenance'])
            ->where('processing_status', 'completed')
            ->each(function (HostedProject $p) use (&$count) {
                $this->generate($p);
                $count++;
            });

        return $count;
    }

    /** Best-effort `nginx -s reload`. Never throws; returns success. */
    public function reload(): bool
    {
        try {
            return Process::timeout(15)->run('nginx -s reload')->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ------------------------------------------------------------ templates */

    /** Document root inside the deployment container. */
    private function docRoot(HostedProject $project): string
    {
        $appPath = rtrim(config('hosting.serving.container_app_path', '/var/www/html'), '/');
        $rel = ltrim($project->webroot_path ?: 'storage/app/hosted/' . $project->slug, '/');

        return $appPath . '/' . $rel;
    }

    private function phpBlock(HostedProject $project): string
    {
        $slug = $this->safe($project->slug);
        $uri = trim(config('hosting.serving.base_uri', 'hosted'), '/') . '/' . $slug;
        $root = $this->docRoot($project);
        $fpm = config('hosting.serving.php_fpm', '127.0.0.1:9000');
        $entry = $project->entry_point ?: 'index.php';
        // Per-project filesystem jail for PHP (open_basedir IS settable via PHP_VALUE;
        // disable_functions is PHP_INI_SYSTEM and lives in the FPM pool, not here).
        $basedir = $root . '/:/tmp/';

        return <<<NGINX
        # Hosted project: {$project->slug} ({$project->type}) — auto-generated, do not edit.
        location /{$uri}/ {
            alias {$root}/;
            index {$entry};
            try_files \$uri \$uri/ /{$uri}/{$entry}?\$query_string;

            location ~ ^/{$uri}/(.+\\.php)\$ {
                alias {$root}/;
                try_files /\$1 =404;
                fastcgi_pass {$fpm};
                fastcgi_index {$entry};
                include fastcgi_params;
                fastcgi_param SCRIPT_FILENAME {$root}/\$1;
                fastcgi_param PHP_VALUE "open_basedir={$basedir}";
                fastcgi_read_timeout 120;
            }
        }

        NGINX;
    }

    private function staticBlock(HostedProject $project): string
    {
        $slug = $this->safe($project->slug);
        $uri = trim(config('hosting.serving.base_uri', 'hosted'), '/') . '/' . $slug;
        $root = $this->docRoot($project);
        $entry = str_ends_with($project->entry_point ?: '', '.html') ? $project->entry_point : 'index.html';

        return <<<NGINX
        # Hosted project: {$project->slug} (static) — auto-generated, do not edit.
        location /{$uri}/ {
            alias {$root}/;
            index {$entry};
            # SPA-friendly: fall back to the entry file so client-side routes work.
            try_files \$uri \$uri/ /{$uri}/{$entry};
        }

        NGINX;
    }

    /* ------------------------------------------------------------- helpers */

    private function ensureDir(): void
    {
        $dir = $this->configDir();
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    private function safe(string $slug): string
    {
        return preg_replace('/[^a-z0-9_-]/', '', strtolower($slug));
    }
}
