<?php

namespace App\Services;

use App\Events\VisitorArrived;
use App\Models\Visitor;
use Illuminate\Http\Request;

/**
 * Records a single page visit: parses the user agent, detects bots, resolves
 * geo info, persists a Visitor row and fires VisitorArrived.
 */
class VisitorTrackerService
{
    public function __construct(private readonly GeoIpService $geo)
    {
    }

    public function track(Request $request): ?Visitor
    {
        $agent = (string) $request->userAgent();
        $isBot = $this->isBot($agent);
        $ip = (string) $request->ip();

        // De-duplicate by device IP: a returning visitor refreshes their
        // existing row (current page + last-seen time) instead of creating a
        // new one — so each device shows up exactly once and only notifies on
        // its first visit. No duplicate rows, no repeated notifications.
        $existing = Visitor::where('ip', $ip)->latest('id')->first();

        if ($existing) {
            $existing->update([
                'browser' => $this->browser($agent),
                'platform' => $this->platform($agent),
                'device' => $this->device($agent),
                'page_url' => mb_substr($request->fullUrl(), 0, 255),
                'visited_at' => now(),
            ]);

            return $existing;
        }

        // Skip geo lookups for bots — just log them as bots.
        $geo = $isBot
            ? ['country' => null, 'country_code' => null, 'region' => null, 'city' => null]
            : $this->geo->lookup($ip);

        $visitor = Visitor::create([
            'ip' => $ip,
            'user_agent' => mb_substr($agent, 0, 1000),
            'browser' => $this->browser($agent),
            'platform' => $this->platform($agent),
            'device' => $this->device($agent),
            'country' => $geo['country'],
            'country_code' => $geo['country_code'],
            'region' => $geo['region'],
            'city' => $geo['city'],
            'page_url' => mb_substr($request->fullUrl(), 0, 255),
            'referrer' => $request->headers->get('referer') ? mb_substr($request->headers->get('referer'), 0, 255) : null,
            'is_bot' => $isBot,
            'visited_at' => now(),
        ]);

        VisitorArrived::dispatch($visitor);

        return $visitor;
    }

    private function isBot(string $agent): bool
    {
        if ($agent === '') {
            return true;
        }

        return (bool) preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|whatsapp|telegram|crawler|fetch|monitor|preview|headless|python-requests|curl|wget|axios|go-http/i', $agent);
    }

    private function browser(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Edg') => 'Edge',
            str_contains($agent, 'OPR') || str_contains($agent, 'Opera') => 'Opera',
            str_contains($agent, 'Brave') => 'Brave',
            str_contains($agent, 'Firefox') => 'Firefox',
            str_contains($agent, 'Chrome') => 'Chrome',
            str_contains($agent, 'Safari') => 'Safari',
            default => 'Unknown',
        };
    }

    private function platform(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone') || str_contains($agent, 'iPad') || str_contains($agent, 'iOS') => 'iOS',
            str_contains($agent, 'Mac OS') || str_contains($agent, 'Macintosh') => 'macOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'Unknown',
        };
    }

    private function device(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'iPad') || str_contains($agent, 'Tablet') => 'tablet',
            str_contains($agent, 'Mobile') || str_contains($agent, 'Android') || str_contains($agent, 'iPhone') => 'mobile',
            default => 'desktop',
        };
    }
}
