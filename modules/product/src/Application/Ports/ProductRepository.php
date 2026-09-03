<?php

namespace Modules\Product\Application\Ports;

use Modules\Product\Domain\Product;

/**
 * Persistence for the Product aggregate (listing + default variant).
 *
 * Application talks to this port only. Eloquent lives in Infrastructure.
 * save() is insert or update. find() returns null when the id is unknown.
 * findMany() is the batch read ProductApi uses — never query inside a loop.
 * skuTaken() is uniqueness across product_variants, not a FormRequest rule.
 */
interface ProductRepository
{
    public function save(Product $product): void;

    public function find(string $id): ?Product;

    /**
     * @param  list<string>  $ids
     * @return array<string, Product> keyed by id; missing ids omitted
     */
    public function findMany(array $ids): array;

    public function skuTaken(string $sku, ?string $exceptVariantId = null): bool;

    public function slugTaken(string $slug, ?string $exceptProductId = null): bool;
}
