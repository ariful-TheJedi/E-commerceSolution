<?php
namespace Modules\Product\Application\Media;
final readonly class UpdateImageCommand
{
    public function __construct(public string $mediaId, public string $productId, public ?int $position, public ?bool $isPrimary, public ?string $variantId, public ?string $alt) {}
}