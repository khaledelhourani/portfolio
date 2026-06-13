<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        return view('admin.blog.index', [
            'posts' => BlogPost::with('category')->latest()->paginate(15),
            'categories' => BlogCategory::withCount('posts')->orderBy('name')->get(),
            'pendingComments' => Comment::where('approved', false)->with(['member', 'post'])->latest()->get(),
        ]);
    }

    public function approveComment(Comment $comment): RedirectResponse
    {
        $comment->update(['approved' => true]);

        return back()->with('status', 'تمت الموافقة على التعليق.');
    }

    public function destroyComment(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('status', 'تم حذف التعليق.');
    }

    public function create(): View
    {
        return view('admin.blog.create', [
            'post' => new BlogPost(['status' => 'draft']),
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function store(BlogPostRequest $request): RedirectResponse
    {
        $post = BlogPost::create($this->prepare($request) + ['user_id' => $request->user()->id]);
        $this->syncTags($post, $request->input('tags'));

        return redirect()->route('admin.blog.index')->with('status', 'تم إنشاء المقال.');
    }

    public function edit(BlogPost $post): View
    {
        return view('admin.blog.edit', [
            'post' => $post->load('tags'),
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function update(BlogPostRequest $request, BlogPost $post): RedirectResponse
    {
        $post->update($this->prepare($request, $post));
        $this->syncTags($post, $request->input('tags'));

        return redirect()->route('admin.blog.index')->with('status', 'تم تحديث المقال.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }
        $post->delete();

        return redirect()->route('admin.blog.index')->with('status', 'تم حذف المقال.');
    }

    public function storeCategory(\Illuminate\Http\Request $request): RedirectResponse
    {
        $name = $request->validate(['name' => ['required', 'string', 'max:80']])['name'];
        BlogCategory::firstOrCreate(['slug' => Str::slug($name) ?: Str::random(6)], ['name' => $name]);

        return back()->with('status', 'تمت إضافة التصنيف.');
    }

    public function destroyCategory(BlogCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('status', 'تم حذف التصنيف.');
    }

    private function prepare(BlogPostRequest $request, ?BlogPost $post = null): array
    {
        $data = $request->safe()->only(['title', 'blog_category_id', 'excerpt', 'content', 'status', 'scheduled_at']);

        // Slug
        if (! $post || blank($post->slug)) {
            $base = Str::slug($request->input('slug') ?: $request->input('title')) ?: 'post';
            $slug = $base;
            $i = 1;
            while (BlogPost::where('slug', $slug)->when($post, fn ($q) => $q->where('id', '!=', $post->id))->exists()) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        } elseif ($request->filled('slug')) {
            $data['slug'] = Str::slug($request->input('slug'));
        }

        // Reading time (~200 wpm) from plain text.
        $words = str_word_count(strip_tags((string) $request->input('content')));
        $data['reading_time'] = max(1, (int) ceil($words / 200));

        // Publish timestamps.
        if ($data['status'] === 'published') {
            $data['published_at'] = $post?->published_at ?? now();
            $data['scheduled_at'] = null;
        } elseif ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        if ($request->hasFile('featured_image')) {
            if ($post?->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        return $data;
    }

    private function syncTags(BlogPost $post, ?string $tags): void
    {
        $ids = collect(explode(',', (string) $tags))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->map(fn ($name) => Tag::firstOrCreate(['slug' => Str::slug($name) ?: Str::random(6)], ['name' => $name])->id)
            ->all();

        $post->tags()->sync($ids);
    }
}
