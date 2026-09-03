<?php

use Modules\Product\Domain\Exceptions\CannotArchiveProduct;
use Modules\Product\Domain\Exceptions\CannotPublishProduct;
use Modules\Product\Domain\Exceptions\CannotUpdateArchivedProduct;
use Modules\Product\Domain\Product;
use Modules\Product\Domain\ProductStatus;
use Modules\Product\Domain\ProductVariant;
use Modules\Product\Domain\ProductVisibility;

function aProduct(array $overrides = []): Product
{
    return Product::create(
        id: $overrides['id'] ?? '01900000-0000-7000-8000-000000000001',
        title: $overrides['title'] ?? 'Wool coat',
        variant: $overrides['variant'] ?? ProductVariant::create(
            id: '01900000-0000-7000-8000-0000000000a1',
            sku: 'WOOL-COAT',
        ),
        visibility: $overrides['visibility'] ?? ProductVisibility::Visible,
        featured: $overrides['featured'] ?? false,
    );
}

it('creates a product as draft, visible, and not featured', function () {
    $product = aProduct();

    expect($product->status())->toBe(ProductStatus::Draft)
        ->and($product->visibility())->toBe(ProductVisibility::Visible)
        ->and($product->isFeatured())->toBeFalse()
        ->and($product->title())->toBe('Wool coat');
});

it('creates a product with catalog visibility and featured', function () {
    $product = aProduct([
        'visibility' => ProductVisibility::Catalog,
        'featured' => true,
    ]);

    expect($product->visibility())->toBe(ProductVisibility::Catalog)
        ->and($product->isFeatured())->toBeTrue();
});

it('publishes a draft to active', function () {
    $product = aProduct();

    $product->publish();

    expect($product->status())->toBe(ProductStatus::Active);
});

it('does not publish an active product', function () {
    $product = aProduct();
    $product->publish();

    $product->publish();
})->throws(CannotPublishProduct::class);

it('does not publish an archived product', function () {
    $product = aProduct();
    $product->archive();

    $product->publish();
})->throws(CannotPublishProduct::class);

it('archives a draft', function () {
    $product = aProduct();

    $product->archive();

    expect($product->status())->toBe(ProductStatus::Archived);
});

it('archives an active product', function () {
    $product = aProduct();
    $product->publish();

    $product->archive();

    expect($product->status())->toBe(ProductStatus::Archived);
});

it('does not archive an archived product', function () {
    $product = aProduct();
    $product->archive();

    $product->archive();
})->throws(CannotArchiveProduct::class);

it('returns an active product to draft', function () {
    $product = aProduct();
    $product->publish();

    $product->draft();

    expect($product->status())->toBe(ProductStatus::Draft);
});

it('returns an archived product to draft', function () {
    $product = aProduct();
    $product->archive();

    $product->draft();

    expect($product->status())->toBe(ProductStatus::Draft);
});

it('updates visibility and featured on a draft', function () {
    $product = aProduct();

    $product->updateListing(
        visibility: ProductVisibility::Search,
        featured: true,
    );

    expect($product->visibility())->toBe(ProductVisibility::Search)
        ->and($product->isFeatured())->toBeTrue()
        ->and($product->status())->toBe(ProductStatus::Draft);
});

it('updates visibility on an active product', function () {
    $product = aProduct();
    $product->publish();

    $product->updateListing(visibility: ProductVisibility::Hidden);

    expect($product->visibility())->toBe(ProductVisibility::Hidden)
        ->and($product->status())->toBe(ProductStatus::Active);
});

it('does not update listing flags on an archived product', function () {
    $product = aProduct();
    $product->archive();

    $product->updateListing(featured: true);
})->throws(CannotUpdateArchivedProduct::class);

it('keeps featured independent of visibility', function () {
    $product = aProduct([
        'visibility' => ProductVisibility::Hidden,
        'featured' => true,
    ]);

    expect($product->visibility())->toBe(ProductVisibility::Hidden)
        ->and($product->isFeatured())->toBeTrue();
});
