<?php

namespace Modules\Product\Application\UpdateProduct;

use Modules\Product\Domain\ProductVisibility;
use Modules\Product\Domain\ProductTaxStatus;
use Modules\Product\Domain\ProductType;
use DateTimeImmutable;

/**
 * Change listing flags, copy, brand, and/or default SKU codes.
 * Null means leave that field unchanged. Status does not change here.
 * Archived listings are rejected by Domain.
 */
final readonly class UpdateProductCommand
{
    public function __construct(
        public string $id,
        public ?ProductVisibility $visibility = null,
        public ?bool $featured = null,
        public ?string $title = null,
        public ?string $shortDescription = null,
        public ?string $description = null,
        public ?string $brand = null,
        public ?string $sku = null,
        public ?string $barcode = null,
        public ?string $gtin = null,
        public ?string $mpn = null,
        public ?int $priceMinor = null,
        public ?int $compareAtMinor = null,
        public ?int $costMinor = null,
        public ?DateTimeImmutable $saleStartsAt = null,
        public ?DateTimeImmutable $saleEndsAt = null,
        public ?ProductTaxStatus $taxStatus = null,
        public ?string $taxClass = null,
        public ?ProductType $type = null,
        public ?bool $soldIndividually = null,
        public ?string $externalUrl = null,
        public ?string $slug = null,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
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
