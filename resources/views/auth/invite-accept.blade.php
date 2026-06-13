@extends('layouts.app')
@section('title', 'تفعيل حساب المشرف')

@section('content')
<section class="mx-auto flex min-h-[75vh] max-w-md flex-col justify-center px-4 py-12">
    <div class="glass-card p-8">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-accent-gradient font-display text-2xl font-bold text-base-bg shadow-glow">خ</div>
            <h1 class="font-display text-2xl font-bold text-ink">تفعيل حساب المشرف</h1>
            <p class="mt-1 text-sm text-ink-muted">دُعيت للانضمام بدور <span class="text-accent-cyan">{{ $invite->role }}</span></p>
            <p class="text-xs text-ink-muted" dir="ltr">{{ $invite->email }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"><ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('admin.invite.accept.submit', $token) }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">الاسم</label>
                <input name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">كلمة المرور</label>
                <input name="password" type="password" required dir="ltr" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-ink-muted">تأكيد كلمة المرور</label>
                <input name="password_confirmation" type="password" required dir="ltr" class="w-full rounded-xl border border-base-border bg-base-bg/60 px-4 py-3 text-ink outline-none focus:border-accent-cyan">
            </div>
            <button class="btn-cyan w-full">تفعيل الحساب والدخول</button>
        </form>
    </div>
</section>
@endsection
