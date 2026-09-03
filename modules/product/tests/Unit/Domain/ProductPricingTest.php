<?php

use Modules\Product\Domain\Exceptions\InvalidPrice;
use Modules\Product\Domain\Exceptions\InvalidSaleWindow;
use Modules\Product\Domain\Exceptions\InvalidCurrency;
use Modules\Product\Domain\ProductVariant;

function aPricedVariant(array $overrides = []): ProductVariant
{
    return ProductVariant::create(
        id: $overrides['id'] ?? '01900000-0000-7000-8000-0000000000b1',
        sku: $overrides['sku'] ?? 'WOOL-COAT',
        priceMinor: $overrides['priceMinor'] ?? 12999,
        currency: $overrides['currency'] ?? 'USD',
        compareAtMinor: array_key_exists('compareAtMinor', $overrides) ? $overrides['compareAtMinor'] : 15999,
        costMinor: array_key_exists('costMinor', $overrides) ? $overrides['costMinor'] : 5000,
        saleStartsAt: array_key_exists('saleStartsAt', $overrides) ? $overrides['saleStartsAt'] : new DateTimeImmutable('2026-10-01T00:00:00+00:00'),
        saleEndsAt: array_key_exists('saleEndsAt', $overrides) ? $overrides['saleEndsAt'] : new DateTimeImmutable('2026-10-31T23:59:59+00:00'),
    );
}

it('creates a variant with base, compare-at, cost, currency, and sale window', function () {
    $variant = aPricedVariant();

    expect($variant->priceMinor())->toBe(12999)
        ->and($variant->compareAtMinor())->toBe(15999)
        ->and($variant->costMinor())->toBe(5000)
        ->and($variant->currency())->toBe('USD')
        ->and($variant->saleStartsAt())->toEqual(new DateTimeImmutable('2026-10-01T00:00:00+00:00'))
        ->and($variant->saleEndsAt())->toEqual(new DateTimeImmutable('2026-10-31T23:59:59+00:00'));
});

it('allows optional compare-at, cost, and sale window values', function () {
    $variant = aPricedVariant([
        'compareAtMinor' => null,
        'costMinor' => null,
        'saleStartsAt' => null,
        'saleEndsAt' => null,
    ]);

    expect($variant->compareAtMinor())->toBeNull()
        ->and($variant->costMinor())->toBeNull()
        ->and($variant->saleStartsAt())->toBeNull()
        ->and($variant->saleEndsAt())->toBeNull();
});

it('rejects negative money values', function (string $field) {
    aPricedVariant([$field => -1]);
})->with(['priceMinor', 'compareAtMinor', 'costMinor'])
    ->throws(InvalidPrice::class);

it('rejects a currency that is not a three-letter uppercase code', function (string $currency) {
    aPricedVariant(['currency' => $currency]);
})->with(['US', 'usd', 'USDX'])
    ->throws(InvalidCurrency::class);

it('requires both sale dates when a sale window is used', function () {
    aPricedVariant(['saleStartsAt' => null]);
})->throws(InvalidSaleWindow::class);

it('requires the sale to end after it starts', function () {
    aPricedVariant([
        'saleStartsAt' => new DateTimeImmutable('2026-10-31T23:59:59+00:00'),
        'saleEndsAt' => new DateTimeImmutable('2026-10-01T00:00:00+00:00'),
    ]);
})->throws(InvalidSaleWindow::class);

it('updates pricing on an existing variant', function () {
    $variant = aPricedVariant();

    $variant->changePricing(
        priceMinor: 9999,
        compareAtMinor: null,
        costMinor: 4000,
        saleStartsAt: null,
        saleEndsAt: null,
    );

    expect($variant->priceMinor())->toBe(9999)
        ->and($variant->compareAtMinor())->toBeNull()
        ->and($variant->costMinor())->toBe(4000)
        ->and($variant->saleStartsAt())->toBeNull()
        ->and($variant->saleEndsAt())->toBeNull();
});