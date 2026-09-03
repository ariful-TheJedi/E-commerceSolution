<?php

namespace Modules\Product\Application\CreateProduct;

use Modules\Product\Application\Exceptions\DuplicateSku;
use Modules\Product\Application\Exceptions\DuplicateSlug;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Contracts\Events\ProductUpdated;
use Modules\Product\Domain\Product;
use Modules\Product\Domain\ProductVariant;
use Shared\Clock;
use Shared\Ids;

/**
 * Create a catalog listing with one default SKU.
 *
 * Orchestration only: Domain builds the aggregate, uniqueness is the port,
 * then we persist and record ProductUpdated. No other business rules here.
 *
 * Clock and Ids are injected so this layer never calls now() or generates
 * ids itself. The wrapping transaction (rows + outbox) is Infrastructure's job.
 */
final class CreateProductHandler
{
    public function __construct(
        private ProductRepository $products,
        private Outbox $outbox,
        private Transaction $transaction,
        private Ids $ids,
        private Clock $clock,
    ) {
    }

    public function handle(CreateProductCommand $command): void
    {
        $variant = ProductVariant::create(
            id: $this->ids->uuid7(),
            sku: $command->sku,
            barcode: $command->barcode,
            gtin: $command->gtin,
            mpn: $command->mpn,
            priceMinor: $command->priceMinor,
            currency: $command->currency,
            compareAtMinor: $command->compareAtMinor,
            costMinor: $command->costMinor,
            saleStartsAt: $command->saleStartsAt,
            saleEndsAt: $command->saleEndsAt,
            weightG: $command->variantWeightG,
            lengthMm: $command->variantLengthMm,
            widthMm: $command->variantWidthMm,
            heightMm: $command->variantHeightMm,
        );

        if ($this->products->skuTaken($variant->sku())) {
            throw new DuplicateSku();
        }

        $product = Product::create(
            id: $command->id,
            title: $command->title,
            variant: $variant,
            shortDescription: $command->shortDescription,
            description: $command->description,
            brand: $command->brand,
            visibility: $command->visibility,
            featured: $command->featured,
            taxStatus: $command->taxStatus,
            taxClass: $command->taxClass,
            type: $command->type,
            soldIndividually: $command->soldIndividually,
            externalUrl: $command->externalUrl,
            slug: $command->slug,
            metaTitle: $command->metaTitle,
            metaDescription: $command->metaDescription,
            weightG: $command->weightG,
            lengthMm: $command->lengthMm,
            widthMm: $command->widthMm,
            heightMm: $command->heightMm,
            shippingClass: $command->shippingClass,
        );

        if ($this->products->slugTaken($product->slug())) {
            throw new DuplicateSlug();
        }

        $this->transaction->run(function () use ($product): void {
            $this->products->save($product);
            $this->outbox->record(new ProductUpdated(
                eventId: $this->ids->uuid7(),
                productId: $product->id(),
                occurredAt: $this->clock->now(),
            ));
        });
    }
}
