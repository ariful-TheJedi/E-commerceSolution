<?php

namespace Modules\Product\Application\CreateProduct;

use Modules\Product\Domain\ProductVisibility;
use Modules\Product\Domain\ProductTaxStatus;
use Modules\Product\Domain\ProductType;
use DateTimeImmutable;

/**
 * Input for creating a listing with its default SKU.
 *
 * The handler does not decide defaults — Domain::create does (draft, visible,
 * not featured). id is a UUIDv7 supplied by the caller. sku is required.
 * Copy, brand, and scanner codes are optional. Variant id is assigned in the handler.
 */
final readonly class CreateProductCommand
{
    public function __construct(
        public string $id,
        public string $title,
        public string $sku,
        public ?string $shortDescription = null,
        public ?string $description = null,
        public ?string $brand = null,
        public ?string $barcode = null,
        public ?string $gtin = null,
        public ?string $mpn = null,
        public int $priceMinor = 0,
        public string $currency = 'XXX',
        public ?int $compareAtMinor = null,
        public ?int $costMinor = null,
        public ?DateTimeImmutable $saleStartsAt = null,
        public ?DateTimeImmutable $saleEndsAt = null,
        public ProductTaxStatus $taxStatus = ProductTaxStatus::Taxable,
        public ?string $taxClass = null,
        public ProductType $type = ProductType::Physical,
        public bool $soldIndividually = false,
        public ?string $externalUrl = null,
        public ?string $slug = null,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public ProductVisibility $visibility = ProductVisibility::Visible,
        public bool $featured = false,
        public ?int $weightG = null,
        public ?int $lengthMm = null,
        public ?int $widthMm = null,
        public ?int $heightMm = null,
        public ?int $variantWeightG = null,
        public ?int $variantLengthMm = null,
        public ?int $variantWidthMm = null,
        public ?int $variantHeightMm = null,
        public ?string $shippingClass = null,
    ) {
    }
}
