<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'ip', 'user_agent', 'browser', 'platform', 'device',
        'country', 'country_code', 'region', 'city',
        'page_url', 'referrer', 'is_bot', 'read_at', 'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'is_bot' => 'boolean',
            'read_at' => 'datetime',
            'visited_at' => 'datetime',
        ];
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeHumans(Builder $query): Builder
    {
        return $query->where('is_bot', false);
    }
}
