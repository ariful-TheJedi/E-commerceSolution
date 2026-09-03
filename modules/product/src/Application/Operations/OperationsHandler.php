<?php
namespace Modules\Product\Application\Operations;
use Modules\Product\Application\Exceptions\ProductValidationException;
use Modules\Product\Application\CreateProduct\CreateProductCommand;
use Modules\Product\Application\CreateProduct\CreateProductHandler;
use Modules\Product\Application\Ports\OperationsRepository;
use Modules\Product\Application\Ports\Outbox;
use Modules\Product\Application\Ports\ProductRepository;
use Modules\Product\Application\Ports\Transaction;
use Modules\Product\Contracts\Events\ProductUpdated;
use Modules\Product\Domain\Product;
use Shared\Clock;
use Shared\Ids;
/** Orchestrates admin listing, duplication, bulk flags, and CSV import. */
final class OperationsHandler
{
    public function __construct(private OperationsRepository $operations, private ProductRepository $products, private CreateProductHandler $creator, private Outbox $outbox, private Transaction $transaction, private Ids $ids, private Clock $clock) {}
    /** @return array{items: list<Product>, next_cursor: ?string} */
    public function page(?string $cursor, int $limit): array { return $this->operations->page($cursor, min($limit, 100)); }
    public function duplicate(DuplicateProductCommand $command): string
    {
        $source = $this->products->find($command->sourceId) ?? throw ProductValidationException::withMessages(['source_id' => 'Product does not exist.']);
        if ($this->products->skuTaken($command->sku) || $this->products->slugTaken($command->slug)) throw ProductValidationException::withMessages(['identity' => 'SKU or slug is already taken.']);
        $variant = $source->defaultVariant();
        $this->creator->handle(new CreateProductCommand($command->id, $source->title().' (Copy)', $command->sku, $source->shortDescription(), $source->description(), $source->brand(), $variant->barcode(), $variant->gtin(), $variant->mpn(), $variant->priceMinor(), $variant->currency(), $variant->compareAtMinor(), $variant->costMinor(), $variant->saleStartsAt(), $variant->saleEndsAt(), $source->taxStatus(), $source->taxClass(), $source->type(), $source->isSoldIndividually(), $source->externalUrl(), $command->slug, $source->metaTitle(), $source->metaDescription(), $source->visibility(), false, $source->weightG(), $source->lengthMm(), $source->widthMm(), $source->heightMm(), $variant->weightG(), $variant->lengthMm(), $variant->widthMm(), $variant->heightMm(), $source->shippingClass()));
        return $command->id;
    }
    public function bulkEdit(BulkEditCommand $command): void
    {
        $this->transaction->run(function () use ($command): void {
            $this->operations->bulkUpdate($command->ids, $command->visibility, $command->featured);
            foreach ($command->ids as $productId) {
                $this->outbox->record(new ProductUpdated($this->ids->uuid7(), $productId, $this->clock->now()));
            }
        });
    }
    public function import(ImportProductsCommand $command): int
    {
        $rows = array_map('str_getcsv', preg_split('/\r\n|\r|\n/', trim($command->csv))); if ($rows === []) return 0;
        $header = array_map('trim', array_shift($rows)); $count = 0;
        foreach ($rows as $row) { if (count($row) < 2 || trim($row[0]) === '') continue; $data = array_combine($header, $row); $this->creator->handle(new CreateProductCommand($this->ids->uuid7(), trim($data['title']), trim($data['sku']), slug: ($data['slug'] ?? null))); $count++; }
        return $count;
    }
}