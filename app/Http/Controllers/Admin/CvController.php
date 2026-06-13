<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Profile;
use App\Models\WorkExperience;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CvController extends Controller
{
    /** type => [model, validation rules] */
    private function config(string $type): array
    {
        return match ($type) {
            'experience' => [WorkExperience::class, [
                'role' => ['required', 'string', 'max:160'],
                'company' => ['required', 'string', 'max:160'],
                'location' => ['nullable', 'string', 'max:160'],
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date'],
                'is_current' => ['nullable', 'boolean'],
                'badge' => ['nullable', 'string', 'max:60'],
                'bullets_text' => ['nullable', 'string', 'max:2000'],
            ]],
            'education' => [Education::class, [
                'degree' => ['required', 'string', 'max:160'],
                'institution' => ['required', 'string', 'max:160'],
                'start_year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
                'end_year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
                'description' => ['nullable', 'string', 'max:1000'],
            ]],
            'certificate' => [Certificate::class, [
                'title' => ['required', 'string', 'max:160'],
                'issuer' => ['required', 'string', 'max:160'],
                'issue_date' => ['nullable', 'date'],
                'credential_url' => ['nullable', 'url', 'max:255'],
            ]],
            default => abort(404),
        };
    }

    public function index(): View
    {
        return view('admin.cv.index', [
            'profile' => Profile::current(),
            'experiences' => WorkExperience::orderBy('sort_order')->orderByDesc('start_date')->get(),
            'education' => Education::orderBy('sort_order')->get(),
            'certificates' => Certificate::orderBy('sort_order')->get(),
        ]);
    }

    /** Upload (or replace) the downloadable CV PDF shown on the public site. */
    public function uploadPdf(Request $request): RedirectResponse
    {
        $request->validate([
            'cv_pdf' => ['required', 'file', 'mimes:pdf', 'max:8192'],
        ]);

        $profile = Profile::current();

        if ($profile->cv_pdf) {
            Storage::disk('public')->delete($profile->cv_pdf);
        }

        $profile->update([
            'cv_pdf' => $request->file('cv_pdf')->store('cv', 'public'),
        ]);

        return back()->with('status', 'تم رفع ملف السيرة (PDF).');
    }

    public function deletePdf(): RedirectResponse
    {
        $profile = Profile::current();

        if ($profile->cv_pdf) {
            Storage::disk('public')->delete($profile->cv_pdf);
            $profile->update(['cv_pdf' => null]);
        }

        return back()->with('status', 'تم حذف ملف السيرة.');
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

    /** Normalise input (experience bullets text → array, current flag). */
    private function prepare(array $data, string $type, Request $request): array
    {
        if ($type === 'experience') {
            $data['is_current'] = $request->boolean('is_current');
            $data['bullets'] = collect(preg_split('/\r\n|\r|\n/', (string) ($data['bullets_text'] ?? '')))
                ->map(fn ($b) => trim($b))->filter()->values()->all();
            unset($data['bullets_text']);
            if ($data['is_current']) {
                $data['end_date'] = null;
            }
        }

        return $data;
    }
}
