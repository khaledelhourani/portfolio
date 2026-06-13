@extends('layouts.admin')

@section('title', 'الملف الشخصي')
@section('breadcrumb', 'الملف الشخصي')

@section('content')
@php $social = $profile->social_links ?? []; @endphp
<form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="mx-auto max-w-4xl space-y-6">
    @csrf @method('PUT')

    <div class="flex items-center justify-between">
        <h1 class="font-display text-2xl font-bold text-ink">الملف الشخصي</h1>
        <button class="btn-cyan">حفظ التغييرات</button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-accent-success/30 bg-accent-success/10 px-4 py-3 text-sm text-accent-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="glass-card p-6">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink">الاسم والدور</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.field name="name_ar" label="الاسم (عربي) *" :value="old('name_ar', $profile->name_ar)" required />
                    <x-admin.field name="name_en" label="الاسم (إنجليزي)" :value="old('name_en', $profile->name_en)" dir="ltr" />
                    <x-admin.field name="role_ar" label="الدور (عربي)" :value="old('role_ar', $profile->role_ar)" />
                    <x-admin.field name="role_en" label="الدور (إنجليزي)" :value="old('role_en', $profile->role_en)" dir="ltr" />
                    <x-admin.field name="credential_badge_ar" label="الشارة (عربي)" :value="old('credential_badge_ar', $profile->credential_badge_ar)" />
                    <x-admin.field name="credential_badge_en" label="الشارة (إنجليزي)" :value="old('credential_badge_en', $profile->credential_badge_en)" dir="ltr" />
                </div>
            </div>

            <div class="glass-card p-6">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink">النبذة</h2>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">نبذة (عربي)</label>
                <textarea name="bio_ar" rows="3" class="mb-4 w-full resize-none rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">{{ old('bio_ar', $profile->bio_ar) }}</textarea>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">نبذة (إنجليزي)</label>
                <textarea name="bio_en" rows="3" dir="ltr" class="w-full resize-none rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">{{ old('bio_en', $profile->bio_en) }}</textarea>
            </div>

            <div class="glass-card p-6">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink">روابط التواصل الاجتماعي</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach (['github' => 'GitHub', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'twitter' => 'X / Twitter'] as $k => $lbl)
                        <x-admin.field name="social[{{ $k }}]" :label="$lbl" :value="old('social.'.$k, $social[$k] ?? '')" dir="ltr" type="url" placeholder="https://..." />
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-card p-6">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink">التواصل</h2>
                <div class="space-y-4">
                    <x-admin.field name="city" label="المدينة" :value="old('city', $profile->city)" />
                    <x-admin.field name="email" label="البريد" :value="old('email', $profile->email)" dir="ltr" type="email" />
                    <x-admin.field name="phone" label="الهاتف" :value="old('phone', $profile->phone)" dir="ltr" />
                </div>
            </div>

            <div class="glass-card p-6" x-data="{ preview: '{{ $profile->photo ? \Illuminate\Support\Facades\Storage::url($profile->photo) : '' }}' }">
                <h2 class="mb-4 font-display text-lg font-semibold text-ink">الصورة</h2>
                <div class="mb-3 aspect-square overflow-hidden rounded-xl border border-base-border bg-base-tag">
                    <template x-if="preview"><img :src="preview" class="h-full w-full object-cover"></template>
                    <template x-if="!preview"><div class="grid h-full place-items-center text-ink-muted"><span class="text-xs">لا توجد صورة</span></div></template>
                </div>
                <input type="file" name="photo" accept="image/*" @change="const f=$event.target.files[0]; if(f) preview=URL.createObjectURL(f)"
                       class="block w-full text-sm text-ink-muted file:me-3 file:rounded-lg file:border-0 file:bg-base-tag file:px-3 file:py-2 file:text-sm file:text-ink hover:file:bg-white/10">
            </div>

            <div class="glass-card p-6">
                <h2 class="mb-3 font-display text-lg font-semibold text-ink">السيرة (PDF)</h2>
                @if ($profile->cv_pdf)
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($profile->cv_pdf) }}" target="_blank" class="mb-3 block text-sm text-accent-cyan hover:underline">عرض الـ CV الحالي ↗</a>
                @endif
                <input type="file" name="cv_pdf" accept="application/pdf"
                       class="block w-full text-sm text-ink-muted file:me-3 file:rounded-lg file:border-0 file:bg-base-tag file:px-3 file:py-2 file:text-sm file:text-ink hover:file:bg-white/10">
            </div>
        </div>
    </div>
</form>
@endsection
