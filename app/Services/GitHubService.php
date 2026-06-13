<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Fetches a small, cached snapshot of the owner's public GitHub activity
 * for the "GitHub activity" widget (Premium Chunk D). All failures degrade
 * gracefully to null so the widget simply hides.
 */
class GitHubService
{
    /** Derive the GitHub username from the profile's social link URL. */
    public function username(): ?string
    {
        $url = Profile::current()->social_links['github'] ?? null;
        if (! $url) {
            return null;
        }

        // Accept a bare username or a github.com/<user> URL.
        $user = Str::of($url)
            ->after('github.com/')
            ->before('/')
            ->trim('/@ ')
            ->value();

        return $user !== '' ? $user : null;
    }

    /** Cached snapshot: profile stats + recent push events. Null on failure. */
    public function snapshot(): ?array
    {
        $user = $this->username();
        if (! $user) {
            return null;
        }

        return Cache::remember("github.snapshot.{$user}", now()->addMinutes(30), function () use ($user) {
            try {
                $req = Http::timeout(8)->withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'User-Agent' => config('app.name', 'Portfolio'),
                ]);

                if ($token = config('services.github.token')) {
                    $req = $req->withToken($token);
                }

                $profile = $req->get("https://api.github.com/users/{$user}");
                if (! $profile->successful()) {
                    return null;
                }

                $events = $req->get("https://api.github.com/users/{$user}/events/public", ['per_page' => 30]);

                return [
                    'username' => $user,
                    'url' => $profile['html_url'] ?? "https://github.com/{$user}",
                    'name' => $profile['name'] ?? $user,
                    'avatar' => $profile['avatar_url'] ?? null,
                    'public_repos' => $profile['public_repos'] ?? 0,
                    'followers' => $profile['followers'] ?? 0,
                    'recent' => $this->summarisePushes($events->successful() ? $events->json() : []),
                ];
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /** Reduce raw event feed to the latest few push events with commit counts. */
    private function summarisePushes(array $events): array
    {
        return collect($events)
            ->where('type', 'PushEvent')
            ->take(5)
            ->map(fn ($e) => [
                'repo' => $e['repo']['name'] ?? '—',
                'commits' => count($e['payload']['commits'] ?? []),
                'at' => $e['created_at'] ?? null,
            ])
            ->values()
            ->all();
    }
}
