<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'education';

    protected $fillable = [
        'degree', 'institution', 'start_year', 'end_year', 'description', 'sort_order',
    ];
}
