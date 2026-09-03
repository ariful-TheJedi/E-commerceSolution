<?php

namespace Modules\Product\Application\DraftProduct;

use Modules\Product\Application\Exceptions\ProductNotFound;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Contracts\Events\ProductUpdated;
use Shared\Clock;
use Shared\Ids;

/**
 * Return a listing to draft so it can be edited and published again.
 *
 * Missing id → ProductNotFound. Domain allows draft() from active or archived.
 */
final class DraftProductHandler
{
    public function __construct(
        private ProductRepository $products,
        private Outbox $outbox,
        private Transaction $transaction,
        private Ids $ids,
        private Clock $clock,
    ) {
    }

    public function handle(DraftProductCommand $command): void
    {
        $product = $this->products->find($command->id)
            ?? throw new ProductNotFound();

        $product->draft();
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
