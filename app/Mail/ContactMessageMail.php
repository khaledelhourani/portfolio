<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Message $message)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'رسالة تواصل جديدة: ' . ($this->message->subject ?: 'بدون موضوع'),
            replyTo: [$this->message->email],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}
