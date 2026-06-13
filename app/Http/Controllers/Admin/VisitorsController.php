<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisitorsController extends Controller
{
    public function index(Request $request): View
    {
        $visitors = Visitor::humans()
            ->tap(fn ($q) => $this->applyFilters($q, $request))
            ->latest('visited_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.visitors.index', [
            'visitors' => $visitors,
            'search' => $request->query('q'),
            'range' => $request->query('range', 'all'),
            'stats' => $this->stats(),
        ]);
    }

    /** Polled by the dashboard for live notifications (new arrivals + counters). */
    public function feed(Request $request): JsonResponse
    {
        $after = (int) $request->query('after', 0);

        $new = Visitor::humans()
            ->when($after, fn ($q) => $q->where('id', '>', $after))
            ->latest('id')
            ->limit(15)
            ->get()
            ->map(fn (Visitor $v) => $this->presentForFeed($v));

        return response()->json([
            'visitors' => $new,
            'last_id' => Visitor::humans()->max('id') ?? 0,
            'unread' => Visitor::humans()->unread()->count(),
            'active_now' => Visitor::humans()->where('visited_at', '>=', now()->subMinutes(5))->count(),
        ]);
    }

    public function markRead(Visitor $visitor): JsonResponse
    {
        $visitor->forceFill(['read_at' => now()])->save();

        return response()->json(['ok' => true]);
    }

    public function markAllRead(): JsonResponse
    {
        Visitor::whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function clear(): RedirectResponse
    {
        Visitor::query()->delete();

        return back()->with('status', 'تم مسح سجل الزوّار.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'visitors-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel/Arabic
            fputcsv($out, ['IP', 'Country', 'City', 'Browser', 'Platform', 'Device', 'Page', 'Referrer', 'Visited At']);

            Visitor::humans()
                ->tap(fn ($q) => $this->applyFilters($q, $request))
                ->latest('visited_at')
                ->chunk(500, function ($chunk) use ($out) {
                    foreach ($chunk as $v) {
                        fputcsv($out, [
                            $v->ip, $v->country, $v->city, $v->browser, $v->platform,
                            $v->device, $v->page_url, $v->referrer,
                            optional($v->visited_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w
                ->where('ip', 'like', "%{$q}%")
                ->orWhere('country', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%")
                ->orWhere('page_url', 'like', "%{$q}%")
                ->orWhere('browser', 'like', "%{$q}%"));
        }

        match ($request->query('range')) {
            'today' => $query->where('visited_at', '>=', now()->startOfDay()),
            'week' => $query->where('visited_at', '>=', now()->startOfWeek()),
            default => null,
        };
    }

    private function stats(): array
    {
        return [
            'total' => Visitor::humans()->count(),
            'today' => Visitor::humans()->where('visited_at', '>=', now()->startOfDay())->count(),
            'active_now' => Visitor::humans()->where('visited_at', '>=', now()->subMinutes(5))->count(),
            'unread' => Visitor::humans()->unread()->count(),
        ];
    }

    /** Shape a visitor for the JSON feed (flag emoji + relative time). */
    private function presentForFeed(Visitor $v): array
    {
        return [
            'id' => $v->id,
            'ip' => $v->ip,
            'flag' => $v->country_code ? $this->flagEmoji($v->country_code) : '🌐',
            'country' => $v->country,
            'city' => $v->city,
            'browser' => $v->browser,
            'device' => $v->device,
            'page' => $v->page_url,
            'time' => optional($v->visited_at)->diffForHumans(),
        ];
    }

    private function flagEmoji(string $code): string
    {
        $code = strtoupper($code);
        if (strlen($code) !== 2) {
            return '🌐';
        }

        return mb_convert_encoding('&#' . (127397 + ord($code[0])) . ';&#' . (127397 + ord($code[1])) . ';', 'UTF-8', 'HTML-ENTITIES');
    }
}
