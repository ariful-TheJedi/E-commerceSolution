<?php

namespace Modules\Product\Application\Ports;

use Modules\Product\Domain\AttributeDefinition;
use Modules\Product\Domain\AttributeOption;
use Modules\Product\Domain\ProductAttributeValue;

/**
 * Persistence port for reusable specifications and their assignments.
 */
interface SpecificationRepository
{
    public function createDefinition(AttributeDefinition $definition): void;

    public function addOption(AttributeOption $option): void;

    public function findDefinition(string $id): ?AttributeDefinition;

    public function saveValue(ProductAttributeValue $value): void;

    /** @return list<ProductAttributeValue> */
    public function valuesForProduct(string $productId): array;
}
