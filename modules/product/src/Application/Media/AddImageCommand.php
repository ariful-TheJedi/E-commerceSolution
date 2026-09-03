<?php
namespace Modules\Product\Application\Media;
final readonly class AddImageCommand
{
    public function __construct(public string $id, public string $productId, public string $path, public ?string $variantId, public ?string $alt, public int $position, public bool $isPrimary) {}
}