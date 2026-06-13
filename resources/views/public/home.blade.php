@extends('layouts.app')

@section('title', lf($profile, 'name') . ' — ' . __('Web Developer'))

@section('content')
    @include('public.partials.hero')
    @include('public.partials.services')
    @include('public.partials.skills')
    @include('public.partials.cv')
    @include('public.partials.github')
    @include('public.partials.testimonials')
    @include('public.partials.contact')
@endsection
