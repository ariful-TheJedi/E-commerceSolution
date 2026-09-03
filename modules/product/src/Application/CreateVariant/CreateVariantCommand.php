<?php

namespace Modules\Product\Application\CreateVariant;

/** Input for one sellable SKU and its selected option values. */
final readonly class CreateVariantCommand
{
    /** @param list<string> $optionValueIds */
    public function __construct(
        public string $id, public string $productId, public string $sku, public array $optionValueIds = [],
        public bool $isDefault = false, public ?string $barcode = null, public ?string $gtin = null,
        public ?string $mpn = null, public int $priceMinor = 0, public string $currency = 'XXX',
    ) {}
}
