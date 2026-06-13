<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AiAssistantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "Feed the AI" manager — lets the owner tune the public assistant:
 * provider + model + API key, base instructions (system prompt), a free-form
 * knowledge base, and the suggested starter questions.
 */
class AiSettingsController extends Controller
{
    private const PROVIDERS = ['gemini', 'groq', 'anthropic'];

    public function edit(AiAssistantService $ai): View
    {
        $providers = [];
        foreach (self::PROVIDERS as $p) {
            $providers[$p] = [
                'model' => Setting::get("{$p}_model"),
                'model_default' => config("portfolio.ai.{$p}.model"),
                'has_key' => filled(Setting::get("{$p}_api_key")) || filled(config("portfolio.ai.{$p}.key")),
            ];
        }

        return view('admin.ai.index', [
            'provider' => $ai->provider(),
            'enabled' => (bool) Setting::get('ai_assistant_enabled', true),
            'systemPrompt' => Setting::get('ai_system_prompt'),
            'extraKnowledge' => Setting::get('ai_extra_knowledge'),
            'suggested' => Setting::get('ai_suggested_questions'),
            'providers' => $providers,
            'isEnabled' => $ai->isEnabled(),
            'providerLabel' => $ai->providerLabel(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ai_provider' => ['required', 'in:' . implode(',', self::PROVIDERS)],
            'ai_assistant_enabled' => ['nullable', 'boolean'],
            'ai_system_prompt' => ['nullable', 'string', 'max:8000'],
            'ai_extra_knowledge' => ['nullable', 'string', 'max:20000'],
            'ai_suggested_questions' => ['nullable', 'string', 'max:2000'],
            'gemini_model' => ['nullable', 'string', 'max:120'],
            'groq_model' => ['nullable', 'string', 'max:120'],
            'anthropic_model' => ['nullable', 'string', 'max:120'],
            'gemini_api_key' => ['nullable', 'string', 'max:300'],
            'groq_api_key' => ['nullable', 'string', 'max:300'],
            'anthropic_api_key' => ['nullable', 'string', 'max:300'],
        ]);

        Setting::set('ai_provider', $data['ai_provider'], 'string', 'ai');
        Setting::set('ai_assistant_enabled', $request->boolean('ai_assistant_enabled'), 'bool', 'ai');
        Setting::set('ai_system_prompt', (string) ($data['ai_system_prompt'] ?? ''), 'text', 'ai');
        Setting::set('ai_extra_knowledge', (string) ($data['ai_extra_knowledge'] ?? ''), 'text', 'ai');
        Setting::set('ai_suggested_questions', (string) ($data['ai_suggested_questions'] ?? ''), 'text', 'ai');

        foreach (self::PROVIDERS as $p) {
            if (array_key_exists("{$p}_model", $data)) {
                Setting::set("{$p}_model", (string) ($data["{$p}_model"] ?? ''), 'string', 'ai');
            }
            // Only overwrite a key when a new value is actually entered, so a
            // blank field never wipes a saved key.
            if (filled($data["{$p}_api_key"] ?? null)) {
                Setting::set("{$p}_api_key", $data["{$p}_api_key"], 'string', 'ai', encrypt: true);
            }
        }

        return back()->with('status', 'تم حفظ إعدادات المساعد الذكي.');
    }
}
