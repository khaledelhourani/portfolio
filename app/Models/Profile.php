<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'name_ar', 'name_en', 'role_ar', 'role_en',
        'bio_ar', 'bio_en', 'credential_badge_ar', 'credential_badge_en',
        'photo', 'cv_pdf', 'city', 'email', 'phone', 'social_links',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
        ];
    }

    /** The owner profile is a single row; fetch or create it. */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
