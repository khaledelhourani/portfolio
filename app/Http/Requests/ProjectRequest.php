<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title_ar' => ['required', 'string', 'max:160'],
            'title_en' => ['nullable', 'string', 'max:160'],
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'type' => ['nullable', 'string', 'max:60'],
            'duration' => ['nullable', 'string', 'max:60'],
            'description_ar' => ['nullable', 'string', 'max:2000'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'tech_stack' => ['nullable', 'string', 'max:500'], // comma-separated
            'github_url' => ['nullable', 'url', 'max:255'],
            'demo_url' => ['nullable', 'url', 'max:255'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'core_focus' => ['nullable', 'string', 'max:2000'],
            'architecture' => ['nullable', 'string', 'max:2000'],
            'mitigation' => ['nullable', 'string', 'max:2000'],
            'featured' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,published'],
        ];
    }
}
