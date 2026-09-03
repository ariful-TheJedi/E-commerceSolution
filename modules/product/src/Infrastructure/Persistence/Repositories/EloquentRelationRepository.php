<?php

namespace Modules\Product\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Product\Application\Ports\RelationRepository;
use Modules\Product\Domain\ProductRelation;

/** Maps manual merchandising links to product.product_relations. */
final class EloquentRelationRepository implements RelationRepository
{
    public function productExists(string $productId): bool
    {
        return DB::connection('product')->table('products')->where('id', $productId)->exists();
    }

    public function save(ProductRelation $relation): void
    {
        DB::connection('product')->table('product_relations')->insertOrIgnore([
            'from_product_id' => $relation->fromProductId,
            'to_product_id' => $relation->toProductId,
            'kind' => $relation->kind,
        ]);
    }
}