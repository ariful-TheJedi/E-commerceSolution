<?php

namespace Modules\Product\Contracts;

use Modules\Product\Contracts\Dto\ProductSummaryDto;

/**
 * Published surface for other modules. Returns DTOs only — never Eloquent.
 */
interface ProductApi
{
    public function isActive(string $id): bool;

    /**
     * Active (storefront-visible) product summaries.
     * Dummy impl returns static samples until F1/persistence exists.
     *
     * @return list<ProductSummaryDto>
     */
    public function listActiveSummaries(): array;
}
