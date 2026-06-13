<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registry row for one project hosted under the portfolio domain by the
 * Multi-Project Hosting Engine. Files live in storage/app/hosted/{slug};
 * sensitive fields (DB password, env vars) are encrypted at rest.
 */
class HostedProject extends Model
{
    /** Project runtimes the engine knows how to serve. */
    public const TYPES = ['php', 'laravel', 'wordpress', 'static', 'nodejs'];

    /** Public-facing lifecycle states. */
    public const STATUSES = ['active', 'maintenance', 'disabled'];

    /** Async deploy-pipeline states (set by ProcessHostedProject). */
    public const PROCESSING = ['pending', 'processing', 'completed', 'failed'];

    protected $fillable = [
        'name', 'name_ar', 'name_en',
        'description', 'description_ar', 'description_en',
        'slug', 'type', 'entry_point', 'status',
        'has_database', 'db_name', 'db_user', 'db_password',
        'env_vars', 'php_version', 'custom_domain', 'thumbnail',
        'zip_path', 'webroot_path', 'nginx_config_path',
        'disk_usage', 'db_size', 'file_count', 'disk_usage_mb', 'db_size_mb',
        'processing_status', 'processing_step', 'processing_log',
        'last_deployed_at',
    ];

    protected $hidden = ['db_password', 'env_vars'];

    protected function casts(): array
    {
        return [
            'has_database' => 'boolean',
            'db_password' => 'encrypted',
            'env_vars' => 'encrypted:array',
            'processing_step' => 'integer',
            'disk_usage_mb' => 'integer',
            'db_size_mb' => 'integer',
            'last_deployed_at' => 'datetime',
        ];
    }

    /* ----------------------------------------------------------------- URLs */

    /**
     * Public URL of the hosted site. Trailing slash matters: static sites
     * reference assets with relative paths that only resolve under a "/" base.
     */
    public function liveUrl(): string
    {
        return url('hosted/' . $this->slug) . '/';
    }

    /* ----------------------------------------------------- Filesystem paths */

    /** Absolute path to the extracted project root. */
    public function basePath(): string
    {
        return storage_path('app/hosted/' . $this->slug);
    }

    /** Document root actually served (Laravel/Node SPAs publish from /public). */
    public function docRoot(): string
    {
        $base = $this->basePath();

        return is_dir($base . '/public') ? $base . '/public' : $base;
    }

    /* ---------------------------------------------------------- Convenience */

    public function isPhpRuntime(): bool
    {
        return in_array($this->type, ['php', 'laravel', 'wordpress'], true);
    }

    public function isStaticRuntime(): bool
    {
        return in_array($this->type, ['static', 'nodejs'], true);
    }

    public function isProcessing(): bool
    {
        return in_array($this->processing_status, ['pending', 'processing'], true);
    }

    /** Localized display name, falling back across name_en → name. */
    public function displayName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $this->{"name_{$locale}"} ?: $this->name_ar ?: $this->name_en ?: (string) $this->name;
    }

    /** Tailwind colour token per status, for dashboard badges. */
    public function statusColor(): string
    {
        return match ($this->status) {
            'active' => 'emerald',
            'maintenance' => 'amber',
            default => 'rose',
        };
    }
}
