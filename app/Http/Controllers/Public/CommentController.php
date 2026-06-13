<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, BlogPost $post): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $post->comments()->create([
            'member_id' => $request->user('member')->id,
            'body' => $data['body'],
            'approved' => false, // awaits admin moderation
        ]);

        return back()->with('comment_status', 'تم إرسال تعليقك، سيظهر بعد المراجعة.');
    }
}
