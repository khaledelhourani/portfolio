<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name_ar', 'name_en', 'company_ar', 'company_en',
        'quote_ar', 'quote_en', 'avatar', 'rating', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }
}
