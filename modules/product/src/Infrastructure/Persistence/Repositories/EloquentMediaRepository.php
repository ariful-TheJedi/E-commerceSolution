<?php

namespace Modules\Product\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Product\Application\Ports\MediaRepository;
use Modules\Product\Domain\DigitalFile;
use Modules\Product\Domain\ProductMedia;

/** Maps gallery and digital-file metadata to product-owned tables. */
final class EloquentMediaRepository implements MediaRepository
{
    public function productExists(string $productId): bool
    {
        return DB::connection('product')->table('products')->where('id', $productId)->exists();
    }

    public function variantBelongsToProduct(string $variantId, string $productId): bool
    {
        return DB::connection('product')->table('product_variants')->where('id', $variantId)->where('product_id', $productId)->exists();
    }

    public function mediaBelongsToProduct(string $mediaId, string $productId): bool
    {
        return DB::connection('product')->table('product_media')->where('id', $mediaId)->where('product_id', $productId)->exists();
    }

    public function saveMedia(ProductMedia $media): void
    {
        DB::connection('product')->transaction(function () use ($media): void {
            if ($media->isPrimary) {
                DB::connection('product')->table('product_media')->where('product_id', $media->productId)->update(['is_primary' => false, 'updated_at' => now()]);
            }
            DB::connection('product')->table('product_media')->insert([
                'id' => $media->id, 'product_id' => $media->productId, 'variant_id' => $media->variantId,
                'kind' => $media->kind, 'path' => $media->path, 'alt' => $media->alt,
                'position' => $media->position, 'is_primary' => $media->isPrimary,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    public function updateMedia(string $mediaId, string $productId, ?int $position, ?bool $isPrimary, ?string $variantId, ?string $alt): void
    {
        DB::connection('product')->transaction(function () use ($mediaId, $productId, $position, $isPrimary, $variantId, $alt): void {
            $currentPosition = $position === null ? null : DB::connection('product')->table('product_media')->where('id', $mediaId)->value('position');
            if ($position !== null && $currentPosition !== null && $position !== $currentPosition) {
                if ($position < $currentPosition) {
                    DB::connection('product')->table('product_media')->where('product_id', $productId)->where('id', '<>', $mediaId)->whereBetween('position', [$position, $currentPosition - 1])->increment('position');
                } else {
                    DB::connection('product')->table('product_media')->where('product_id', $productId)->where('id', '<>', $mediaId)->whereBetween('position', [$currentPosition + 1, $position])->decrement('position');
                }
            }
            $table = DB::connection('product')->table('product_media');
            if ($isPrimary === true) {
                $table->where('product_id', $productId)->update(['is_primary' => false, 'updated_at' => now()]);
            }
            $changes = array_filter([
                'position' => $position, 'is_primary' => $isPrimary, 'variant_id' => $variantId, 'alt' => $alt,
            ], static fn ($value): bool => $value !== null);
            $changes['updated_at'] = now();
            $table->where('id', $mediaId)->where('product_id', $productId)->update($changes);
        });
    }

    public function saveDigitalFile(DigitalFile $file): void
    {
        DB::connection('product')->table('digital_files')->insert([
            'id' => $file->id, 'product_id' => $file->productId, 'variant_id' => $file->variantId,
            'path' => $file->path, 'download_limit' => $file->downloadLimit,
            'expires_after_days' => $file->expiresAfterDays, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}