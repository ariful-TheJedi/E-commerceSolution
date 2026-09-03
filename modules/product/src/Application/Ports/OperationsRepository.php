<?php
namespace Modules\Product\Application\Ports;
use Modules\Product\Domain\Product;
/** Provides admin-oriented product queries and bulk catalog mutations. */
interface OperationsRepository
{
    /** @return array{items:list<Product>,next_cursor:?string} */
    public function page(?string $cursor, int $limit): array;
    /** @param list<string> $ids */
    public function bulkUpdate(array $ids, ?string $visibility, ?bool $featured): void;
}