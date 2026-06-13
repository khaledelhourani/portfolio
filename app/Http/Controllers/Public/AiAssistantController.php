<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    private const DAILY_LIMIT = 20;

    public function __construct(private readonly AiAssistantService $ai)
    {
    }

    public function show(): View
    {
        return view('public.ai-assistant', [
            'enabled' => $this->ai->isEnabled(),
            'suggested' => $this->ai->suggestedQuestions(),
            'providerLabel' => $this->ai->providerLabel(),
            'dailyLimit' => self::DAILY_LIMIT,
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        if (! $this->ai->isEnabled()) {
            return response()->json([
                'error' => 'المساعد الذكي غير مُفعّل حالياً. الرجاء المحاولة لاحقاً.',
            ], 503);
        }

        $data = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:40'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:4000'],
        ]);

        // Rate limit: 20 messages per IP per day.
        $key = 'ai-chat:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, self::DAILY_LIMIT)) {
            return response()->json([
                'error' => 'لقد بلغت الحد اليومي (' . self::DAILY_LIMIT . ' رسالة). عد غداً 🙏',
            ], 429);
        }

        try {
            $reply = $this->ai->reply($data['messages']);
        } catch (\Throwable $e) {
            report($e);

            // Surface provider rate-limit as a friendlier, distinct message.
            if (str_contains($e->getMessage(), '429')) {
                return response()->json([
                    'error' => 'المساعد مشغول حالياً (تجاوز حد الطلبات). انتظر دقيقة وحاول مجدداً.',
                ], 429);
            }

            return response()->json([
                'error' => 'حدث خطأ أثناء التواصل مع المساعد. حاول مرة أخرى.',
            ], 502);
        }

        RateLimiter::hit($key, 86400); // count this exchange, resets after 24h

        return response()->json([
            'reply' => $reply,
            'remaining' => RateLimiter::remaining($key, self::DAILY_LIMIT),
        ]);
    }
}
