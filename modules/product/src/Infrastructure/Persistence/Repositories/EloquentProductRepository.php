<?php

namespace Modules\Product\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Str;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Domain\Product;
use Modules\Product\Domain\ProductStatus;
use Modules\Product\Domain\ProductTaxStatus;
use Modules\Product\Domain\ProductType;
use Modules\Product\Domain\ProductVariant;
use Modules\Product\Domain\ProductVisibility;
use Modules\Product\Infrastructure\Persistence\Models\ProductModel;
use Modules\Product\Infrastructure\Persistence\Models\ProductVariantModel;
use UnexpectedValueException;

/**
 * Maps Domain Product (listing + default variant) ↔ product.products
 * and product.product_variants.
 *
 * Status and visibility are stored as lowercase text matching the CHECKs
 * on the Postgres table. A new listing row also gets type=physical, slug
 * from title, tax_status=taxable. A new variant row gets price_minor=0 and
 * currency=XXX until feature 3 owns prices.
 *
 * default_variant_id is set after the variant row exists (avoid the insert cycle).
 *
 * This class does not start a transaction with the outbox. The caller
 * (API / unit of work) wraps save + outbox.record in one transaction.
 */
final class EloquentProductRepository implements ProductRepository
{
    public function save(Product $product): void
    {
        $variant = $product->defaultVariant();

        ProductModel::query()->updateOrCreate(
            ['id' => $product->id()],
            [
                'title' => $product->title(),
                'short_description' => $product->shortDescription(),
                'description' => $product->description(),
                'brand' => $product->brand(),
                'status' => strtolower($product->status()->name),
                'visibility' => strtolower($product->visibility()->name),
                'featured' => $product->isFeatured(),
                'sold_individually' => $product->isSoldIndividually(),
                'type' => $product->type()->value,
                'slug' => $product->slug(),
                'external_url' => $product->externalUrl(),
                'meta_title' => $product->metaTitle(),
                'meta_description' => $product->metaDescription(),
                'tax_status' => $product->taxStatus()->value,
                'tax_class' => $product->taxClass(),
                'weight_g' => $product->weightG(),
                'length_mm' => $product->lengthMm(),
                'width_mm' => $product->widthMm(),
                'height_mm' => $product->heightMm(),
                'shipping_class' => $product->shippingClass(),
            ],
        );

        ProductVariantModel::query()->updateOrCreate(
            ['id' => $variant->id()],
            [
                'product_id' => $product->id(),
                'sku' => $variant->sku(),
                'barcode' => $variant->barcode(),
                'gtin' => $variant->gtin(),
                'mpn' => $variant->mpn(),
                'is_default' => true,
                'price_minor' => $variant->priceMinor(),
                'compare_at_minor' => $variant->compareAtMinor(),
                'cost_minor' => $variant->costMinor(),
                'currency' => $variant->currency(),
                'sale_starts_at' => $variant->saleStartsAt()?->format(DATE_ATOM),
                'sale_ends_at' => $variant->saleEndsAt()?->format(DATE_ATOM),
                'weight_g' => $variant->weightG(),
                'length_mm' => $variant->lengthMm(),
                'width_mm' => $variant->widthMm(),
                'height_mm' => $variant->heightMm(),
            ],
        );

        ProductModel::query()->where('id', $product->id())->update([
            'default_variant_id' => $variant->id(),
        ]);
    }

    public function find(string $id): ?Product
    {
        $row = ProductModel::query()->find($id);

        if ($row === null) {
            return null;
        }

        return $this->toDomain($row);
    }

    public function findMany(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $found = [];

        foreach (ProductModel::query()->whereIn('id', $ids)->get() as $row) {
            $product = $this->toDomain($row);
            $found[$product->id()] = $product;
        }

        return $found;
    }

    public function skuTaken(string $sku, ?string $exceptVariantId = null): bool
    {
        $query = ProductVariantModel::query()->where('sku', $sku);

        if ($exceptVariantId !== null) {
            $query->where('id', '!=', $exceptVariantId);
        }

        return $query->exists();
    }

    public function slugTaken(string $slug, ?string $exceptProductId = null): bool
    {
        $query = ProductModel::query()->where('slug', $slug);

        if ($exceptProductId !== null) {
            $query->where('id', '!=', $exceptProductId);
        }

        return $query->exists();
    }

    private function toDomain(ProductModel $row): Product
    {
        $variantRow = ProductVariantModel::query()
            ->where('product_id', $row->id)
            ->where('is_default', true)
            ->first();

        if ($variantRow === null) {
            throw new UnexpectedValueException("Product [{$row->id}] has no default variant.");
        }

        return Product::reconstitute(
            id: $row->id,
            title: $row->title,
            status: $this->statusFromStorage((string) $row->status),
            visibility: $this->visibilityFromStorage((string) $row->visibility),
            featured: (bool) $row->featured,
            taxStatus: ProductTaxStatus::from((string) $row->tax_status),
            taxClass: $row->tax_class,
            type: ProductType::from((string) $row->type),
            soldIndividually: (bool) $row->sold_individually,
            externalUrl: $row->external_url,
            slug: $row->slug,
            metaTitle: $row->meta_title,
            metaDescription: $row->meta_description,
            weightG: $row->weight_g === null ? null : (int) $row->weight_g,
            lengthMm: $row->length_mm === null ? null : (int) $row->length_mm,
            widthMm: $row->width_mm === null ? null : (int) $row->width_mm,
            heightMm: $row->height_mm === null ? null : (int) $row->height_mm,
            shippingClass: $row->shipping_class,
            defaultVariant: ProductVariant::reconstitute(
                id: $variantRow->id,
                sku: $variantRow->sku,
                barcode: $variantRow->barcode,
                gtin: $variantRow->gtin,
                mpn: $variantRow->mpn,
                isDefault: true,
                priceMinor: (int) $variantRow->price_minor,
                compareAtMinor: $variantRow->compare_at_minor === null ? null : (int) $variantRow->compare_at_minor,
                costMinor: $variantRow->cost_minor === null ? null : (int) $variantRow->cost_minor,
                currency: (string) $variantRow->currency,
                saleStartsAt: $variantRow->sale_starts_at === null ? null : new \DateTimeImmutable($variantRow->sale_starts_at),
                saleEndsAt: $variantRow->sale_ends_at === null ? null : new \DateTimeImmutable($variantRow->sale_ends_at),
                weightG: $variantRow->weight_g === null ? null : (int) $variantRow->weight_g,
                lengthMm: $variantRow->length_mm === null ? null : (int) $variantRow->length_mm,
                widthMm: $variantRow->width_mm === null ? null : (int) $variantRow->width_mm,
                heightMm: $variantRow->height_mm === null ? null : (int) $variantRow->height_mm,
            ),
            shortDescription: $row->short_description,
            description: $row->description,
            brand: $row->brand,
        );
    }

    private function statusFromStorage(string $value): ProductStatus
    {
        return match ($value) {
            'draft' => ProductStatus::Draft,
            'active' => ProductStatus::Active,
            'archived' => ProductStatus::Archived,
            default => throw new UnexpectedValueException("Unknown product status [{$value}]."),
        };
    }

    private function visibilityFromStorage(string $value): ProductVisibility
    {
        return match ($value) {
            'visible' => ProductVisibility::Visible,
            'catalog' => ProductVisibility::Catalog,
            'search' => ProductVisibility::Search,
            'hidden' => ProductVisibility::Hidden,
            default => throw new UnexpectedValueException("Unknown product visibility [{$value}]."),
        };
    }
}
