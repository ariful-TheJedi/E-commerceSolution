<?php

namespace Modules\Product\Infrastructure\Adapters;

use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Contracts\Events\ProductUpdated;

/**
 * Writes ProductUpdated to platform.outbox on the product connection.
 *
 * Platform owns the table (DDL). This adapter inserts the row — no FK.
 * Same connection as the listing write so one transaction covers both.
 * Postgres: platform.outbox (qualified). SQLite tests: table outbox.
 * Same eventId twice leaves one row (idempotent insert).
 */
final class PlatformOutbox implements Outbox
{
    public function record(object $event): void
    {
        if (! $event instanceof ProductUpdated) {
            throw new InvalidArgumentException('Product outbox accepts ProductUpdated only.');
        }

        $connection = DB::connection('product');
        $table = $connection->getDriverName() === 'pgsql' ? 'platform.outbox' : 'outbox';

        if ($connection->table($table)->where('id', $event->eventId)->exists()) {
            return;
        }

        $connection->table($table)->insert([
            'id' => $event->eventId,
            'type' => ProductUpdated::class,
            'payload' => json_encode(['productId' => $event->productId], JSON_THROW_ON_ERROR),
            'occurred_at' => $event->occurredAt->format(DateTimeInterface::ATOM),
            'published_at' => null,
        ]);
    }
}
