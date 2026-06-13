<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'name', 'category', 'level', 'years', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['level' => 'integer'];
    }
}
