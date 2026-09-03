<?php

use Modules\Product\Application\CreateProduct\CreateProductCommand;
use Modules\Product\Application\CreateProduct\CreateProductHandler;
use Modules\Product\Application\UpdateProduct\UpdateProductCommand;
use Modules\Product\Application\UpdateProduct\UpdateProductHandler;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Domain\ProductTaxStatus;
use Modules\Product\Domain\Product;
use Modules\Product\Domain\ProductVariant;
use Shared\Clock;
use Shared\Ids;

final class PricingInMemoryProductRepository implements ProductRepository
{
    /** @var array<string, Product> */
    private array $items = [];

    public function save(Product $product): void
    {
        $this->items[$product->id()] = $product;
    }

    public function find(string $id): ?Product
    {
        return $this->items[$id] ?? null;
    }

    public function findMany(array $ids): array
    {
        return array_filter($this->items, fn (Product $product): bool => in_array($product->id(), $ids, true));
    }

    public function skuTaken(string $sku, ?string $exceptVariantId = null): bool
    {
        foreach ($this->items as $product) {
            $variant = $product->defaultVariant();

            if ($variant->sku() === $sku && $variant->id() !== $exceptVariantId) {
                return true;
            }
        }

        return false;
    }

    public function slugTaken(string $slug, ?string $exceptProductId = null): bool
    {
        return false;
    }
}

final class PricingInMemoryOutbox implements Outbox
{
    /** @var list<object> */
    public array $events = [];

    public function record(object $event): void
    {
        $this->events[] = $event;
    }
}

final class PricingImmediateTransaction implements Transaction
{
    public function run(callable $work): mixed
    {
        return $work();
    }
}

final class PricingFixedIds implements Ids
{
    public function uuid7(): string
    {
        return '01900000-0000-7000-8000-0000000000e3';
    }
}

final class PricingFixedClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-09-01T12:00:00+00:00');
    }
}

function pricingWorld(): object
{
    $products = new PricingInMemoryProductRepository();
    $outbox = new PricingInMemoryOutbox();
    $transaction = new PricingImmediateTransaction();
    $ids = new PricingFixedIds();
    $clock = new PricingFixedClock();

    return (object) compact('products', 'outbox', 'transaction', 'ids', 'clock');
}

function seedPricingDraft(PricingInMemoryProductRepository $products): Product
{
    $product = Product::create(
        id: '01900000-0000-7000-8000-000000000001',
        title: 'Wool coat',
        variant: ProductVariant::create(
            id: '01900000-0000-7000-8000-0000000000a1',
            sku: 'WOOL-COAT',
        ),
    );
    $products->save($product);

    return $product;
}

it('creates a product with variant pricing and tax settings', function () {
    $world = pricingWorld();
    $handler = new CreateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new CreateProductCommand(
        id: '01900000-0000-7000-8000-000000000003',
        title: 'Wool coat',
        sku: 'WOOL-COAT-PRICE',
        priceMinor: 12999,
        currency: 'USD',
        compareAtMinor: 15999,
        costMinor: 5000,
        saleStartsAt: new DateTimeImmutable('2026-10-01T00:00:00+00:00'),
        saleEndsAt: new DateTimeImmutable('2026-10-31T23:59:59+00:00'),
        taxStatus: ProductTaxStatus::Taxable,
        taxClass: 'standard',
    ));

    $product = $world->products->find('01900000-0000-7000-8000-000000000003');

    expect($product->defaultVariant()->priceMinor())->toBe(12999)
        ->and($product->defaultVariant()->currency())->toBe('USD')
        ->and($product->taxStatus())->toBe(ProductTaxStatus::Taxable)
        ->and($product->taxClass())->toBe('standard')
        ->and($world->outbox->events)->toHaveCount(1);
});

it('updates variant pricing and tax settings', function () {
    $world = pricingWorld();
    $product = seedPricingDraft($world->products);
    $handler = new UpdateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new UpdateProductCommand(
        id: $product->id(),
        priceMinor: 9999,
        compareAtMinor: null,
        costMinor: 4000,
        saleStartsAt: null,
        saleEndsAt: null,
        taxStatus: ProductTaxStatus::None,
        taxClass: null,
    ));

    $saved = $world->products->find($product->id());

    expect($saved->defaultVariant()->priceMinor())->toBe(9999)
        ->and($saved->defaultVariant()->compareAtMinor())->toBeNull()
        ->and($saved->taxStatus())->toBe(ProductTaxStatus::None)
        ->and($saved->taxClass())->toBeNull()
        ->and($world->outbox->events)->toHaveCount(1);
});