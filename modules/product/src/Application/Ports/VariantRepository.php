<?php

namespace Modules\Product\Application\Ports;

use Modules\Product\Domain\ProductOption;
use Modules\Product\Domain\AttributeOption;
use Modules\Product\Domain\ProductVariant;
use Modules\Product\Domain\VariantOptionSelection;

/** Persistence port for product option dimensions and sellable variants. */
interface VariantRepository
{
    public function createOption(ProductOption $option): void;

    public function createOptionValue(AttributeOption $option): void;
    public function optionValueBelongsToProduct(string $optionValueId, string $productId): bool;
    public function optionValueOptionId(string $optionValueId): ?string;

    /** @param list<VariantOptionSelection> $selections */
    public function createVariant(ProductVariant $variant, string $productId, bool $isDefault, array $selections): void;
}
