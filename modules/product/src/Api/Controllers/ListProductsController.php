<?php

namespace Modules\Product\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Contracts\ProductApi;

/**
 * HTTP front door only — no business rules.
 */
final class ListProductsController
{
    public function __invoke(ProductApi $products): JsonResponse
    {
        $items = array_map(
            static fn ($p): array => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'status' => $p->status,
                'description' => $p->description,
                'price_minor' => $p->priceMinor,
                'currency' => $p->currency,
            ],
            $products->listActiveSummaries(),
        );

        return response()->json([
            'data' => $items,
        ]);
    }
}
