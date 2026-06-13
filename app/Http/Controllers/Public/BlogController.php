<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = BlogPost::query()
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->with('category')
            ->when($request->query('category'), fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($request->query('tag'), fn ($q, $slug) => $q->whereHas('tags', fn ($t) => $t->where('slug', $slug)))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('public.blog.index', [
            'posts' => $posts,
            'categories' => BlogCategory::withCount(['posts' => fn ($q) => $q->where('status', 'published')])->orderBy('name')->get(),
            'activeCategory' => $request->query('category'),
        ]);
    }

    public function show(string $slug): View
    {
        $post = BlogPost::where('slug', $slug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->with(['category', 'tags', 'author', 'comments' => fn ($q) => $q->where('approved', true)->latest()->with('member')])
            ->firstOrFail();

        $post->increment('views');

        return view('public.blog.show', compact('post'));
    }
}
