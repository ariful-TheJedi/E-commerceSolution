<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
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

    Schema::connection('product')->create('attributes', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('data_type');
        $table->boolean('filterable')->default(false);
        $table->boolean('sortable')->default(false);
        $table->boolean('visible_on_pdp')->default(true);
        $table->timestamps();
    });

    Schema::connection('product')->create('attribute_options', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('attribute_id');
        $table->string('label');
        $table->string('slug');
        $table->integer('position')->default(0);
        $table->string('color_hex')->nullable();
        $table->string('image_path')->nullable();
        $table->timestamps();
    });

    Schema::connection('product')->create('product_attribute_values', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('product_id');
        $table->string('variant_id')->nullable();
        $table->string('attribute_id');
        $table->text('value_text')->nullable();
        $table->string('attribute_option_id')->nullable();
        $table->timestamps();
    });

    Schema::connection('product')->create('product_options', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('product_id');
        $table->string('name');
        $table->integer('position')->default(0);
        $table->timestamps();
    });

    Schema::connection('product')->create('product_option_values', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('option_id');
        $table->string('label');
        $table->string('slug');
        $table->integer('position')->default(0);
        $table->string('color_hex')->nullable();
        $table->string('image_path')->nullable();
        $table->timestamps();
    });

    Schema::connection('product')->create('variant_option_values', function (Blueprint $table) {
        $table->string('variant_id');
        $table->string('option_value_id');
        $table->string('option_id');
        $table->primary(['variant_id', 'option_value_id']);
    });

    Schema::connection('product')->create('product_media', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('product_id');
        $table->string('variant_id')->nullable();
        $table->string('kind')->default('image');
        $table->text('path');
        $table->text('alt')->nullable();
        $table->integer('position')->default(0);
        $table->boolean('is_primary')->default(false);
        $table->timestamps();
    });

    Schema::connection('product')->create('digital_files', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('product_id');
        $table->string('variant_id')->nullable();
        $table->text('path');
        $table->integer('download_limit')->nullable();
        $table->integer('expires_after_days')->nullable();
        $table->timestamps();
    });

    Schema::connection('product')->create('categories', function (Blueprint $table) {
        $table->string('id')->primary(); $table->string('parent_id')->nullable(); $table->string('name'); $table->string('slug')->unique(); $table->integer('position')->default(0); $table->timestamps();
    });
    Schema::connection('product')->create('product_categories', function (Blueprint $table) {
        $table->string('product_id'); $table->string('category_id'); $table->boolean('is_canonical')->default(false); $table->integer('position')->default(0); $table->primary(['product_id', 'category_id']);
    });
    Schema::connection('product')->create('tags', function (Blueprint $table) {
        $table->string('id')->primary(); $table->string('name'); $table->string('slug')->unique(); $table->timestamps();
    });
    Schema::connection('product')->create('product_tags', function (Blueprint $table) {
        $table->string('product_id'); $table->string('tag_id'); $table->primary(['product_id', 'tag_id']);
    });
    Schema::connection('product')->create('collections', function (Blueprint $table) {
        $table->string('id')->primary(); $table->string('name'); $table->string('slug')->unique(); $table->string('kind'); $table->string('match')->nullable(); $table->timestamps();
    });
    Schema::connection('product')->create('collection_rules', function (Blueprint $table) {
        $table->string('id')->primary(); $table->string('collection_id'); $table->string('field'); $table->string('operator'); $table->text('value'); $table->timestamps();
    });
    Schema::connection('product')->create('collection_products', function (Blueprint $table) {
        $table->string('collection_id'); $table->string('product_id'); $table->integer('position')->default(0); $table->primary(['collection_id', 'product_id']);
    });
    Schema::connection('product')->create('product_relations', function (Blueprint $table) {
        $table->string('from_product_id'); $table->string('to_product_id'); $table->string('kind'); $table->primary(['from_product_id', 'to_product_id', 'kind']);
    });

    Schema::connection('product')->create('url_redirects', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('from_path')->unique();
        $table->string('to_path');
        $table->timestamps();
    });

    Schema::connection('product')->create('outbox', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('type');
        $table->text('payload');
        $table->string('occurred_at');
        $table->string('published_at')->nullable();
    });
});

function createListing(array $payload = []): TestResponse
{
    return test()->postJson('/api/v1/products', [
        'title' => $payload['title'] ?? 'Wool coat',
        'sku' => $payload['sku'] ?? 'WOOL-COAT',
        'visibility' => $payload['visibility'] ?? 'visible',
        'featured' => $payload['featured'] ?? false,
        ...array_filter(
            [
                'short_description' => $payload['short_description'] ?? null,
                'description' => $payload['description'] ?? null,
                'brand' => $payload['brand'] ?? null,
                'barcode' => $payload['barcode'] ?? null,
                'gtin' => $payload['gtin'] ?? null,
                'mpn' => $payload['mpn'] ?? null,
                'price_minor' => $payload['price_minor'] ?? null,
                'compare_at_minor' => $payload['compare_at_minor'] ?? null,
                'cost_minor' => $payload['cost_minor'] ?? null,
                'currency' => $payload['currency'] ?? null,
                'sale_starts_at' => $payload['sale_starts_at'] ?? null,
                'sale_ends_at' => $payload['sale_ends_at'] ?? null,
                'tax_status' => $payload['tax_status'] ?? null,
                'tax_class' => $payload['tax_class'] ?? null,
                'type' => $payload['type'] ?? null,
                'sold_individually' => $payload['sold_individually'] ?? null,
                'external_url' => $payload['external_url'] ?? null,
                'slug' => $payload['slug'] ?? null,
                'meta_title' => $payload['meta_title'] ?? null,
                'meta_description' => $payload['meta_description'] ?? null,
                'weight_g' => $payload['weight_g'] ?? null,
                'length_mm' => $payload['length_mm'] ?? null,
                'width_mm' => $payload['width_mm'] ?? null,
                'height_mm' => $payload['height_mm'] ?? null,
                'variant_weight_g' => $payload['variant_weight_g'] ?? null,
                'variant_length_mm' => $payload['variant_length_mm'] ?? null,
                'variant_width_mm' => $payload['variant_width_mm'] ?? null,
                'variant_height_mm' => $payload['variant_height_mm'] ?? null,
                'shipping_class' => $payload['shipping_class'] ?? null,
            ],
            fn ($value) => $value !== null,
        ),
    ], [
        'Idempotency-Key' => $payload['idempotency'] ?? '01900000-0000-7000-8000-00000000key1',
    ]);
}

it('rejects create without a title as problem details', function () {
    $response = $this->postJson('/api/v1/products', [
        'sku' => 'WOOL-COAT',
        'visibility' => 'visible',
    ], [
        'Idempotency-Key' => '01900000-0000-7000-8000-00000000key1',
    ]);

    $response->assertStatus(422)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'https://ecommercesolution.test/problems/validation')
        ->assertJsonPath('status', 422);
});

it('creates reusable specifications, swatches, and product assignments', function () {
    $attributeId = $this->postJson('/api/v1/attributes', [
        'name' => 'Material',
        'slug' => 'material',
        'data_type' => 'enum',
        'filterable' => true,
    ])->assertCreated()->json('data.id');

    $optionId = $this->postJson('/api/v1/attributes/'.$attributeId.'/options', [
        'label' => 'Wool',
        'slug' => 'wool',
        'color_hex' => '#AA7744',
    ])->assertCreated()->json('data.id');

    $product = createListing(['sku' => 'WOOL-COAT-SPECIFICATION'])->json('data');
    $variantId = DB::connection('product')->table('product_variants')->value('id');

    $this->putJson('/api/v1/products/'.$product['id'].'/specifications', [
        'attribute_id' => $attributeId,
        'attribute_option_id' => $optionId,
    ])->assertCreated();

    $textAttributeId = $this->postJson('/api/v1/attributes', [
        'name' => 'Warranty', 'slug' => 'warranty', 'data_type' => 'text',
    ])->assertCreated()->json('data.id');

    $this->putJson('/api/v1/products/'.$product['id'].'/specifications', [
        'attribute_id' => $textAttributeId,
        'variant_id' => $variantId,
        'value' => '2 years',
    ])->assertCreated();

    expect(DB::connection('product')->table('product_attribute_values')->count())->toBe(2);
});

it('creates option combinations with unique SKUs and one default', function () {
    $product = createListing(['sku' => 'SHIRT-BASE'])->json('data');
    $optionId = $this->postJson('/api/v1/products/'.$product['id'].'/options', ['name' => 'Size'])->assertCreated()->json('data.id');
    $smallId = $this->postJson('/api/v1/product-options/'.$optionId.'/values', ['label' => 'Small', 'slug' => 'small'])->assertCreated()->json('data.id');
    $largeId = $this->postJson('/api/v1/product-options/'.$optionId.'/values', ['label' => 'Large', 'slug' => 'large'])->assertCreated()->json('data.id');

    $this->postJson('/api/v1/products/'.$product['id'].'/variants', ['sku' => 'SHIRT-S', 'option_value_ids' => [$smallId], 'is_default' => true, 'price_minor' => 1000, 'currency' => 'USD'])->assertCreated();
    $this->postJson('/api/v1/products/'.$product['id'].'/variants', ['sku' => 'SHIRT-L', 'option_value_ids' => [$largeId], 'is_default' => true, 'price_minor' => 1100, 'currency' => 'USD'])->assertCreated();

    $this->postJson('/api/v1/products/'.$product['id'].'/variants', ['sku' => 'SHIRT-DUP', 'option_value_ids' => [$largeId, $largeId]])->assertStatus(422);

    expect(DB::connection('product')->table('product_variants')->where('product_id', $product['id'])->where('is_default', true)->count())->toBe(1)
        ->and(DB::connection('product')->table('variant_option_values')->count())->toBe(2);
});

it('manages ordered product media, primary images, variant maps, and digital files', function () {
    $product = createListing(['sku' => 'MEDIA-BASE'])->json('data');
    $variantId = DB::connection('product')->table('product_variants')->value('id');

    $firstId = $this->postJson('/api/v1/products/'.$product['id'].'/media', [
        'path' => 'products/media/front.webp', 'position' => 0, 'is_primary' => true,
    ])->assertCreated()->json('data.id');
    $secondId = $this->postJson('/api/v1/products/'.$product['id'].'/media', [
        'path' => 'products/media/detail.webp', 'position' => 1, 'variant_id' => $variantId,
    ])->assertCreated()->json('data.id');

    $this->patchJson('/api/v1/products/'.$product['id'].'/media/'.$secondId, [
        'position' => 0, 'is_primary' => true,
    ])->assertOk();

    $media = DB::connection('product')->table('product_media')->orderBy('position')->get();
    expect($media)->toHaveCount(2)
        ->and($media->first()->id)->toBe($secondId)
        ->and($media->first()->is_primary)->toBe(1)
        ->and($media->first()->variant_id)->toBe($variantId)
        ->and($media->last()->id)->toBe($firstId)
        ->and($media->last()->is_primary)->toBe(0);

    $this->postJson('/api/v1/products/'.$product['id'].'/digital-files', [
        'path' => 'products/files/manual.pdf', 'download_limit' => 3, 'expires_after_days' => 30,
    ])->assertCreated();

    expect(DB::connection('product')->table('digital_files')->first())->toMatchArray([
        'product_id' => $product['id'], 'path' => 'products/files/manual.pdf',
        'download_limit' => 3, 'expires_after_days' => 30,
    ]);
});

it('rejects media mapped to a different product and invalid download limits', function () {
    $first = createListing(['sku' => 'MEDIA-ONE'])->json('data');
    createListing(['title' => 'Different media product', 'sku' => 'MEDIA-TWO']);
    $otherVariantId = DB::connection('product')->table('product_variants')->orderBy('id', 'desc')->value('id');

    $this->postJson('/api/v1/products/'.$first['id'].'/media', [
        'path' => 'products/media/invalid.webp', 'variant_id' => $otherVariantId,
    ])->assertStatus(422);

    $this->postJson('/api/v1/products/'.$first['id'].'/digital-files', [
        'path' => 'products/files/invalid.pdf', 'download_limit' => 0,
    ])->assertStatus(422);
});

it('creates nested taxonomies and assigns categories, tags, and collections', function () {
    $rootId = $this->postJson('/api/v1/categories', ['name' => 'Clothing', 'slug' => 'clothing'])->assertCreated()->json('data.id');
    $childId = $this->postJson('/api/v1/categories', ['name' => 'Coats', 'slug' => 'coats', 'parent_id' => $rootId])->assertCreated()->json('data.id');
    $tagId = $this->postJson('/api/v1/tags', ['name' => 'Winter', 'slug' => 'winter'])->assertCreated()->json('data.id');
    $collectionId = $this->postJson('/api/v1/collections', ['name' => 'Winter coats', 'slug' => 'winter-coats', 'kind' => 'automatic', 'match' => 'all'])->assertCreated()->json('data.id');
    $product = createListing(['title' => 'Taxonomy coat', 'sku' => 'TAXONOMY-COAT'])->json('data');

    $this->putJson('/api/v1/products/'.$product['id'].'/categories/'.$rootId, ['canonical' => true])->assertOk();
    $this->putJson('/api/v1/products/'.$product['id'].'/categories/'.$childId, ['canonical' => true, 'position' => 1])->assertOk();
    $this->putJson('/api/v1/products/'.$product['id'].'/tags/'.$tagId)->assertOk();
    $this->postJson('/api/v1/collections/'.$collectionId.'/rules', ['field' => 'tag', 'operator' => 'eq', 'value' => 'winter'])->assertCreated();
    $this->putJson('/api/v1/collections/'.$collectionId.'/products/'.$product['id'], ['position' => 2])->assertOk();

    expect(DB::connection('product')->table('categories')->where('parent_id', $rootId)->value('id'))->toBe($childId)
        ->and(DB::connection('product')->table('product_categories')->where('product_id', $product['id'])->where('is_canonical', true)->value('category_id'))->toBe($childId)
        ->and(DB::connection('product')->table('product_tags')->count())->toBe(1)
        ->and(DB::connection('product')->table('collection_rules')->where('collection_id', $collectionId)->value('field'))->toBe('tag')
        ->and(DB::connection('product')->table('collection_products')->where('collection_id', $collectionId)->value('position'))->toBe(2);
});

it('rejects invalid taxonomy parents, memberships, and automatic collection matches', function () {
    $product = createListing(['title' => 'Taxonomy invalid', 'sku' => 'TAXONOMY-INVALID'])->json('data');
    $this->postJson('/api/v1/categories', ['name' => 'Bad child', 'slug' => 'bad-child', 'parent_id' => 'missing'])->assertStatus(422);
    $this->putJson('/api/v1/products/'.$product['id'].'/categories/missing')->assertStatus(422);
    $this->postJson('/api/v1/collections', ['name' => 'Bad collection', 'slug' => 'bad-collection', 'kind' => 'automatic', 'match' => 'never'])->assertStatus(422);
});

it('creates manual product relationships without self-links or duplicates', function () {
    $source = createListing(['title' => 'Source product', 'sku' => 'RELATION-SOURCE'])->json('data');
    $target = createListing(['title' => 'Target product', 'sku' => 'RELATION-TARGET'])->json('data');

    foreach (['related', 'upsell', 'cross_sell', 'alternative', 'fbt'] as $kind) {
        $this->putJson('/api/v1/products/'.$source['id'].'/relationships/'.$target['id'], ['kind' => $kind])->assertCreated();
    }
    $this->putJson('/api/v1/products/'.$source['id'].'/relationships/'.$target['id'], ['kind' => 'related'])->assertCreated();

    expect(DB::connection('product')->table('product_relations')->count())->toBe(5);

    $this->putJson('/api/v1/products/'.$source['id'].'/relationships/'.$source['id'], ['kind' => 'related'])->assertStatus(422);
    $this->putJson('/api/v1/products/'.$source['id'].'/relationships/missing', ['kind' => 'related'])->assertStatus(422);
});

it('supports cursor admin listing, duplication, bulk edits, and CSV operations', function () {
    $first = createListing(['title' => 'Admin alpha', 'sku' => 'ADMIN-ALPHA'])->json('data');
    $second = createListing(['title' => 'Admin beta', 'sku' => 'ADMIN-BETA'])->json('data');
    $third = createListing(['title' => 'Admin gamma', 'sku' => 'ADMIN-GAMMA'])->json('data');

    $page = $this->getJson('/api/v1/admin/products?limit=2')->assertOk()->json();
    expect($page['data'])->toHaveCount(2)->and($page['next_cursor'])->not->toBeNull();
    $nextPage = $this->getJson('/api/v1/admin/products?limit=2&cursor='.urlencode($page['next_cursor']))->assertOk()->json();
    expect($nextPage['data'])->toHaveCount(1)->and($nextPage['data'][0]['id'])->toBe($third['id']);

    $copyId = $this->postJson('/api/v1/admin/products/'.$first['id'].'/duplicate', ['sku' => 'ADMIN-ALPHA-COPY', 'slug' => 'admin-alpha-copy'])->assertCreated()->json('data.id');
    expect(DB::connection('product')->table('products')->where('id', $copyId)->value('title'))->toBe('Admin alpha (Copy)')
        ->and(DB::connection('product')->table('product_variants')->where('product_id', $copyId)->value('sku'))->toBe('ADMIN-ALPHA-COPY');

    $this->patchJson('/api/v1/admin/products/bulk', ['ids' => [$first['id'], $second['id']], 'visibility' => 'hidden', 'featured' => true])->assertOk();
    expect(DB::connection('product')->table('products')->whereIn('id', [$first['id'], $second['id']])->where('visibility', 'hidden')->where('featured', true)->count())->toBe(2);
    expect(DB::connection('product')->table('outbox')->where('type', Modules\Product\Contracts\Events\ProductUpdated::class)->count())->toBeGreaterThanOrEqual(2);

    $csv = "title,sku,slug\nImported product,IMPORTED-1,imported-product\n";
    $this->postJson('/api/v1/admin/products/import', ['csv' => $csv])->assertCreated()->assertJsonPath('data.imported', 1);
    $this->get('/api/v1/admin/products/export')->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8')->assertSee('ADMIN-ALPHA')->assertSee('IMPORTED-1');
});

it('rejects create without a sku as problem details', function () {
    $response = $this->postJson('/api/v1/products', [
        'title' => 'Wool coat',
    ], [
        'Idempotency-Key' => '01900000-0000-7000-8000-00000000key1',
    ]);

    $response->assertStatus(422)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', 'https://ecommercesolution.test/problems/validation');
});

it('creates a draft listing and returns the JSON React expects', function () {
    $response = createListing();

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Wool coat')
        ->assertJsonPath('data.sku', 'WOOL-COAT')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.visibility', 'visible')
        ->assertJsonPath('data.featured', false)
        ->assertJsonPath('data.short_description', null)
        ->assertJsonPath('data.brand', null);

    $id = $response->json('data.id');
    expect($id)->toBeString()->not->toBeEmpty();
    $response->assertHeader('Location', '/api/v1/products/'.$id);
});

it('creates with catalog visibility and featured', function () {
    $response = createListing([
        'title' => 'Linen shirt',
        'sku' => 'LINEN-SHIRT',
        'visibility' => 'catalog',
        'featured' => true,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.visibility', 'catalog')
        ->assertJsonPath('data.featured', true);
});

it('creates with listing copy, brand, and sku codes', function () {
    $response = createListing([
        'short_description' => 'Warm wool.',
        'description' => 'A long coat for winter.',
        'brand' => 'North Mill',
        'barcode' => '0123456789012',
        'gtin' => '0123456789012',
        'mpn' => 'NM-WOOL-1',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.short_description', 'Warm wool.')
        ->assertJsonPath('data.description', 'A long coat for winter.')
        ->assertJsonPath('data.brand', 'North Mill')
        ->assertJsonPath('data.sku', 'WOOL-COAT')
        ->assertJsonPath('data.barcode', '0123456789012')
        ->assertJsonPath('data.gtin', '0123456789012')
        ->assertJsonPath('data.mpn', 'NM-WOOL-1');
});

it('creates with pricing and tax settings', function () {
    createListing([
        'sku' => 'WOOL-COAT-PRICE',
        'price_minor' => 12999,
        'compare_at_minor' => 15999,
        'cost_minor' => 5000,
        'currency' => 'USD',
        'sale_starts_at' => '2026-10-01T00:00:00+00:00',
        'sale_ends_at' => '2026-10-31T23:59:59+00:00',
        'tax_status' => 'taxable',
        'tax_class' => 'standard',
    ])->assertCreated()
        ->assertJsonPath('data.price_minor', 12999)
        ->assertJsonPath('data.compare_at_minor', 15999)
        ->assertJsonPath('data.cost_minor', 5000)
        ->assertJsonPath('data.currency', 'USD')
        ->assertJsonPath('data.tax_status', 'taxable')
        ->assertJsonPath('data.tax_class', 'standard');
});

it('creates catalog shipping data and variant overrides', function () {
    createListing([
        'sku' => 'WOOL-COAT-SHIPPING',
        'weight_g' => 1800,
        'length_mm' => 800,
        'width_mm' => 600,
        'height_mm' => 120,
        'variant_weight_g' => 1900,
        'variant_length_mm' => 820,
        'variant_width_mm' => 620,
        'variant_height_mm' => 130,
        'shipping_class' => 'bulky',
    ])->assertCreated()
        ->assertJsonPath('data.weight_g', 1800)
        ->assertJsonPath('data.length_mm', 800)
        ->assertJsonPath('data.width_mm', 600)
        ->assertJsonPath('data.height_mm', 120)
        ->assertJsonPath('data.variant_weight_g', 1900)
        ->assertJsonPath('data.variant_length_mm', 820)
        ->assertJsonPath('data.variant_width_mm', 620)
        ->assertJsonPath('data.variant_height_mm', 130)
        ->assertJsonPath('data.shipping_class', 'bulky');
});

it('updates shipping data without clearing omitted measurements', function () {
    $id = createListing([
        'sku' => 'WOOL-COAT-SHIPPING-UPDATE',
        'weight_g' => 1800,
        'length_mm' => 800,
        'width_mm' => 600,
        'height_mm' => 120,
        'shipping_class' => 'standard',
    ])->json('data.id');

    $this->patchJson('/api/v1/products/'.$id, [
        'weight_g' => 2000,
        'shipping_class' => 'bulky',
    ])->assertOk()
        ->assertJsonPath('data.weight_g', 2000)
        ->assertJsonPath('data.length_mm', 800)
        ->assertJsonPath('data.width_mm', 600)
        ->assertJsonPath('data.height_mm', 120)
        ->assertJsonPath('data.shipping_class', 'bulky');
});

it('rejects negative shipping measurements', function () {
    createListing([
        'sku' => 'WOOL-COAT-BAD-SHIPPING',
        'weight_g' => -1,
    ])->assertStatus(422)
        ->assertJsonPath('type', 'https://ecommercesolution.test/problems/validation');
});

it('updates pricing and tax settings', function () {
    $id = createListing(['sku' => 'WOOL-COAT-UPDATE'])->json('data.id');

    $this->patchJson('/api/v1/products/'.$id, [
        'price_minor' => 9999,
        'cost_minor' => 4000,
        'tax_status' => 'none',
    ])->assertOk()
        ->assertJsonPath('data.price_minor', 9999)
        ->assertJsonPath('data.cost_minor', 4000)
        ->assertJsonPath('data.tax_status', 'none')
        ->assertJsonPath('data.tax_class', null);
});

it('creates an external product with sold individually enabled', function () {
    createListing([
        'sku' => 'WOOL-COAT-EXTERNAL',
        'type' => 'external',
        'sold_individually' => true,
        'external_url' => 'https://merchant.example/wool-coat',
    ])->assertCreated()
        ->assertJsonPath('data.type', 'external')
        ->assertJsonPath('data.sold_individually', true)
        ->assertJsonPath('data.external_url', 'https://merchant.example/wool-coat');
});

it('updates product type and sold individually setting', function () {
    $id = createListing(['sku' => 'WOOL-COAT-TYPE'])->json('data.id');

    $this->patchJson('/api/v1/products/'.$id, [
        'type' => 'downloadable',
        'sold_individually' => true,
    ])->assertOk()
        ->assertJsonPath('data.type', 'downloadable')
        ->assertJsonPath('data.sold_individually', true);
});

it('creates a product with SEO fields', function () {
    createListing([
        'sku' => 'WOOL-COAT-SEO',
        'slug' => 'winter-wool-coat',
        'meta_title' => 'Winter Wool Coat',
        'meta_description' => 'A warm coat for winter.',
    ])->assertCreated()
        ->assertJsonPath('data.slug', 'winter-wool-coat')
        ->assertJsonPath('data.meta_title', 'Winter Wool Coat')
        ->assertJsonPath('data.meta_description', 'A warm coat for winter.');
});

it('updates a slug and SEO metadata', function () {
    $id = createListing(['sku' => 'WOOL-COAT-SEO-UPDATE'])->json('data.id');

    $this->patchJson('/api/v1/products/'.$id, [
        'slug' => 'new-wool-coat',
        'meta_title' => 'New Wool Coat',
        'meta_description' => 'Updated description.',
    ])->assertOk()
        ->assertJsonPath('data.slug', 'new-wool-coat')
        ->assertJsonPath('data.meta_title', 'New Wool Coat')
        ->assertJsonPath('data.meta_description', 'Updated description.');

    $this->assertDatabaseHas('url_redirects', [
        'from_path' => '/products/wool-coat',
        'to_path' => '/products/new-wool-coat',
    ], 'product');
});

it('updates listing flags without changing status', function () {
    $id = createListing()->json('data.id');

    $this->patchJson('/api/v1/products/'.$id, [
        'visibility' => 'search',
        'featured' => true,
    ])->assertOk()
        ->assertJsonPath('data.id', $id)
        ->assertJsonPath('data.visibility', 'search')
        ->assertJsonPath('data.featured', true)
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.sku', 'WOOL-COAT');
});

it('updates listing copy and sku codes', function () {
    $id = createListing()->json('data.id');

    $this->patchJson('/api/v1/products/'.$id, [
        'title' => 'Wool coat updated',
        'short_description' => 'Short',
        'brand' => 'North Mill',
        'sku' => 'WOOL-COAT-2',
        'barcode' => '999',
    ])->assertOk()
        ->assertJsonPath('data.title', 'Wool coat updated')
        ->assertJsonPath('data.short_description', 'Short')
        ->assertJsonPath('data.brand', 'North Mill')
        ->assertJsonPath('data.sku', 'WOOL-COAT-2')
        ->assertJsonPath('data.barcode', '999')
        ->assertJsonPath('data.status', 'draft');
});

it('publishes a draft', function () {
    $id = createListing()->json('data.id');

    $this->postJson('/api/v1/products/'.$id.'/publish', [], [
        'Idempotency-Key' => '01900000-0000-7000-8000-00000000key2',
    ])->assertOk()
        ->assertJsonPath('data.status', 'active');
});

it('archives a listing', function () {
    $id = createListing()->json('data.id');

    $this->postJson('/api/v1/products/'.$id.'/archive', [], [
        'Idempotency-Key' => '01900000-0000-7000-8000-00000000key3',
    ])->assertOk()
        ->assertJsonPath('data.status', 'archived');
});

it('returns an archived listing to draft', function () {
    $id = createListing()->json('data.id');

    $this->postJson('/api/v1/products/'.$id.'/archive', [], [
        'Idempotency-Key' => '01900000-0000-7000-8000-00000000key4',
    ]);

    $this->postJson('/api/v1/products/'.$id.'/draft', [], [
        'Idempotency-Key' => '01900000-0000-7000-8000-00000000key5',
    ])->assertOk()
        ->assertJsonPath('data.status', 'draft');
});
