<?php
namespace Modules\Product\Application\Media;
final readonly class AddDigitalFileCommand
{
    public function __construct(public string $id, public string $productId, public string $path, public ?string $variantId, public ?int $downloadLimit, public ?int $expiresAfterDays) {}
}