<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Product\Domain\Product;
use Modules\Product\Domain\ProductStatus;
use Modules\Product\Domain\ProductVariant;
use Modules\Product\Domain\ProductTaxStatus;
use Modules\Product\Domain\ProductType;
use Modules\Product\Domain\ProductVisibility;
use Modules\Product\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'database.connections.product' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
    ]);

    DB::purge('product');

    Schema::connection('product')->create('products', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('title');
        $table->text('short_description')->nullable();
        $table->text('description')->nullable();
        $table->string('brand')->nullable();
        $table->string('status');
        $table->string('visibility');
        $table->boolean('featured')->default(false);
        $table->boolean('sold_individually')->default(false);
        $table->string('default_variant_id')->nullable();
        $table->string('type')->default('physical');
        $table->string('slug');
        $table->string('external_url')->nullable();
        $table->string('meta_title')->nullable();
        $table->text('meta_description')->nullable();
        $table->string('tax_status')->default('taxable');
        $table->string('tax_class')->nullable();
        $table->integer('weight_g')->nullable();
        $table->integer('length_mm')->nullable();
        $table->integer('width_mm')->nullable();
        $table->integer('height_mm')->nullable();
        $table->string('shipping_class')->nullable();
        $table->timestamps();
    });

    Schema::connection('product')->create('product_variants', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('product_id');
        $table->string('sku')->unique();
        $table->string('barcode')->nullable();
        $table->string('gtin')->nullable();
        $table->string('mpn')->nullable();
        $table->boolean('is_default')->default(true);
        $table->integer('price_minor');
        $table->integer('compare_at_minor')->nullable();
        $table->integer('cost_minor')->nullable();
        $table->string('currency', 3);
        $table->string('sale_starts_at')->nullable();
        $table->string('sale_ends_at')->nullable();
        $table->integer('weight_g')->nullable();
        $table->integer('length_mm')->nullable();
        $table->integer('width_mm')->nullable();
        $table->integer('height_mm')->nullable();
        $table->timestamps();
    });
});

function aPersistedProduct(array $overrides = []): Product
{
    return Product::create(
        id: $overrides['id'] ?? '01900000-0000-7000-8000-000000000001',
        title: $overrides['title'] ?? 'Wool coat',
        variant: ProductVariant::create(
            id: $overrides['variantId'] ?? '01900000-0000-7000-8000-0000000000a1',
            sku: $overrides['sku'] ?? 'WOOL-COAT',
            barcode: $overrides['barcode'] ?? null,
            gtin: $overrides['gtin'] ?? null,
            mpn: $overrides['mpn'] ?? null,
            priceMinor: $overrides['priceMinor'] ?? 0,
            currency: $overrides['currency'] ?? 'XXX',
            compareAtMinor: $overrides['compareAtMinor'] ?? null,
            costMinor: $overrides['costMinor'] ?? null,
            saleStartsAt: $overrides['saleStartsAt'] ?? null,
            saleEndsAt: $overrides['saleEndsAt'] ?? null,
        ),
        shortDescription: $overrides['shortDescription'] ?? null,
        description: $overrides['description'] ?? null,
        brand: $overrides['brand'] ?? null,
        visibility: $overrides['visibility'] ?? ProductVisibility::Visible,
        featured: $overrides['featured'] ?? false,
        taxStatus: $overrides['taxStatus'] ?? ProductTaxStatus::Taxable,
        taxClass: $overrides['taxClass'] ?? null,
        type: $overrides['type'] ?? ProductType::Physical,
        soldIndividually: $overrides['soldIndividually'] ?? false,
        externalUrl: $overrides['externalUrl'] ?? null,
        slug: $overrides['slug'] ?? null,
        metaTitle: $overrides['metaTitle'] ?? null,
        metaDescription: $overrides['metaDescription'] ?? null,
    );
}

it('round-trips product type, sold individually, and external URL', function () {
    $repository = new EloquentProductRepository();
    $product = aPersistedProduct([
        'id' => '01900000-0000-7000-8000-000000000008',
        'variantId' => '01900000-0000-7000-8000-0000000000a8',
        'sku' => 'WOOL-COAT-8',
        'type' => ProductType::External,
        'soldIndividually' => true,
        'externalUrl' => 'https://merchant.example/wool-coat',
    ]);

    $repository->save($product);
    $found = $repository->find($product->id());

    expect($found->type())->toBe(ProductType::External)
        ->and($found->isSoldIndividually())->toBeTrue()
        ->and($found->externalUrl())->toBe('https://merchant.example/wool-coat');
});

it('round-trips SEO slug and metadata', function () {
    $repository = new EloquentProductRepository();
    $product = aPersistedProduct([
        'id' => '01900000-0000-7000-8000-000000000011',
        'variantId' => '01900000-0000-7000-8000-0000000000b1',
        'sku' => 'WOOL-COAT-11',
        'slug' => 'winter-wool-coat',
        'metaTitle' => 'Winter Wool Coat',
        'metaDescription' => 'A warm coat for winter.',
    ]);

    $repository->save($product);
    $found = $repository->find($product->id());

    expect($found->slug())->toBe('winter-wool-coat')
        ->and($found->metaTitle())->toBe('Winter Wool Coat')
        ->and($found->metaDescription())->toBe('A warm coat for winter.');
});

it('round-trips pricing and tax settings', function () {
    $repository = new EloquentProductRepository();
    $product = aPersistedProduct([
        'id' => '01900000-0000-7000-8000-000000000004',
        'variantId' => '01900000-0000-7000-8000-0000000000a4',
        'sku' => 'WOOL-COAT-4',
        'priceMinor' => 12999,
        'currency' => 'USD',
        'compareAtMinor' => 15999,
        'costMinor' => 5000,
        'saleStartsAt' => new DateTimeImmutable('2026-10-01T00:00:00+00:00'),
        'saleEndsAt' => new DateTimeImmutable('2026-10-31T23:59:59+00:00'),
        'taxStatus' => ProductTaxStatus::Taxable,
        'taxClass' => 'standard',
    ]);

    $repository->save($product);
    $found = $repository->find($product->id());

    expect($found->defaultVariant()->priceMinor())->toBe(12999)
        ->and($found->defaultVariant()->currency())->toBe('USD')
        ->and($found->defaultVariant()->compareAtMinor())->toBe(15999)
        ->and($found->defaultVariant()->costMinor())->toBe(5000)
        ->and($found->defaultVariant()->saleStartsAt())->toEqual(new DateTimeImmutable('2026-10-01T00:00:00+00:00'))
        ->and($found->defaultVariant()->saleEndsAt())->toEqual(new DateTimeImmutable('2026-10-31T23:59:59+00:00'))
        ->and($found->taxStatus())->toBe(ProductTaxStatus::Taxable)
        ->and($found->taxClass())->toBe('standard');
});

it('round-trips a draft product', function () {
    $repository = new EloquentProductRepository();
    $product = aPersistedProduct();

    $repository->save($product);
    $found = $repository->find($product->id());

    expect($found)->not->toBeNull()
        ->and($found->title())->toBe('Wool coat')
        ->and($found->status())->toBe(ProductStatus::Draft)
        ->and($found->visibility())->toBe(ProductVisibility::Visible)
        ->and($found->isFeatured())->toBeFalse()
        ->and($found->defaultVariant()->sku())->toBe('WOOL-COAT');

    $this->assertDatabaseHas('products', [
        'id' => '01900000-0000-7000-8000-000000000001',
        'title' => 'Wool coat',
        'status' => 'draft',
        'visibility' => 'visible',
        'default_variant_id' => '01900000-0000-7000-8000-0000000000a1',
    ], 'product');
});

it('round-trips catalog visibility and featured', function () {
    $repository = new EloquentProductRepository();
    $product = aPersistedProduct([
        'id' => '01900000-0000-7000-8000-000000000002',
        'title' => 'Linen shirt',
        'sku' => 'LINEN-SHIRT',
        'variantId' => '01900000-0000-7000-8000-0000000000a2',
        'visibility' => ProductVisibility::Catalog,
        'featured' => true,
    ]);

    $repository->save($product);
    $found = $repository->find($product->id());

    expect($found->visibility())->toBe(ProductVisibility::Catalog)
        ->and($found->isFeatured())->toBeTrue();
});

it('persists a status change', function () {
    $repository = new EloquentProductRepository();
    $product = aPersistedProduct([
        'id' => '01900000-0000-7000-8000-000000000003',
        'variantId' => '01900000-0000-7000-8000-0000000000a3',
        'sku' => 'WOOL-COAT-3',
    ]);
    $repository->save($product);

    $product->publish();
    $repository->save($product);

    expect($repository->find($product->id())->status())->toBe(ProductStatus::Active);

    $this->assertDatabaseHas('products', [
        'id' => '01900000-0000-7000-8000-000000000003',
        'status' => 'active',
    ], 'product');
});

it('returns null when the product is missing', function () {
    $repository = new EloquentProductRepository();

    expect($repository->find('01900000-0000-7000-8000-000000000099'))->toBeNull();
});

it('round-trips listing copy, brand, and sku codes', function () {
    $repository = new EloquentProductRepository();
    $product = aPersistedProduct([
        'shortDescription' => 'Warm wool.',
        'description' => 'A long coat for winter.',
        'brand' => 'North Mill',
        'barcode' => '0123456789012',
        'gtin' => '0123456789012',
        'mpn' => 'NM-WOOL-1',
    ]);

    $repository->save($product);
    $found = $repository->find($product->id());

    expect($found->shortDescription())->toBe('Warm wool.')
        ->and($found->description())->toBe('A long coat for winter.')
        ->and($found->brand())->toBe('North Mill')
        ->and($found->defaultVariant()->barcode())->toBe('0123456789012')
        ->and($found->defaultVariant()->gtin())->toBe('0123456789012')
        ->and($found->defaultVariant()->mpn())->toBe('NM-WOOL-1')
        ->and($found->defaultVariant()->isDefault())->toBeTrue();

    $this->assertDatabaseHas('product_variants', [
        'id' => '01900000-0000-7000-8000-0000000000a1',
        'product_id' => '01900000-0000-7000-8000-000000000001',
        'sku' => 'WOOL-COAT',
        'is_default' => true,
        'price_minor' => 0,
        'currency' => 'XXX',
    ], 'product');
});

it('reports when a sku is taken, ignoring the same variant', function () {
    $repository = new EloquentProductRepository();
    $repository->save(aPersistedProduct());

    expect($repository->skuTaken('WOOL-COAT'))->toBeTrue()
        ->and($repository->skuTaken('WOOL-COAT', '01900000-0000-7000-8000-0000000000a1'))->toBeFalse()
        ->and($repository->skuTaken('OTHER'))->toBeFalse();
});
