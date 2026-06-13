<?php

namespace App\Events;

use App\Models\Visitor;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired whenever a (human) visitor lands on the public site.
 *
 * Currently the dashboard consumes new arrivals by polling the visitors feed,
 * which needs zero extra infrastructure. To push them over WebSockets instead,
 * implement ShouldBroadcast here and add Laravel Reverb (Part 8) — the payload
 * is already shaped for it.
 */
class VisitorArrived
{
    use Dispatchable, SerializesModels;

    public function __construct(public Visitor $visitor)
    {
    }
}
