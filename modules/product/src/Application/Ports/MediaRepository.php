<?php

namespace Modules\Product\Application\Ports;

use Modules\Product\Domain\DigitalFile;
use Modules\Product\Domain\ProductMedia;

/** Persists product gallery metadata and validates product-owned variants. */
interface MediaRepository
{
    public function productExists(string $productId): bool;
    public function variantBelongsToProduct(string $variantId, string $productId): bool;
    public function mediaBelongsToProduct(string $mediaId, string $productId): bool;
    public function saveMedia(ProductMedia $media): void;
    public function updateMedia(string $mediaId, string $productId, ?int $position, ?bool $isPrimary, ?string $variantId, ?string $alt): void;
    public function saveDigitalFile(DigitalFile $file): void;
}