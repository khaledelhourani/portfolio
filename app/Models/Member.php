<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Public site member authenticated via a social provider (Part 5).
 * Uses the `members` auth guard, separate from admin `users`.
 */
class Member extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'provider',
        'provider_id',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function favoriteProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'favorites')->withTimestamps();
    }
}
