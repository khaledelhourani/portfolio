<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Education;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Setting;
use App\Models\WorkExperience;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Talks to a chat LLM on behalf of the public "Khalid AI" page. Provider is
 * switchable (Gemini / Groq / Anthropic) — default Gemini's free tier. The
 * system prompt is assembled dynamically from the owner's live CMS data so
 * answers always reflect the current profile, CV and published projects.
 */
class AiAssistantService
{
    /** Human-readable labels shown in the UI ("Powered by …"). */
    private const LABELS = [
        'gemini' => 'Google Gemini',
        'groq' => 'Groq · Llama',
        'anthropic' => 'Anthropic Claude',
    ];

    public function provider(): string
    {
        $p = Setting::get('ai_provider') ?: config('portfolio.ai.provider', 'gemini');

        return array_key_exists($p, self::LABELS) ? $p : 'gemini';
    }

    public function providerLabel(): string
    {
        return self::LABELS[$this->provider()];
    }

    public function isEnabled(): bool
    {
        return (bool) Setting::get('ai_assistant_enabled', true) && filled($this->apiKey());
    }

    /** Key for the active provider: encrypted CMS setting first, then config/.env. */
    private function apiKey(): ?string
    {
        $p = $this->provider();

        return Setting::get("{$p}_api_key") ?: config("portfolio.ai.{$p}.key");
    }

    private function model(): string
    {
        $p = $this->provider();

        return Setting::get("{$p}_model") ?: config("portfolio.ai.{$p}.model");
    }

    /**
     * Send the running conversation and return the assistant's reply text.
     *
     * @param  array<int, array{role: string, content: string}>  $history
     */
    public function reply(array $history): string
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('AI assistant is not configured.');
        }

        $text = match ($this->provider()) {
            'anthropic' => $this->callAnthropic($history),
            'groq' => $this->callGroq($history),
            default => $this->callGemini($history),
        };

        return trim($text) ?: 'لم أتمكن من توليد رد. حاول مرة أخرى.';
    }

    // ----------------------------------------------------------------- Gemini
    private function callGemini(array $history): string
    {
        $contents = array_map(fn ($m) => [
            'role' => $m['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $m['content']]],
        ], $history);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model()}:generateContent";

        $res = Http::timeout(60)
            ->withHeaders(['x-goog-api-key' => $this->apiKey()])
            ->post($url, [
                'system_instruction' => ['parts' => [['text' => $this->systemPrompt()]]],
                'contents' => $contents,
                'generationConfig' => ['maxOutputTokens' => 1024, 'temperature' => 0.7],
            ]);

        $this->assertOk($res, 'Gemini');

        return collect($res->json('candidates.0.content.parts', []))->pluck('text')->implode('');
    }

    // ------------------------------------------------------------------- Groq
    private function callGroq(array $history): string
    {
        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt()]],
            $history,
        );

        $res = Http::timeout(60)
            ->withToken($this->apiKey())
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $this->model(),
                'messages' => $messages,
                'max_tokens' => 1024,
                'temperature' => 0.7,
            ]);

        $this->assertOk($res, 'Groq');

        return (string) $res->json('choices.0.message.content', '');
    }

    // -------------------------------------------------------------- Anthropic
    private function callAnthropic(array $history): string
    {
        $res = Http::timeout(60)->withHeaders([
            'x-api-key' => $this->apiKey(),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->model(),
            'max_tokens' => 1024,
            'system' => $this->systemPrompt(),
            'messages' => $history,
        ]);

        $this->assertOk($res, 'Anthropic');

        return collect($res->json('content', []))->where('type', 'text')->pluck('text')->implode('');
    }

    private function assertOk(\Illuminate\Http\Client\Response $res, string $provider): void
    {
        if ($res->failed()) {
            throw new RuntimeException("{$provider} API error: " . $res->status() . ' ' . $res->body());
        }
    }

    // -------------------------------------------------------- System prompt
    /** Compose the system prompt from the configured base + live owner data. */
    public function systemPrompt(): string
    {
        $base = Setting::get('ai_system_prompt')
            ?: 'أنت مساعد خالد الحوراني الذكي. أجب باختصار وبنفس لغة السؤال (عربي أو إنجليزي) معتمداً على بيانات السيرة والمشاريع أدناه فقط. إن سُئلت عن شيء خارج هذه البيانات، قل بلطف إنك لا تملك هذه المعلومة.';

        return $base . "\n\n--- بيانات خالد الحوراني (المصدر الوحيد للحقائق) ---\n" . $this->knowledgeBase();
    }

    private function knowledgeBase(): string
    {
        $p = Profile::current();

        $lines = [];
        $lines[] = "الاسم: {$p->name_ar}" . ($p->name_en ? " ({$p->name_en})" : '');
        $lines[] = "الدور: {$p->role_ar}" . ($p->role_en ? " / {$p->role_en}" : '');
        if ($p->city) $lines[] = "المدينة: {$p->city}";
        if ($p->email) $lines[] = "البريد: {$p->email}";
        if ($p->phone) $lines[] = "الهاتف: {$p->phone}";
        if ($p->bio_ar) $lines[] = "نبذة: {$p->bio_ar}";
        if ($p->bio_en) $lines[] = "Bio (EN): {$p->bio_en}";

        $exp = WorkExperience::orderByDesc('start_date')->get();
        if ($exp->isNotEmpty()) {
            $lines[] = "\nالخبرات العملية:";
            foreach ($exp as $e) {
                $period = optional($e->start_date)->format('Y') . ' - ' . ($e->is_current ? 'حتى الآن' : optional($e->end_date)->format('Y'));
                $lines[] = "• {$e->role} @ {$e->company} ({$period})" . ($e->location ? " — {$e->location}" : '');
                foreach (($e->bullets ?? []) as $b) {
                    $lines[] = "   - {$b}";
                }
            }
        }

        $edu = Education::orderBy('sort_order')->get();
        if ($edu->isNotEmpty()) {
            $lines[] = "\nالتعليم:";
            foreach ($edu as $e) {
                $lines[] = "• {$e->degree} — {$e->institution} ({$e->start_year}-{$e->end_year})";
            }
        }

        $certs = Certificate::orderBy('sort_order')->get();
        if ($certs->isNotEmpty()) {
            $lines[] = "\nالشهادات:";
            foreach ($certs as $c) {
                $lines[] = "• {$c->title} — {$c->issuer}" . ($c->issue_date ? " ({$c->issue_date->format('Y')})" : '');
            }
        }

        $projects = Project::published()->ordered()->get();
        if ($projects->isNotEmpty()) {
            $lines[] = "\nالمشاريع المنشورة:";
            foreach ($projects as $pr) {
                $tech = is_array($pr->tech_stack) ? implode(', ', $pr->tech_stack) : '';
                $desc = $pr->description_ar ?: $pr->description_en;
                $lines[] = "• {$pr->title_ar}" . ($pr->type ? " [{$pr->type}]" : '') . ($tech ? " — تقنيات: {$tech}" : '');
                if ($desc) $lines[] = "   {$desc}";
                if ($pr->github_url) $lines[] = "   GitHub: {$pr->github_url}";
            }
        }

        // Free-form facts the owner fed the assistant from the AI manager page.
        if ($extra = trim((string) Setting::get('ai_extra_knowledge'))) {
            $lines[] = "\nمعلومات إضافية:";
            $lines[] = $extra;
        }

        return implode("\n", $lines);
    }

    /** Suggested starter questions shown as pills under the chat. */
    public function suggestedQuestions(): array
    {
        $custom = collect(preg_split('/\r\n|\r|\n/', (string) Setting::get('ai_suggested_questions')))
            ->map(fn ($q) => trim($q))
            ->filter()
            ->values();

        if ($custom->isNotEmpty()) {
            return $custom->take(8)->all();
        }

        return [
            'مين هو خالد الحوراني؟',
            'شو أبرز مشاريعك التقنية؟',
            'شو التقنيات اللي بتشتغل فيها؟',
            'كيف أقدر أتواصل معك؟',
        ];
    }
}
