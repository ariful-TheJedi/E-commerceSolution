<?php

namespace Modules\Product\Application\Media;

use Modules\Product\Application\Exceptions\ProductValidationException;
use Modules\Product\Application\Ports\MediaRepository;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Contracts\Events\ProductUpdated;
use Modules\Product\Domain\DigitalFile;
use Modules\Product\Domain\ProductMedia;
use Shared\Clock;
use Shared\Ids;

/** Orchestrates gallery and downloadable-file catalog metadata changes. */
final class MediaHandler
{
    public function __construct(private MediaRepository $media, private Outbox $outbox, private Transaction $transaction, private Ids $ids, private Clock $clock) {}

    public function addImage(AddImageCommand $command): void
    {
        $this->assertProductAndVariant($command->productId, $command->variantId);
        $media = new ProductMedia($command->id, $command->productId, $command->path, $command->variantId, $command->alt, $command->position, $command->isPrimary);
        $this->transaction->run(function () use ($media): void { $this->media->saveMedia($media); $this->record($media->productId); });
    }

    public function updateImage(UpdateImageCommand $command): void
    {
        if (!$this->media->mediaBelongsToProduct($command->mediaId, $command->productId)) throw ProductValidationException::withMessages(['media_id' => 'Media does not belong to the product.']);
        $this->assertProductAndVariant($command->productId, $command->variantId);
        $this->transaction->run(function () use ($command): void { $this->media->updateMedia($command->mediaId, $command->productId, $command->position, $command->isPrimary, $command->variantId, $command->alt); $this->record($command->productId); });
    }

    public function addDigitalFile(AddDigitalFileCommand $command): void
    {
        $this->assertProductAndVariant($command->productId, $command->variantId);
        $file = new DigitalFile($command->id, $command->productId, $command->path, $command->variantId, $command->downloadLimit, $command->expiresAfterDays);
        $this->transaction->run(function () use ($file): void { $this->media->saveDigitalFile($file); $this->record($file->productId); });
    }

    private function assertProductAndVariant(string $productId, ?string $variantId): void
    {
        if (!$this->media->productExists($productId)) throw ProductValidationException::withMessages(['product_id' => 'Product does not exist.']);
        if ($variantId !== null && !$this->media->variantBelongsToProduct($variantId, $productId)) throw ProductValidationException::withMessages(['variant_id' => 'Variant does not belong to the product.']);
    }

    private function record(string $productId): void
    {
        $this->outbox->record(new ProductUpdated($this->ids->uuid7(), $productId, $this->clock->now()));
    }
}