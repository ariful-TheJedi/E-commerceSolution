<?php

use Modules\Product\Domain\Product;
use Modules\Product\Domain\ProductType;
use Modules\Product\Domain\ProductVariant;
use Modules\Product\Domain\Exceptions\CannotUpdateArchivedProduct;

function aTypedProduct(array $overrides = []): Product
{
    return Product::create(
        id: $overrides['id'] ?? '01900000-0000-7000-8000-000000000005',
        title: $overrides['title'] ?? 'Wool coat',
        variant: ProductVariant::create(
            id: $overrides['variantId'] ?? '01900000-0000-7000-8000-0000000000a5',
            sku: $overrides['sku'] ?? 'WOOL-COAT-5',
        ),
        type: $overrides['type'] ?? ProductType::Physical,
        soldIndividually: $overrides['soldIndividually'] ?? false,
        externalUrl: $overrides['externalUrl'] ?? null,
    );
}

it('creates a product with a type and sold individually flag', function () {
    $product = aTypedProduct([
        'type' => ProductType::Virtual,
        'soldIndividually' => true,
    ]);

    expect($product->type())->toBe(ProductType::Virtual)
        ->and($product->isSoldIndividually())->toBeTrue()
        ->and($product->externalUrl())->toBeNull();
});

it('stores an external product URL', function () {
    $product = aTypedProduct([
        'type' => ProductType::External,
        'externalUrl' => 'https://merchant.example/products/wool-coat',
    ]);

    expect($product->type())->toBe(ProductType::External)
        ->and($product->externalUrl())->toBe('https://merchant.example/products/wool-coat');
});

it('updates product type, external URL, and sold individually flag', function () {
    $product = aTypedProduct();

    $product->changeCatalogType(
        type: ProductType::Bundle,
        soldIndividually: true,
    );

    expect($product->type())->toBe(ProductType::Bundle)
        ->and($product->isSoldIndividually())->toBeTrue()
        ->and($product->externalUrl())->toBeNull();
});

it('does not update product type settings on an archived product', function () {
    $product = aTypedProduct();
    $product->archive();

    $product->changeCatalogType(ProductType::Virtual, true);
})->throws(CannotUpdateArchivedProduct::class);