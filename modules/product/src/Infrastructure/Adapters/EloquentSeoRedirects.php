<?php

namespace Modules\Product\Infrastructure\Adapters;

use Illuminate\Support\Facades\DB;
use Modules\Product\Application\Ports\SeoRedirects;
use Shared\Ids;

/**
 * Stores Product SEO slug redirects in product.url_redirects.
 */
final class EloquentSeoRedirects implements SeoRedirects
{
    public function __construct(private Ids $ids)
    {
    }

    public function record(string $fromPath, string $toPath): void
    {
        DB::connection('product')->table('url_redirects')->insertOrIgnore([
            'id' => $this->ids->uuid7(),
            'from_path' => $fromPath,
            'to_path' => $toPath,
            'created_at' => now(),
        ]);
    }
}