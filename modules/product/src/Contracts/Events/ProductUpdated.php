<?php

namespace Modules\Product\Contracts\Events;

use DateTimeImmutable;

/**
 * A catalog listing changed. Other modules may react; they must not write product.*.
 *
 * Past tense, immutable. eventId makes handlers safe to run twice.
 * Written to platform.outbox in the same transaction as the row change.
 */
final readonly class ProductUpdated
{
    public function __construct(
        public string $eventId,
        public string $productId,
        public DateTimeImmutable $occurredAt,
    ) {
    }
}
