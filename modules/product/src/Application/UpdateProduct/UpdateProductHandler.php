<?php

namespace Modules\Product\Application\UpdateProduct;

use Modules\Product\Application\Exceptions\DuplicateSku;
use Modules\Product\Application\Exceptions\DuplicateSlug;
use Modules\Product\Application\Exceptions\ProductNotFound;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Application\Ports\SeoRedirects;
use Modules\Product\Contracts\Events\ProductUpdated;
use Shared\Clock;
use Shared\Ids;

/**
 * Update listing flags, copy, brand, and/or default SKU codes.
 *
 * Missing id → ProductNotFound. Archived → Domain exception, no save,
 * no outbox row. Duplicate SKU → DuplicateSku, no save, no outbox row.
 */
final class UpdateProductHandler
{
    public function __construct(
        private ProductRepository $products,
        private Outbox $outbox,
        private Transaction $transaction,
        private Ids $ids,
        private Clock $clock,
        private ?SeoRedirects $redirects = null,
    ) {
    }

    public function handle(UpdateProductCommand $command): void
    {
        $product = $this->products->find($command->id)
            ?? throw new ProductNotFound();
        $oldSlug = $product->slug();

        $product->updateDetails(
            title: $command->title,
            shortDescription: $command->shortDescription,
            description: $command->description,
            brand: $command->brand,
        );
        $product->updateListing(
            visibility: $command->visibility,
            featured: $command->featured,
        );

        if ($command->sku !== null && $this->products->skuTaken($command->sku, $product->defaultVariant()->id())) {
            throw new DuplicateSku();
        }

        $product->changeDefaultIdentifiers(
            sku: $command->sku,
            barcode: $command->barcode,
            gtin: $command->gtin,
            mpn: $command->mpn,
        );

        if ($command->priceMinor !== null || $command->compareAtMinor !== null
            || $command->costMinor !== null || $command->saleStartsAt !== null
            || $command->saleEndsAt !== null) {
            $product->defaultVariant()->changePricing(
                priceMinor: $command->priceMinor,
                compareAtMinor: $command->compareAtMinor,
                costMinor: $command->costMinor,
                saleStartsAt: $command->saleStartsAt,
                saleEndsAt: $command->saleEndsAt,
            );
        }

        if ($command->taxStatus !== null) {
            $product->changeTaxSettings($command->taxStatus, $command->taxClass);
        }

        if ($command->weightG !== null || $command->lengthMm !== null || $command->widthMm !== null
            || $command->heightMm !== null || $command->shippingClass !== null) {
            $product->changeShipping(
                weightG: $command->weightG ?? $product->weightG(),
                lengthMm: $command->lengthMm ?? $product->lengthMm(),
                widthMm: $command->widthMm ?? $product->widthMm(),
                heightMm: $command->heightMm ?? $product->heightMm(),
                shippingClass: $command->shippingClass ?? $product->shippingClass(),
            );
        }

        if ($command->variantWeightG !== null || $command->variantLengthMm !== null
            || $command->variantWidthMm !== null || $command->variantHeightMm !== null) {
            $product->defaultVariant()->changeShipping(
                weightG: $command->variantWeightG ?? $product->defaultVariant()->weightG(),
                lengthMm: $command->variantLengthMm ?? $product->defaultVariant()->lengthMm(),
                widthMm: $command->variantWidthMm ?? $product->defaultVariant()->widthMm(),
                heightMm: $command->variantHeightMm ?? $product->defaultVariant()->heightMm(),
            );
        }

        if ($command->type !== null || $command->soldIndividually !== null || $command->externalUrl !== null) {
            $product->changeCatalogType(
                type: $command->type ?? $product->type(),
                soldIndividually: $command->soldIndividually ?? $product->isSoldIndividually(),
                externalUrl: $command->externalUrl,
            );
        }

        if ($command->slug !== null || $command->metaTitle !== null || $command->metaDescription !== null) {
            $product->changeSeo(
                slug: $command->slug ?? $product->slug(),
                metaTitle: $command->metaTitle,
                metaDescription: $command->metaDescription,
            );

            if ($this->products->slugTaken($product->slug(), $product->id())) {
                throw new DuplicateSlug();
            }
        }

        $this->transaction->run(function () use ($product, $oldSlug): void {
            $this->products->save($product);
            $this->outbox->record(new ProductUpdated(
                eventId: $this->ids->uuid7(),
                productId: $product->id(),
                occurredAt: $this->clock->now(),
            ));
            if ($oldSlug !== $product->slug()) {
                $this->redirects?->record('/products/'.$oldSlug, '/products/'.$product->slug());
            }
        });
    }
}
