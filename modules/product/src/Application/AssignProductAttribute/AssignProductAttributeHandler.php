<?php

namespace Modules\Product\Application\AssignProductAttribute;

use InvalidArgumentException;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\SpecificationRepository;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Contracts\Events\ProductUpdated;
use Modules\Product\Domain\AttributeDataType;
use Modules\Product\Domain\ProductAttributeValue;
use Shared\Clock;
use Shared\Ids;

/** Assigns a reusable, typed specification without creating variant options. */
final class AssignProductAttributeHandler
{
    public function __construct(
        private SpecificationRepository $specifications,
        private ProductRepository $products,
        private Outbox $outbox,
        private Transaction $transaction,
        private Ids $ids,
        private Clock $clock,
    ) {
    }

    public function handle(AssignProductAttributeCommand $command): void
    {
        if ($this->products->find($command->productId) === null) {
            throw new InvalidArgumentException('Product does not exist.');
        }

        $definition = $this->specifications->findDefinition($command->attributeId)
            ?? throw new InvalidArgumentException('Attribute does not exist.');

        if ($definition->dataType === AttributeDataType::Enum && $command->attributeOptionId === null) {
            throw new InvalidArgumentException('Enum attributes require an option.');
        }

        if ($definition->dataType !== AttributeDataType::Enum && $command->valueText === null) {
            throw new InvalidArgumentException('This attribute requires a value.');
        }

        $value = new ProductAttributeValue(
            id: $command->id,
            productId: $command->productId,
            attributeId: $command->attributeId,
            variantId: $command->variantId,
            valueText: $command->valueText,
            attributeOptionId: $command->attributeOptionId,
        );

        $this->transaction->run(function () use ($value, $command): void {
            $this->specifications->saveValue($value);
            $this->outbox->record(new ProductUpdated(
                eventId: $this->ids->uuid7(), productId: $command->productId, occurredAt: $this->clock->now(),
            ));
        });
    }
}
