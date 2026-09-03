<?php

namespace Modules\Product\Contracts;

/**
 * Questions other modules may ask about a listing. The only catalog door
 * besides events. Never Eloquent, never product.* SQL from the caller.
 *
 * isActive means the listing is published (status active) and therefore
 * sellable at the catalog level. Unknown id is not active. Stock is not
 * this module — Inventory asks here, then checks its own tables.
 *
 * Prefer areActive for many ids so the caller never loops (one catalog read).
 */
interface ProductApi
{
    public function isActive(string $id): bool;

    /**
     * @param  list<string>  $ids
     * @return array<string, bool> keyed by the ids that were asked
     */
    public function areActive(array $ids): array;
}
