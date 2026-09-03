<?php
namespace Modules\Product\Api\Controllers;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Modules\Product\Application\Ports\OperationsRepository;
/** GET /api/v1/admin/products/export — exports the admin catalog CSV. */
final class ExportProductsController { public function __construct(private OperationsRepository $operations) {} public function __invoke(Request $request): Response { $page = $this->operations->page(null, 100); $stream = fopen('php://temp', 'r+'); fputcsv($stream, ['id', 'title', 'sku', 'slug', 'status', 'visibility', 'featured']); foreach ($page['items'] as $product) fputcsv($stream, [$product->id(), $product->title(), $product->defaultVariant()->sku(), $product->slug(), strtolower($product->status()->name), strtolower($product->visibility()->name), $product->isFeatured() ? '1' : '0']); rewind($stream); $csv = stream_get_contents($stream); fclose($stream); return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="products.csv"']); } }