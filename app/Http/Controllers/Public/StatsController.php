<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    /**
     * Live "online now" count — distinct human visitors seen in the last
     * 5 minutes. Polled by the public footer counter.
     */
    public function online(): JsonResponse
    {
        $online = Visitor::humans()
            ->where('visited_at', '>=', now()->subMinutes(5))
            ->distinct('ip')
            ->count('ip');

        // Always show at least the current viewer.
        return response()->json(['online' => max(1, $online)]);
    }
}
