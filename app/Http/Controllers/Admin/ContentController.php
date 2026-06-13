<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Skill;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manages the "premium" content blocks added in Chunk C:
 * Services, Skills and Testimonials. Type-keyed in the same spirit
 * as CvController so all three share one set of CRUD routes.
 */
class ContentController extends Controller
{
    /** type => [model, validation rules] */
    private function config(string $type): array
    {
        return match ($type) {
            'service' => [Service::class, [
                'title_ar' => ['required', 'string', 'max:160'],
                'title_en' => ['nullable', 'string', 'max:160'],
                'description_ar' => ['nullable', 'string', 'max:1000'],
                'description_en' => ['nullable', 'string', 'max:1000'],
                'icon' => ['nullable', 'string', 'max:40'],
                'price_range' => ['nullable', 'string', 'max:60'],
                'featured' => ['nullable', 'boolean'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            ]],
            'skill' => [Skill::class, [
                'name' => ['required', 'string', 'max:80'],
                'category' => ['required', 'in:frontend,backend,database,tools,ai'],
                'level' => ['required', 'integer', 'min:0', 'max:100'],
                'years' => ['nullable', 'string', 'max:40'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            ]],
            'testimonial' => [Testimonial::class, [
                'name_ar' => ['required', 'string', 'max:120'],
                'name_en' => ['nullable', 'string', 'max:120'],
                'company_ar' => ['nullable', 'string', 'max:160'],
                'company_en' => ['nullable', 'string', 'max:160'],
                'quote_ar' => ['required', 'string', 'max:1000'],
                'quote_en' => ['nullable', 'string', 'max:1000'],
                'rating' => ['required', 'integer', 'min:1', 'max:5'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            ]],
            default => abort(404),
        };
    }

    public function index(): View
    {
        return view('admin.content.index', [
            'services' => Service::orderBy('sort_order')->get(),
            'skills' => Skill::orderBy('category')->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        [$model, $rules] = $this->config($type);
        $data = $this->prepare($request->validate($rules), $type, $request);
        $model::create($data);

        return back()->with('status', 'تمت الإضافة.');
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        [$model, $rules] = $this->config($type);
        $data = $this->prepare($request->validate($rules), $type, $request);
        $model::findOrFail($id)->update($data);

        return back()->with('status', 'تم التحديث.');
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        [$model] = $this->config($type);
        $model::findOrFail($id)->delete();

        return back()->with('status', 'تم الحذف.');
    }

    /** Normalise input (service featured checkbox, default sort_order). */
    private function prepare(array $data, string $type, Request $request): array
    {
        if ($type === 'service') {
            $data['featured'] = $request->boolean('featured');
        }
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
