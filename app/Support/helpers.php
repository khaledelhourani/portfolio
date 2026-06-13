<?php

use Illuminate\Database\Eloquent\Model;

if (! function_exists('lf')) {
    /**
     * Localized field accessor. Returns `{$key}_{locale}` on a model, falling
     * back to the Arabic value, then any non-empty variant. Lets bilingual
     * (_ar/_en) columns render based on the active locale.
     */
    function lf(Model $model, string $key, ?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return $model->{"{$key}_{$locale}"}
            ?: $model->{"{$key}_ar"}
            ?: $model->{"{$key}_en"};
    }
}

if (! function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        return app()->getLocale() === 'ar';
    }
}

if (! function_exists('human_bytes')) {
    /** Format a byte count as a human-readable size (e.g. 1.4 MB). */
    function human_bytes(int $bytes, int $decimals = 1): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), $decimals) . ' ' . $units[$i];
    }
}

if (! function_exists('social_providers')) {
    /**
     * Social login providers that are fully configured (id + secret present).
     * Returns [driver => ['label' => ..., 'short' => ...]].
     */
    function social_providers(): array
    {
        $all = [
            'google' => ['label' => 'Google', 'short' => 'G'],
            'github' => ['label' => 'GitHub', 'short' => 'GH'],
            'linkedin-openid' => ['label' => 'LinkedIn', 'short' => 'in'],
            'twitter-oauth-2' => ['label' => 'X', 'short' => 'X'],
        ];

        return array_filter($all, function ($_, $driver) {
            $cfg = config("services.{$driver}");

            return ! empty($cfg['client_id']) && ! empty($cfg['client_secret']);
        }, ARRAY_FILTER_USE_BOTH);
    }
}
