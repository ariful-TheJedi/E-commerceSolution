<?php

use Modules\Product\Application\ArchiveProduct\ArchiveProductCommand;
use Modules\Product\Application\ArchiveProduct\ArchiveProductHandler;
use Modules\Product\Application\CreateProduct\CreateProductCommand;
use Modules\Product\Application\CreateProduct\CreateProductHandler;
use Modules\Product\Application\DraftProduct\DraftProductCommand;
use Modules\Product\Application\DraftProduct\DraftProductHandler;
use Modules\Product\Application\Exceptions\DuplicateSku;
use Modules\Product\Application\Exceptions\ProductNotFound;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Application\Ports\SeoRedirects;
use Modules\Product\Application\PublishProduct\PublishProductCommand;
use Modules\Product\Application\PublishProduct\PublishProductHandler;
use Modules\Product\Application\QueryProductApi;
use Modules\Product\Application\UpdateProduct\UpdateProductCommand;
use Modules\Product\Application\UpdateProduct\UpdateProductHandler;
use Modules\Product\Contracts\Events\ProductUpdated;
use Modules\Product\Domain\Exceptions\CannotPublishProduct;
use Modules\Product\Domain\Exceptions\CannotUpdateArchivedProduct;
use Modules\Product\Domain\Product;
use Modules\Product\Domain\ProductStatus;
use Modules\Product\Domain\ProductVariant;
use Modules\Product\Domain\ProductVisibility;
use Shared\Clock;
use Shared\Ids;

final class InMemoryProductRepository implements ProductRepository
{
    /** @var array<string, Product> */
    private array $items = [];

    /** @var list<Product> */
    public array $saved = [];

    public function save(Product $product): void
    {
        $this->items[$product->id()] = $product;
        $this->saved[] = $product;
    }

    public function find(string $id): ?Product
    {
        return $this->items[$id] ?? null;
    }

    public function findMany(array $ids): array
    {
        $found = [];

        foreach ($ids as $id) {
            if (isset($this->items[$id])) {
                $found[$id] = $this->items[$id];
            }
        }

        return $found;
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
        foreach ($this->items as $product) {
            if ($product->slug() === $slug && $product->id() !== $exceptProductId) {
                return true;
            }
        }

        return false;
    }
}

final class InMemoryOutbox implements Outbox
{
    /** @var list<object> */
    public array $events = [];

    public function record(object $event): void
    {
        $this->events[] = $event;
    }
}

final class FixedIds implements Ids
{
    public function uuid7(): string
    {
        return '01900000-0000-7000-8000-0000000000e1';
    }
}

final class FixedClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-09-01T12:00:00+00:00');
    }
}

final class ImmediateTransaction implements Transaction
{
    public function run(callable $work): mixed
    {
        return $work();
    }
}

final class InMemorySeoRedirects implements SeoRedirects
{
    /** @var list<array{from: string, to: string}> */
    public array $items = [];

    public function record(string $fromPath, string $toPath): void
    {
        $this->items[] = ['from' => $fromPath, 'to' => $toPath];
    }
}

function productWorld(): object
{
    $products = new InMemoryProductRepository();
    $outbox = new InMemoryOutbox();
    $transaction = new ImmediateTransaction();
    $ids = new FixedIds();
    $clock = new FixedClock();
    $redirects = new InMemorySeoRedirects();

    return (object) compact('products', 'outbox', 'transaction', 'ids', 'clock', 'redirects');
}

function seedDraft(InMemoryProductRepository $products, array $overrides = []): Product
{
    $product = Product::create(
        id: $overrides['id'] ?? '01900000-0000-7000-8000-000000000001',
        title: $overrides['title'] ?? 'Wool coat',
        variant: ProductVariant::create(
            id: $overrides['variantId'] ?? '01900000-0000-7000-8000-0000000000a1',
            sku: $overrides['sku'] ?? 'WOOL-COAT',
        ),
        visibility: $overrides['visibility'] ?? ProductVisibility::Visible,
        featured: $overrides['featured'] ?? false,
    );
    $products->save($product);

    return $product;
}

function lastEvent(InMemoryOutbox $outbox): ProductUpdated
{
    expect($outbox->events)->toHaveCount(1)
        ->and($outbox->events[0])->toBeInstanceOf(ProductUpdated::class);

    $event = $outbox->events[0];
    assert($event instanceof ProductUpdated);

    return $event;
}

it('creates a draft, saves it, and records ProductUpdated', function () {
    $world = productWorld();
    $handler = new CreateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new CreateProductCommand(
        id: '01900000-0000-7000-8000-000000000001',
        title: 'Wool coat',
        sku: 'WOOL-COAT',
    ));

    $saved = $world->products->find('01900000-0000-7000-8000-000000000001');

    expect($saved)->not->toBeNull()
        ->and($saved->status())->toBe(ProductStatus::Draft)
        ->and($saved->visibility())->toBe(ProductVisibility::Visible)
        ->and($saved->isFeatured())->toBeFalse()
        ->and($saved->title())->toBe('Wool coat');

    $event = lastEvent($world->outbox);

    expect($event->productId)->toBe('01900000-0000-7000-8000-000000000001')
        ->and($event->eventId)->toBe('01900000-0000-7000-8000-0000000000e1')
        ->and($event->occurredAt)->toEqual(new DateTimeImmutable('2026-09-01T12:00:00+00:00'));
});

it('creates a product with SEO fields', function () {
    $world = productWorld();
    $handler = new CreateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new CreateProductCommand(
        id: '01900000-0000-7000-8000-000000000010',
        title: 'Wool coat',
        sku: 'WOOL-COAT-10',
        slug: 'winter-wool-coat',
        metaTitle: 'Winter Wool Coat',
        metaDescription: 'A warm wool coat.',
    ));

    $product = $world->products->find('01900000-0000-7000-8000-000000000010');

    expect($product->slug())->toBe('winter-wool-coat')
        ->and($product->metaTitle())->toBe('Winter Wool Coat')
        ->and($product->metaDescription())->toBe('A warm wool coat.');
});

it('updates SEO fields and records a redirect for the old slug', function () {
    $world = productWorld();
    $product = seedDraft($world->products);
    $handler = new UpdateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock, $world->redirects);

    $handler->handle(new UpdateProductCommand(
        id: $product->id(),
        slug: 'new-wool-coat',
        metaTitle: 'New Wool Coat',
        metaDescription: 'Updated description.',
    ));

    expect($world->products->find($product->id())->slug())->toBe('new-wool-coat')
        ->and($world->redirects->items)->toBe([
            ['from' => '/products/wool-coat', 'to' => '/products/new-wool-coat'],
        ]);
});

it('creates with catalog visibility and featured', function () {
    $world = productWorld();
    $handler = new CreateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new CreateProductCommand(
        id: '01900000-0000-7000-8000-000000000002',
        title: 'Linen shirt',
        sku: 'LINEN-SHIRT',
        visibility: ProductVisibility::Catalog,
        featured: true,
    ));

    $saved = $world->products->find('01900000-0000-7000-8000-000000000002');

    expect($saved->visibility())->toBe(ProductVisibility::Catalog)
        ->and($saved->isFeatured())->toBeTrue();
});

it('publishes a draft, saves it, and records ProductUpdated', function () {
    $world = productWorld();
    $product = seedDraft($world->products);
    $handler = new PublishProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new PublishProductCommand($product->id()));

    expect($world->products->find($product->id())->status())->toBe(ProductStatus::Active);

    $event = lastEvent($world->outbox);

    expect($event->productId)->toBe($product->id());
});

it('does not publish when the product is missing', function () {
    $world = productWorld();
    $handler = new PublishProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new PublishProductCommand('01900000-0000-7000-8000-000000000099'));
})->throws(ProductNotFound::class);

it('does not save or record when publish is illegal', function () {
    $world = productWorld();
    $product = seedDraft($world->products);
    $product->publish();
    $savedBefore = count($world->products->saved);
    $handler = new PublishProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    expect(fn () => $handler->handle(new PublishProductCommand($product->id())))
        ->toThrow(CannotPublishProduct::class)
        ->and($world->products->saved)->toHaveCount($savedBefore)
        ->and($world->outbox->events)->toBeEmpty();
});

it('archives a product, saves it, and records ProductUpdated', function () {
    $world = productWorld();
    $product = seedDraft($world->products);
    $handler = new ArchiveProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new ArchiveProductCommand($product->id()));

    expect($world->products->find($product->id())->status())->toBe(ProductStatus::Archived)
        ->and(lastEvent($world->outbox)->productId)->toBe($product->id());
});

it('does not archive when the product is missing', function () {
    $world = productWorld();
    $handler = new ArchiveProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new ArchiveProductCommand('01900000-0000-7000-8000-000000000099'));
})->throws(ProductNotFound::class);

it('returns a product to draft, saves it, and records ProductUpdated', function () {
    $world = productWorld();
    $product = seedDraft($world->products);
    $product->publish();
    $handler = new DraftProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new DraftProductCommand($product->id()));

    expect($world->products->find($product->id())->status())->toBe(ProductStatus::Draft)
        ->and(lastEvent($world->outbox)->productId)->toBe($product->id());
});

it('updates listing flags, saves, and records ProductUpdated', function () {
    $world = productWorld();
    $product = seedDraft($world->products);
    $handler = new UpdateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new UpdateProductCommand(
        id: $product->id(),
        visibility: ProductVisibility::Search,
        featured: true,
    ));

    $saved = $world->products->find($product->id());

    expect($saved->visibility())->toBe(ProductVisibility::Search)
        ->and($saved->isFeatured())->toBeTrue()
        ->and($saved->status())->toBe(ProductStatus::Draft)
        ->and(lastEvent($world->outbox)->productId)->toBe($product->id());
});

it('does not save or record when listing update is illegal', function () {
    $world = productWorld();
    $product = seedDraft($world->products);
    $product->archive();
    $savedBefore = count($world->products->saved);
    $handler = new UpdateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    expect(fn () => $handler->handle(new UpdateProductCommand(
        id: $product->id(),
        featured: true,
    )))
        ->toThrow(CannotUpdateArchivedProduct::class)
        ->and($world->products->saved)->toHaveCount($savedBefore)
        ->and($world->outbox->events)->toBeEmpty();
});

it('creates listing copy and a default variant, then records ProductUpdated', function () {
    $world = productWorld();
    $handler = new CreateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new CreateProductCommand(
        id: '01900000-0000-7000-8000-000000000010',
        title: 'Wool coat',
        sku: 'WOOL-COAT',
        shortDescription: 'Warm wool.',
        description: 'A long coat for winter.',
        brand: 'North Mill',
        barcode: '0123456789012',
        gtin: '0123456789012',
        mpn: 'NM-WOOL-1',
    ));

    $saved = $world->products->find('01900000-0000-7000-8000-000000000010');

    expect($saved->shortDescription())->toBe('Warm wool.')
        ->and($saved->description())->toBe('A long coat for winter.')
        ->and($saved->brand())->toBe('North Mill')
        ->and($saved->defaultVariant()->sku())->toBe('WOOL-COAT')
        ->and($saved->defaultVariant()->barcode())->toBe('0123456789012')
        ->and($saved->defaultVariant()->gtin())->toBe('0123456789012')
        ->and($saved->defaultVariant()->mpn())->toBe('NM-WOOL-1')
        ->and($saved->defaultVariant()->isDefault())->toBeTrue()
        ->and(lastEvent($world->outbox)->productId)->toBe('01900000-0000-7000-8000-000000000010');
});

it('does not create when the sku is already taken', function () {
    $world = productWorld();
    seedDraft($world->products, ['sku' => 'WOOL-COAT']);
    $savedBefore = count($world->products->saved);
    $handler = new CreateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    expect(fn () => $handler->handle(new CreateProductCommand(
        id: '01900000-0000-7000-8000-000000000011',
        title: 'Other coat',
        sku: 'WOOL-COAT',
    )))
        ->toThrow(DuplicateSku::class)
        ->and($world->products->saved)->toHaveCount($savedBefore)
        ->and($world->outbox->events)->toBeEmpty();
});

it('updates copy, brand, and sku codes, then records ProductUpdated', function () {
    $world = productWorld();
    $product = seedDraft($world->products);
    $handler = new UpdateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    $handler->handle(new UpdateProductCommand(
        id: $product->id(),
        title: 'Wool coat updated',
        shortDescription: 'Short',
        description: 'Long',
        brand: 'North Mill',
        sku: 'WOOL-COAT-2',
        barcode: '999',
        gtin: '111',
        mpn: 'NM-2',
    ));

    $saved = $world->products->find($product->id());

    expect($saved->title())->toBe('Wool coat updated')
        ->and($saved->shortDescription())->toBe('Short')
        ->and($saved->brand())->toBe('North Mill')
        ->and($saved->defaultVariant()->sku())->toBe('WOOL-COAT-2')
        ->and($saved->defaultVariant()->barcode())->toBe('999')
        ->and(lastEvent($world->outbox)->productId)->toBe($product->id());
});

it('does not change sku to one already taken', function () {
    $world = productWorld();
    seedDraft($world->products, [
        'id' => '01900000-0000-7000-8000-000000000001',
        'sku' => 'WOOL-COAT',
        'variantId' => '01900000-0000-7000-8000-0000000000a1',
    ]);
    $other = seedDraft($world->products, [
        'id' => '01900000-0000-7000-8000-000000000002',
        'title' => 'Linen shirt',
        'sku' => 'LINEN-SHIRT',
        'variantId' => '01900000-0000-7000-8000-0000000000a2',
    ]);
    $savedBefore = count($world->products->saved);
    $handler = new UpdateProductHandler($world->products, $world->outbox, $world->transaction, $world->ids, $world->clock);

    expect(fn () => $handler->handle(new UpdateProductCommand(
        id: $other->id(),
        sku: 'WOOL-COAT',
    )))
        ->toThrow(DuplicateSku::class)
        ->and($world->products->saved)->toHaveCount($savedBefore)
        ->and($world->outbox->events)->toBeEmpty()
        ->and($world->products->find($other->id())->defaultVariant()->sku())->toBe('LINEN-SHIRT');
});

it('reports a published listing as active through ProductApi', function () {
    $world = productWorld();
    $product = seedDraft($world->products);
    $product->publish();
    $world->products->save($product);
    $api = new QueryProductApi($world->products);

    expect($api->isActive($product->id()))->toBeTrue();
});

it('reports draft, archived, and unknown ids as not active', function () {
    $world = productWorld();
    $draft = seedDraft($world->products);
    $archived = seedDraft($world->products, [
        'id' => '01900000-0000-7000-8000-000000000002',
        'sku' => 'OTHER',
        'variantId' => '01900000-0000-7000-8000-0000000000a2',
    ]);
    $archived->archive();
    $world->products->save($archived);
    $api = new QueryProductApi($world->products);

    expect($api->isActive($draft->id()))->toBeFalse()
        ->and($api->isActive($archived->id()))->toBeFalse()
        ->and($api->isActive('01900000-0000-7000-8000-000000000099'))->toBeFalse();
});

it('answers many ids in one catalog read', function () {
    $world = productWorld();
    $draft = seedDraft($world->products);
    $active = seedDraft($world->products, [
        'id' => '01900000-0000-7000-8000-000000000002',
        'sku' => 'LINEN-SHIRT',
        'variantId' => '01900000-0000-7000-8000-0000000000a2',
    ]);
    $active->publish();
    $world->products->save($active);
    $api = new QueryProductApi($world->products);

    expect($api->areActive([$draft->id(), $active->id()]))->toBe([
        $draft->id() => false,
        $active->id() => true,
    ]);
});
