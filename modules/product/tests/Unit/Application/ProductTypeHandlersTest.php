<?php

use Modules\Product\Application\CreateProduct\CreateProductCommand;
use Modules\Product\Application\CreateProduct\CreateProductHandler;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Application\UpdateProduct\UpdateProductCommand;
use Modules\Product\Application\UpdateProduct\UpdateProductHandler;
use Modules\Product\Domain\Product;
use Modules\Product\Domain\ProductType;
use Modules\Product\Domain\ProductVariant;
use Shared\Clock;
use Shared\Ids;

final class TypeInMemoryProductRepository implements ProductRepository
{
    /** @var array<string, Product> */
    private array $items = [];

    public function save(Product $product): void { $this->items[$product->id()] = $product; }
    public function find(string $id): ?Product { return $this->items[$id] ?? null; }
    public function findMany(array $ids): array { return array_filter($this->items, fn (Product $product): bool => in_array($product->id(), $ids, true)); }
    public function skuTaken(string $sku, ?string $exceptVariantId = null): bool { return false; }
    public function slugTaken(string $slug, ?string $exceptProductId = null): bool { return false; }
}

final class TypeInMemoryOutbox implements Outbox
{
    /** @var list<object> */
    public array $events = [];
    public function record(object $event): void { $this->events[] = $event; }
}

final class TypeImmediateTransaction implements Transaction
{
    public function run(callable $work): mixed { return $work(); }
}

final class TypeFixedIds implements Ids
{
    public function uuid7(): string { return '01900000-0000-7000-8000-0000000000f1'; }
}

final class TypeFixedClock implements Clock
{
    public function now(): DateTimeImmutable { return new DateTimeImmutable('2026-09-03T12:00:00+00:00'); }
}

function typeWorld(): object
{
    $products = new TypeInMemoryProductRepository();
    $outbox = new TypeInMemoryOutbox();
    $transaction = new TypeImmediateTransaction();
    $ids = new TypeFixedIds();
    $clock = new TypeFixedClock();

    return (object) compact('products', 'outbox', 'transaction', 'ids', 'clock');
}

function seedTypeProduct(TypeInMemoryProductRepository $products): Product
{
    $product = Product::create(
        id: '01900000-0000-7000-8000-000000000006',
        title: 'Wool coat',
        variant: ProductVariant::create(
            id: '01900000-0000-7000-8000-0000000000a6',
            sku: 'WOOL-COAT-6',
        ),
    );
    $products->save($product);

    return $product;
}

it('creates a typed product with sold individually and external URL settings', function () {
    $world = typeWorld();
    $handler = new CreateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new CreateProductCommand(
        id: '01900000-0000-7000-8000-000000000007',
        title: 'Wool coat',
        sku: 'WOOL-COAT-7',
        type: ProductType::External,
        soldIndividually: true,
        externalUrl: 'https://merchant.example/wool-coat',
    ));

    $product = $world->products->find('01900000-0000-7000-8000-000000000007');

    expect($product->type())->toBe(ProductType::External)
        ->and($product->isSoldIndividually())->toBeTrue()
        ->and($product->externalUrl())->toBe('https://merchant.example/wool-coat')
        ->and($world->outbox->events)->toHaveCount(1);
});

it('updates a product type and sold individually setting', function () {
    $world = typeWorld();
    $product = seedTypeProduct($world->products);
    $handler = new UpdateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new UpdateProductCommand(
        id: $product->id(),
        type: ProductType::Downloadable,
        soldIndividually: true,
    ));

    $saved = $world->products->find($product->id());

    expect($saved->type())->toBe(ProductType::Downloadable)
        ->and($saved->isSoldIndividually())->toBeTrue()
        ->and($world->outbox->events)->toHaveCount(1);
});