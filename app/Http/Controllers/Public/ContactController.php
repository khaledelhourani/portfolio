<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageMail;
use App\Models\Message;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(ContactRequest $request): JsonResponse
    {
        $message = Message::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'subject' => $request->input('subject'),
            'body' => $request->string('body'),
            'status' => 'unread',
            'ip' => $request->ip(),
        ]);

        // Notify the owner (uses the configured mailer; logs in local dev).
        $to = Setting::get('contact_email', config('mail.from.address'));

        if ($to) {
            try {
                Mail::to($to)->send(new ContactMessageMail($message));
            } catch (\Throwable $e) {
                report($e); // don't fail the submission if mail transport is down
            }
        }

        return response()->json([
            'ok' => true,
            'message' => __('تم استلام رسالتك بنجاح! سأعود إليك قريباً.'),
        ]);
    }
}
