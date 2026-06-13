<x-mail::message>
# رسالة تواصل جديدة

**الاسم:** {{ $message->name }}
**البريد:** {{ $message->email }}
**الموضوع:** {{ $message->subject ?: '—' }}

---

{{ $message->body }}

---

<small>وردت من {{ $message->ip }} بتاريخ {{ $message->created_at->format('Y-m-d H:i') }}</small>
</x-mail::message>
