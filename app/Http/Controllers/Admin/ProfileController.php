<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.edit', ['profile' => Profile::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = Profile::current();

        $data = $request->validate([
            'name_ar' => ['required', 'string', 'max:120'],
            'name_en' => ['nullable', 'string', 'max:120'],
            'role_ar' => ['nullable', 'string', 'max:120'],
            'role_en' => ['nullable', 'string', 'max:120'],
            'bio_ar' => ['nullable', 'string', 'max:2000'],
            'bio_en' => ['nullable', 'string', 'max:2000'],
            'credential_badge_ar' => ['nullable', 'string', 'max:120'],
            'credential_badge_en' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'social.github' => ['nullable', 'url', 'max:255'],
            'social.instagram' => ['nullable', 'url', 'max:255'],
            'social.facebook' => ['nullable', 'url', 'max:255'],
            'social.linkedin' => ['nullable', 'url', 'max:255'],
            'social.twitter' => ['nullable', 'url', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cv_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:8192'],
        ]);

        $payload = collect($data)->except(['social', 'photo', 'cv_pdf'])->toArray();
        $payload['social_links'] = array_filter($request->input('social', []));

        if ($request->hasFile('photo')) {
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }
            $payload['photo'] = $request->file('photo')->store('profile', 'public');
        }

        if ($request->hasFile('cv_pdf')) {
            if ($profile->cv_pdf) {
                Storage::disk('public')->delete($profile->cv_pdf);
            }
            $payload['cv_pdf'] = $request->file('cv_pdf')->store('cv', 'public');
        }

        $profile->update($payload);

        return back()->with('status', 'تم حفظ الملف الشخصي بنجاح.');
    }
}
