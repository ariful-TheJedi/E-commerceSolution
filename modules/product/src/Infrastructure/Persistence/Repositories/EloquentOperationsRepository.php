<?php
namespace Modules\Product\Infrastructure\Persistence\Repositories;
use Illuminate\Support\Facades\DB;
use Modules\Product\Application\Ports\OperationsRepository;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Domain\Product;
/** Implements bounded, cursor-based admin catalog operations. */
final class EloquentOperationsRepository implements OperationsRepository
{
    public function __construct(private ProductRepository $products) {}
    public function page(?string $cursor, int $limit): array
    {
        $query = DB::connection('product')->table('products')->orderBy('id')->limit($limit + 1);
        if ($cursor !== null) $query->where('id', '>', base64_decode($cursor, true) ?: '');
        $ids = $query->pluck('id')->all(); $next = count($ids) > $limit ? base64_encode((string) $ids[$limit - 1]) : null;
        $ids = array_slice($ids, 0, $limit); $found = $this->products->findMany($ids); $items = array_values(array_filter(array_map(fn ($id) => $found[$id] ?? null, $ids)));
        return ['items' => $items, 'next_cursor' => $next];
    }
    public function bulkUpdate(array $ids, ?string $visibility, ?bool $featured): void
    {
        $changes = array_filter(['visibility' => $visibility, 'featured' => $featured], static fn ($value): bool => $value !== null);
        if ($changes !== []) DB::connection('product')->table('products')->whereIn('id', $ids)->update([...$changes, 'updated_at' => now()]);
    }
}