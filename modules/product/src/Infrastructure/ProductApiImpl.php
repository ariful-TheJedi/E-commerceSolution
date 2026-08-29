<?php

namespace Modules\Product\Infrastructure;

use Modules\Product\Contracts\Dto\ProductSummaryDto;
use Modules\Product\Contracts\ProductApi;
use Modules\Product\Infrastructure\Persistence\Models\Product;

/**
 * Temporary stub until F1 + repository exist.
 * Builds demo rows with ProductFactory::make() (no DB write).
 */
final class ProductApiImpl implements ProductApi
{
    /** @var list<ProductSummaryDto> */
    private array $demo;

    public function __construct()
    {
        $models = [
            ...Product::factory()->active()->count(5)->make()->all(),
            ...Product::factory()->draft()->count(2)->make()->all(),
        ];

        $this->demo = array_map(
            static fn (Product $product): ProductSummaryDto => new ProductSummaryDto(
                id: (string) $product->id,
                title: (string) $product->title,
                slug: (string) $product->slug,
                status: (string) $product->status,
                description: (string) $product->description,
                priceMinor: (int) $product->price_minor,
                currency: (string) $product->currency,
            ),
            $models,
        );
    }

    public function isActive(string $id): bool
    {
        foreach ($this->demo as $product) {
            if ($product->id === $id) {
                return $product->status === 'active';
            }
        }

        return false;
    }

    public function listActiveSummaries(): array
    {
        return array_values(array_filter(
            $this->demo,
            static fn (ProductSummaryDto $p): bool => $p->status === 'active',
        ));
    }
}
