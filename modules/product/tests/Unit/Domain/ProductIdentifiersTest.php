<?php

use Modules\Product\Domain\Exceptions\CannotUpdateArchivedProduct;
use Modules\Product\Domain\Exceptions\EmptySku;
use Modules\Product\Domain\Exceptions\EmptyTitle;
use Modules\Product\Domain\Product;
use Modules\Product\Domain\ProductVariant;
use Modules\Product\Domain\ProductVisibility;

function anIdentifierVariant(array $overrides = []): ProductVariant
{
    return ProductVariant::create(
        id: $overrides['id'] ?? '01900000-0000-7000-8000-0000000000a1',
        sku: $overrides['sku'] ?? 'WOOL-COAT',
        barcode: $overrides['barcode'] ?? null,
        gtin: $overrides['gtin'] ?? null,
        mpn: $overrides['mpn'] ?? null,
    );
}

function aListedProduct(array $overrides = []): Product
{
    return Product::create(
        id: $overrides['id'] ?? '01900000-0000-7000-8000-000000000001',
        title: $overrides['title'] ?? 'Wool coat',
        variant: $overrides['variant'] ?? anIdentifierVariant(),
        shortDescription: $overrides['shortDescription'] ?? null,
        description: $overrides['description'] ?? null,
        brand: $overrides['brand'] ?? null,
        visibility: $overrides['visibility'] ?? ProductVisibility::Visible,
        featured: $overrides['featured'] ?? false,
    );
}

it('creates a listing with copy, optional brand, and a default variant', function () {
    $product = aListedProduct([
        'shortDescription' => 'Warm wool.',
        'description' => 'A long coat for winter.',
        'brand' => 'North Mill',
        'variant' => anIdentifierVariant([
            'sku' => 'WOOL-COAT',
            'barcode' => '0123456789012',
            'gtin' => '0123456789012',
            'mpn' => 'NM-WOOL-1',
        ]),
    ]);

    expect($product->title())->toBe('Wool coat')
        ->and($product->shortDescription())->toBe('Warm wool.')
        ->and($product->description())->toBe('A long coat for winter.')
        ->and($product->brand())->toBe('North Mill')
        ->and($product->defaultVariant()->sku())->toBe('WOOL-COAT')
        ->and($product->defaultVariant()->barcode())->toBe('0123456789012')
        ->and($product->defaultVariant()->gtin())->toBe('0123456789012')
        ->and($product->defaultVariant()->mpn())->toBe('NM-WOOL-1')
        ->and($product->defaultVariant()->isDefault())->toBeTrue();
});

it('does not create a variant with an empty sku', function () {
    anIdentifierVariant(['sku' => '']);
})->throws(EmptySku::class);

it('does not create a variant with a blank sku', function () {
    anIdentifierVariant(['sku' => '   ']);
})->throws(EmptySku::class);

it('does not create a listing with an empty title', function () {
    aListedProduct(['title' => '']);
})->throws(EmptyTitle::class);

it('updates listing copy and brand on a draft', function () {
    $product = aListedProduct();

    $product->updateDetails(
        title: 'Wool coat updated',
        shortDescription: 'Short',
        description: 'Long',
        brand: 'North Mill',
    );

    expect($product->title())->toBe('Wool coat updated')
        ->and($product->shortDescription())->toBe('Short')
        ->and($product->description())->toBe('Long')
        ->and($product->brand())->toBe('North Mill');
});

it('leaves omitted listing details unchanged', function () {
    $product = aListedProduct([
        'shortDescription' => 'Warm wool.',
        'brand' => 'North Mill',
    ]);

    $product->updateDetails(title: 'Renamed');

    expect($product->title())->toBe('Renamed')
        ->and($product->shortDescription())->toBe('Warm wool.')
        ->and($product->brand())->toBe('North Mill');
});

it('clears optional listing details with an empty string', function () {
    $product = aListedProduct([
        'shortDescription' => 'Warm wool.',
        'description' => 'Long',
        'brand' => 'North Mill',
    ]);

    $product->updateDetails(
        shortDescription: '',
        description: '',
        brand: '',
    );

    expect($product->shortDescription())->toBeNull()
        ->and($product->description())->toBeNull()
        ->and($product->brand())->toBeNull();
});

it('does not update details with an empty title', function () {
    $product = aListedProduct();

    $product->updateDetails(title: '  ');
})->throws(EmptyTitle::class);

it('changes default variant identifiers', function () {
    $product = aListedProduct();

    $product->changeDefaultIdentifiers(
        sku: 'WOOL-COAT-2',
        barcode: '999',
        gtin: '111',
        mpn: 'NM-2',
    );

    expect($product->defaultVariant()->sku())->toBe('WOOL-COAT-2')
        ->and($product->defaultVariant()->barcode())->toBe('999')
        ->and($product->defaultVariant()->gtin())->toBe('111')
        ->and($product->defaultVariant()->mpn())->toBe('NM-2');
});

it('does not change identifiers to an empty sku', function () {
    $product = aListedProduct();

    $product->changeDefaultIdentifiers(sku: '  ');
})->throws(EmptySku::class);

it('does not update details on an archived product', function () {
    $product = aListedProduct();
    $product->archive();

    $product->updateDetails(brand: 'North Mill');
})->throws(CannotUpdateArchivedProduct::class);

it('does not change identifiers on an archived product', function () {
    $product = aListedProduct();
    $product->archive();

    $product->changeDefaultIdentifiers(sku: 'OTHER');
})->throws(CannotUpdateArchivedProduct::class);
