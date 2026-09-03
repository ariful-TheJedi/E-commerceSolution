<?php

namespace Modules\Product\Application\ArchiveProduct;

use Modules\Product\Application\Exceptions\ProductNotFound;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Contracts\Events\ProductUpdated;
use Shared\Clock;
use Shared\Ids;

/**
 * Archive a listing (draft or active → archived).
 *
 * Missing id → ProductNotFound. Already archived → Domain exception,
 * nothing written. Domain decides; this class loads, calls, saves, records.
 */
final class ArchiveProductHandler
{
    public function __construct(
        private ProductRepository $products,
        private Outbox $outbox,
        private Transaction $transaction,
        private Ids $ids,
        private Clock $clock,
    ) {
    }

    public function handle(ArchiveProductCommand $command): void
    {
        $product = $this->products->find($command->id)
            ?? throw new ProductNotFound();

        $product->archive();
        $this->transaction->run(function () use ($product): void {
            $this->products->save($product);
            $this->outbox->record(new ProductUpdated(
                eventId: $this->ids->uuid7(),
                productId: $product->id(),
                occurredAt: $this->clock->now(),
            ));
        });
    }
}
