<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    protected $fillable = [
        'role', 'company', 'location', 'start_date', 'end_date',
        'is_current', 'bullets', 'badge', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'bullets' => 'array',
        ];
    }
}
