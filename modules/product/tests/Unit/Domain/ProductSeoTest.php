<?php

use Modules\Product\Domain\Product;
use Modules\Product\Domain\ProductVariant;

function anSeoProduct(array $overrides = []): Product
{
    return Product::create(
        id: '01900000-0000-7000-8000-000000000009',
        title: $overrides['title'] ?? 'Wool Coat',
        variant: ProductVariant::create(
            id: '01900000-0000-7000-8000-0000000000a9',
            sku: 'WOOL-COAT-9',
        ),
        slug: $overrides['slug'] ?? null,
        metaTitle: $overrides['metaTitle'] ?? null,
        metaDescription: $overrides['metaDescription'] ?? null,
    );
}

it('creates a URL slug from the title when none is supplied', function () {
    expect(anSeoProduct()->slug())->toBe('wool-coat');
});

it('stores explicit slug and meta fields', function () {
    $product = anSeoProduct([
        'slug' => 'winter-wool-coat',
        'metaTitle' => 'Winter Wool Coat | North Mill',
        'metaDescription' => 'A warm wool coat for winter.',
    ]);

    expect($product->slug())->toBe('winter-wool-coat')
        ->and($product->metaTitle())->toBe('Winter Wool Coat | North Mill')
        ->and($product->metaDescription())->toBe('A warm wool coat for winter.');
});

it('updates SEO fields', function () {
    $product = anSeoProduct();

    $product->changeSeo('new-wool-coat', 'New Wool Coat', 'Updated description');

    expect($product->slug())->toBe('new-wool-coat')
        ->and($product->metaTitle())->toBe('New Wool Coat')
        ->and($product->metaDescription())->toBe('Updated description');
});