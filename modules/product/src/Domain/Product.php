<?php

namespace Modules\Product\Domain;

use Modules\Product\Domain\Exceptions\CannotArchiveProduct;
use Modules\Product\Domain\Exceptions\CannotPublishProduct;
use Modules\Product\Domain\Exceptions\CannotUpdateArchivedProduct;
use Modules\Product\Domain\Exceptions\EmptyTitle;

/**
 * Catalog listing. Not stock, not a cart line.
 *
 * Feature 1 owns status, visibility, featured.
 * Feature 2 owns listing copy (title, short/long description, optional brand
 * as text) and one default ProductVariant for SKU codes.
 *
 * Status
 *   draft     — not for sale, not on the public catalog
 *   active    — published
 *   archived  — kept for history, not sold
 *
 * Transitions
 *   create  → draft
 *   publish → draft to active only
 *   archive → draft or active to archived (not already archived)
 *   draft   → active or archived back to draft
 *
 * Visibility, featured, copy, and SKU codes may change on draft or active,
 * never archived. Featured is a separate flag, not a visibility value.
 *
 * Pure PHP: no Laravel, no database, no clock.
 */
final class Product
{
    private function __construct(
        private readonly string $id,
        private string $title,
        private ?string $shortDescription,
        private ?string $description,
        private ?string $brand,
        private ProductStatus $status,
        private ProductVisibility $visibility,
        private bool $featured,
        private ProductTaxStatus $taxStatus,
        private ?string $taxClass,
        private ProductType $type,
        private bool $soldIndividually,
        private ?string $externalUrl,
        private string $slug,
        private ?string $metaTitle,
        private ?string $metaDescription,
        private ?int $weightG,
        private ?int $lengthMm,
        private ?int $widthMm,
        private ?int $heightMm,
        private ?string $shippingClass,
        private ProductVariant $defaultVariant,
    ) {
    }

    /**
     * New listings start as draft, visible, and not featured unless told otherwise.
     * A simple product always has one default variant (the SKU).
     */
    public static function create(
        string $id,
        string $title,
        ProductVariant $variant,
        ?string $shortDescription = null,
        ?string $description = null,
        ?string $brand = null,
        ProductVisibility $visibility = ProductVisibility::Visible,
        bool $featured = false,
        ProductTaxStatus $taxStatus = ProductTaxStatus::Taxable,
        ?string $taxClass = null,
        ProductType $type = ProductType::Physical,
        bool $soldIndividually = false,
        ?string $externalUrl = null,
        ?string $slug = null,
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        ?int $weightG = null,
        ?int $lengthMm = null,
        ?int $widthMm = null,
        ?int $heightMm = null,
        ?string $shippingClass = null,
    ): self {
        return new self(
            id: $id,
            title: self::requireTitle($title),
            shortDescription: self::optional($shortDescription),
            description: self::optional($description),
            brand: self::optional($brand),
            status: ProductStatus::Draft,
            visibility: $visibility,
            featured: $featured,
            taxStatus: $taxStatus,
            taxClass: $taxClass,
            type: $type,
            soldIndividually: $soldIndividually,
            externalUrl: $externalUrl,
            slug: self::makeSlug($slug ?? $title),
            metaTitle: self::optional($metaTitle),
            metaDescription: self::optional($metaDescription),
            weightG: self::shippingValue($weightG),
            lengthMm: self::shippingValue($lengthMm),
            widthMm: self::shippingValue($widthMm),
            heightMm: self::shippingValue($heightMm),
            shippingClass: self::optional($shippingClass),
            defaultVariant: $variant,
        );
    }

    /**
     * Rebuild from a stored row. Does not apply create() defaults.
     * Used by the repository only — not a second way to "create" a listing.
     */
    public static function reconstitute(
        string $id,
        string $title,
        ProductStatus $status,
        ProductVisibility $visibility,
        bool $featured,
        ProductVariant $defaultVariant,
        ?string $shortDescription = null,
        ?string $description = null,
        ?string $brand = null,
        ProductTaxStatus $taxStatus = ProductTaxStatus::Taxable,
        ?string $taxClass = null,
        ProductType $type = ProductType::Physical,
        bool $soldIndividually = false,
        ?string $externalUrl = null,
        ?string $slug = null,
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        ?int $weightG = null,
        ?int $lengthMm = null,
        ?int $widthMm = null,
        ?int $heightMm = null,
        ?string $shippingClass = null,
    ): self {
        return new self(
            id: $id,
            title: $title,
            shortDescription: $shortDescription,
            description: $description,
            brand: $brand,
            status: $status,
            visibility: $visibility,
            featured: $featured,
            taxStatus: $taxStatus,
            taxClass: $taxClass,
            type: $type,
            soldIndividually: $soldIndividually,
            externalUrl: $externalUrl,
            slug: self::makeSlug($slug ?? $title),
            metaTitle: self::optional($metaTitle),
            metaDescription: self::optional($metaDescription),
            weightG: self::shippingValue($weightG),
            lengthMm: self::shippingValue($lengthMm),
            widthMm: self::shippingValue($widthMm),
            heightMm: self::shippingValue($heightMm),
            shippingClass: self::optional($shippingClass),
            defaultVariant: $defaultVariant,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function shortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function brand(): ?string
    {
        return $this->brand;
    }

    public function status(): ProductStatus
    {
        return $this->status;
    }

    public function visibility(): ProductVisibility
    {
        return $this->visibility;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function defaultVariant(): ProductVariant
    {
        return $this->defaultVariant;
    }

    public function taxStatus(): ProductTaxStatus
    {
        return $this->taxStatus;
    }

    public function taxClass(): ?string
    {
        return $this->taxClass;
    }

    public function type(): ProductType
    {
        return $this->type;
    }

    public function isSoldIndividually(): bool
    {
        return $this->soldIndividually;
    }

    public function externalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function metaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function metaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function weightG(): ?int
    {
        return $this->weightG;
    }

    public function lengthMm(): ?int
    {
        return $this->lengthMm;
    }

    public function widthMm(): ?int
    {
        return $this->widthMm;
    }

    public function heightMm(): ?int
    {
        return $this->heightMm;
    }

    public function shippingClass(): ?string
    {
        return $this->shippingClass;
    }

    public function changeShipping(
        ?int $weightG,
        ?int $lengthMm,
        ?int $widthMm,
        ?int $heightMm,
        ?string $shippingClass,
    ): void {
        $this->assertNotArchived();
        $this->weightG = self::shippingValue($weightG);
        $this->lengthMm = self::shippingValue($lengthMm);
        $this->widthMm = self::shippingValue($widthMm);
        $this->heightMm = self::shippingValue($heightMm);
        $this->shippingClass = self::optional($shippingClass);
    }

    public function changeSeo(string $slug, ?string $metaTitle, ?string $metaDescription): void
    {
        $this->assertNotArchived();
        $this->slug = self::makeSlug($slug);
        $this->metaTitle = self::optional($metaTitle);
        $this->metaDescription = self::optional($metaDescription);
    }

    public function changeCatalogType(
        ProductType $type,
        bool $soldIndividually,
        ?string $externalUrl = null,
    ): void {
        $this->assertNotArchived();
        $this->type = $type;
        $this->soldIndividually = $soldIndividually;
        $this->externalUrl = $externalUrl === null ? null : trim($externalUrl);
    }

    public function changeTaxSettings(ProductTaxStatus $taxStatus, ?string $taxClass): void
    {
        $this->assertNotArchived();
        $this->taxStatus = $taxStatus;
        $this->taxClass = $taxClass === null ? null : trim($taxClass);
    }

    /**
     * Publish means "put on sale": draft → active.
     * Already active or archived cannot be published.
     */
    public function publish(): void
    {
        if ($this->status !== ProductStatus::Draft) {
            throw new CannotPublishProduct();
        }

        $this->status = ProductStatus::Active;
    }

    /**
     * Archive hides the listing from sale. Draft or active may archive.
     * An archived listing stays archived until draft() brings it back.
     */
    public function archive(): void
    {
        if ($this->status === ProductStatus::Archived) {
            throw new CannotArchiveProduct();
        }

        $this->status = ProductStatus::Archived;
    }

    /**
     * Return to draft from active or archived so it can be edited and published again.
     */
    public function draft(): void
    {
        $this->status = ProductStatus::Draft;
    }

    /**
     * Change where the listing appears (visibility) and whether it is featured.
     * Null means "leave this field as it is". Archived listings are frozen.
     */
    public function updateListing(
        ?ProductVisibility $visibility = null,
        ?bool $featured = null,
    ): void {
        $this->assertNotArchived();

        if ($visibility !== null) {
            $this->visibility = $visibility;
        }

        if ($featured !== null) {
            $this->featured = $featured;
        }
    }

    /**
     * Change title, copy, and brand. Null means leave unchanged.
     * Empty string clears an optional field. Empty title is rejected.
     */
    public function updateDetails(
        ?string $title = null,
        ?string $shortDescription = null,
        ?string $description = null,
        ?string $brand = null,
    ): void {
        $this->assertNotArchived();

        if ($title !== null) {
            $this->title = self::requireTitle($title);
        }

        if ($shortDescription !== null) {
            $this->shortDescription = self::optional($shortDescription);
        }

        if ($description !== null) {
            $this->description = self::optional($description);
        }

        if ($brand !== null) {
            $this->brand = self::optional($brand);
        }
    }

    /**
     * Change codes on the default SKU. Null means leave unchanged.
     */
    public function changeDefaultIdentifiers(
        ?string $sku = null,
        ?string $barcode = null,
        ?string $gtin = null,
        ?string $mpn = null,
    ): void {
        $this->assertNotArchived();

        $this->defaultVariant->changeIdentifiers(
            sku: $sku,
            barcode: $barcode,
            gtin: $gtin,
            mpn: $mpn,
        );
    }

    private function assertNotArchived(): void
    {
        if ($this->status === ProductStatus::Archived) {
            throw new CannotUpdateArchivedProduct();
        }
    }

    private static function requireTitle(string $title): string
    {
        $title = trim($title);

        if ($title === '') {
            throw new EmptyTitle();
        }

        return $title;
    }

    private static function optional(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function shippingValue(?int $value): ?int
    {
        if ($value !== null && $value < 0) {
            throw new \InvalidArgumentException('Shipping measurements cannot be negative.');
        }

        return $value;
    }

    private static function makeSlug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            throw new EmptyTitle();
        }

        return $slug;
    }
}
