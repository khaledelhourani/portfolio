<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Resolves an IP address to geo info via ip-api.com (free tier, no key).
 * Results are cached per IP for a day to stay well under the rate limit.
 */
class GeoIpService
{
    public function lookup(string $ip): array
    {
        // Local / private addresses won't resolve — label them for dev clarity.
        if ($this->isLocal($ip)) {
            return [
                'country' => 'محلي',
                'country_code' => null,
                'region' => null,
                'city' => 'localhost',
            ];
        }

        return Cache::remember("geoip:{$ip}", now()->addDay(), function () use ($ip) {
            try {
                $res = Http::timeout(4)->get(rtrim(config('portfolio.geoip_endpoint'), '/') . "/{$ip}", [
                    'fields' => 'status,country,countryCode,regionName,city',
                ]);

                if ($res->ok() && $res->json('status') === 'success') {
                    return [
                        'country' => $res->json('country'),
                        'country_code' => $res->json('countryCode'),
                        'region' => $res->json('regionName'),
                        'city' => $res->json('city'),
                    ];
                }
            } catch (\Throwable $e) {
                report($e);
            }

            return ['country' => null, 'country_code' => null, 'region' => null, 'city' => null];
        });
    }

    private function isLocal(string $ip): bool
    {
        return $ip === '127.0.0.1'
            || $ip === '::1'
            || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
