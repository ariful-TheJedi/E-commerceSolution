<?php

namespace Modules\Product\Application\Relations;

use Modules\Product\Application\Exceptions\ProductValidationException;
use Modules\Product\Application\Ports\RelationRepository;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Contracts\Events\ProductUpdated;
use Modules\Product\Domain\ProductRelation;
use Shared\Clock;
use Shared\Ids;

/** Validates and stores manual merchandising links without generating them. */
final class RelationHandler
{
    public function __construct(private RelationRepository $relations, private Transaction $transaction, private Outbox $outbox, private Ids $ids, private Clock $clock) {}

    public function handle(CreateRelationCommand $command): void
    {
        if (!$this->relations->productExists($command->fromProductId)) throw ProductValidationException::withMessages(['from_product_id' => 'Source product does not exist.']);
        if (!$this->relations->productExists($command->toProductId)) throw ProductValidationException::withMessages(['to_product_id' => 'Target product does not exist.']);
        try { $relation = new ProductRelation($command->fromProductId, $command->toProductId, $command->kind); }
        catch (\InvalidArgumentException $exception) { throw ProductValidationException::withMessages(['kind' => $exception->getMessage()]); }
        $this->transaction->run(function () use ($relation, $command): void {
            $this->relations->save($relation);
            $this->outbox->record(new ProductUpdated($this->ids->uuid7(), $command->fromProductId, $this->clock->now()));
        });
    }
}