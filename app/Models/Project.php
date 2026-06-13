<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    protected $fillable = [
        'project_category_id', 'title_ar', 'title_en', 'slug', 'type', 'duration',
        'description_ar', 'description_en', 'tech_stack', 'github_url', 'demo_url',
        'thumbnail', 'core_focus', 'architecture', 'mitigation',
        'featured', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'tech_stack' => 'array',
            'featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'favorites')->withTimestamps();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }
}
