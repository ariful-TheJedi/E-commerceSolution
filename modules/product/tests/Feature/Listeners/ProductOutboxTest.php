<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Contracts\Events\ProductUpdated;
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

    Schema::connection('product')->create('outbox', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('type');
        $table->text('payload');
        $table->string('occurred_at');
        $table->string('published_at')->nullable();
    });
});

it('writes ProductUpdated to the outbox in the same transaction as create', function () {
    $this->postJson('/api/v1/products', [
        'title' => 'Wool coat',
        'sku' => 'WOOL-COAT',
    ], [
        'Idempotency-Key' => '01900000-0000-7000-8000-00000000key1',
    ])->assertCreated();

    $this->assertDatabaseHas('products', [
        'title' => 'Wool coat',
        'status' => 'draft',
    ], 'product');

    $this->assertDatabaseHas('outbox', [
        'type' => ProductUpdated::class,
    ], 'product');

    expect(DB::connection('product')->table('outbox')->count())->toBe(1);
});

it('ignores the same outbox event id on a second write', function () {
    $outbox = app(Outbox::class);
    $event = new ProductUpdated(
        eventId: '01900000-0000-7000-8000-0000000000e1',
        productId: '01900000-0000-7000-8000-000000000001',
        occurredAt: new DateTimeImmutable('2026-09-02T00:00:00+00:00'),
    );

    $outbox->record($event);
    $outbox->record($event);

    expect(DB::connection('product')->table('outbox')->count())->toBe(1);

    $this->assertDatabaseHas('outbox', [
        'id' => '01900000-0000-7000-8000-0000000000e1',
        'type' => ProductUpdated::class,
    ], 'product');
});
