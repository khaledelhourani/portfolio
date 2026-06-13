<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'is_encrypted'];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    /** Read a setting value (decrypting + casting as needed). */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::rememberForever("setting:{$key}", fn () => static::where('key', $key)->first());

        if (! $setting) {
            return $default;
        }

        $value = $setting->is_encrypted && $setting->value
            ? Crypt::decryptString($setting->value)
            : $setting->value;

        return match ($setting->type) {
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /** Write a setting value (encrypting when flagged). */
    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general', bool $encrypt = false): void
    {
        $stored = $type === 'json' ? json_encode($value) : (string) $value;

        if ($encrypt && $stored !== '') {
            $stored = Crypt::encryptString($stored);
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'type' => $type, 'group' => $group, 'is_encrypted' => $encrypt],
        );

        Cache::forget("setting:{$key}");
    }
}
