<?php

namespace Modules\Product\Infrastructure\Adapters;

use DateTimeImmutable;
use Shared\Clock;

/**
 * Real clock. UTC. Injected so Domain and Application never call now().
 */
final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
