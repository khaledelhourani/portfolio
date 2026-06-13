@extends('layouts.admin')
@section('title', 'تعديل: ' . $post->title)
@section('breadcrumb', 'تعديل المقال')
@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="font-display text-2xl font-bold text-ink">تعديل المقال</h1>
        @if ($post->status === 'published')
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="btn-outline">عرض ↗</a>
        @endif
    </div>
    @include('admin.blog._form')
@endsection
