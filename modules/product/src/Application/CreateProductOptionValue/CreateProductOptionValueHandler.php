<?php

namespace Modules\Product\Application\CreateProductOptionValue;

use Modules\Product\Application\Ports\VariantRepository;
use Modules\Product\Domain\AttributeOption;

/** Adds a selectable value; it does not create a SKU. */
final class CreateProductOptionValueHandler
{
    public function __construct(private VariantRepository $variants) {}
    public function handle(CreateProductOptionValueCommand $command): void
    {
        $this->variants->createOptionValue(new AttributeOption($command->id, $command->optionId, $command->label, $command->slug, $command->colorHex, $command->imagePath, $command->position));
    }
}
