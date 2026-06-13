<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MessagesController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');

        $messages = Message::query()
            ->when($filter === 'unread', fn ($q) => $q->where('status', 'unread'))
            ->when($filter === 'archived', fn ($q) => $q->where('status', 'archived'))
            ->when($filter === 'all', fn ($q) => $q->where('status', '!=', 'archived'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.messages.index', [
            'messages' => $messages,
            'filter' => $filter,
            'unreadCount' => Message::where('status', 'unread')->count(),
        ]);
    }

    public function show(Message $message): View
    {
        if ($message->status === 'unread') {
            $message->update(['status' => 'read']);
        }

        return view('admin.messages.show', compact('message'));
    }

    public function reply(Request $request, Message $message): RedirectResponse
    {
        $data = $request->validate([
            'reply' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        $from = Setting::get('contact_email', config('mail.from.address'));

        try {
            Mail::raw($data['reply'], function ($mail) use ($message, $from) {
                $mail->to($message->email, $message->name)
                    ->subject('رد على رسالتك: ' . ($message->subject ?: 'تواصل'))
                    ->replyTo($from);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['reply' => 'تعذّر إرسال البريد. تحقّق من إعدادات SMTP.']);
        }

        $message->update(['status' => 'replied', 'replied_at' => now()]);

        return back()->with('status', 'تم إرسال الرد بنجاح.');
    }

    public function update(Request $request, Message $message): RedirectResponse
    {
        $status = $request->validate(['status' => ['required', 'in:unread,read,replied,archived']])['status'];
        $message->update(['status' => $status]);

        return back()->with('status', 'تم تحديث حالة الرسالة.');
    }

    public function destroy(Message $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('status', 'تم حذف الرسالة.');
    }
}
