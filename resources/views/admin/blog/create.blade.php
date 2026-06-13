@extends('layouts.admin')
@section('title', 'مقال جديد')
@section('breadcrumb', 'مقال جديد')
@section('content')
    <h1 class="mb-6 font-display text-2xl font-bold text-ink">مقال جديد</h1>
    @include('admin.blog._form')
@endsection
