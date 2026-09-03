<?php

namespace Modules\Product\Application\CreateVariant;

use InvalidArgumentException;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\VariantRepository;
use Modules\Product\Domain\ProductVariant;
use Modules\Product\Domain\VariantOptionSelection;

/** Creates a SKU combination and enforces one value per option dimension. */
final class CreateVariantHandler
{
    public function __construct(private ProductRepository $products, private VariantRepository $variants) {}

    public function handle(CreateVariantCommand $command): void
    {
        if ($this->products->find($command->productId) === null) throw new InvalidArgumentException('Product does not exist.');
        if ($this->products->skuTaken($command->sku)) throw new InvalidArgumentException('SKU is already taken.');
        $selections = [];
        $optionIds = [];
        foreach ($command->optionValueIds as $optionValueId) {
            if (!$this->variants->optionValueBelongsToProduct($optionValueId, $command->productId)) throw new InvalidArgumentException('Option value does not belong to the product.');
            $optionId = $this->variants->optionValueOptionId($optionValueId);
            if ($optionId === null || in_array($optionId, $optionIds, true)) throw new InvalidArgumentException('A variant may select only one value per option.');
            $optionIds[] = $optionId;
            $selections[] = new VariantOptionSelection($command->id, $optionValueId, $optionId);
        }
        $this->variants->createVariant(ProductVariant::create($command->id, $command->sku, $command->barcode, $command->gtin, $command->mpn, $command->priceMinor, $command->currency), $command->productId, $command->isDefault, $selections);
    }
}
