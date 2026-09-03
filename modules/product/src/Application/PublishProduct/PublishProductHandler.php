<?php

namespace Modules\Product\Application\PublishProduct;

use Modules\Product\Application\Exceptions\ProductNotFound;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Contracts\Events\ProductUpdated;
use Shared\Clock;
use Shared\Ids;

/**
 * Publish a draft listing (draft → active).
 *
 * Missing id → ProductNotFound. Illegal status → Domain exception, no save,
 * no outbox row. Domain decides; this class only loads, calls, saves, records.
 */
final class PublishProductHandler
{
    public function __construct(
        private ProductRepository $products,
        private Outbox $outbox,
        private Transaction $transaction,
        private Ids $ids,
        private Clock $clock,
    ) {
    }

    public function handle(PublishProductCommand $command): void
    {
        $product = $this->products->find($command->id)
            ?? throw new ProductNotFound();

        $product->publish();
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
