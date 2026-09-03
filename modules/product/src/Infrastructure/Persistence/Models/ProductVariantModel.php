<?php

namespace Modules\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent row for product.product_variants. Never leave Persistence/.
 *
 * Maps to Domain ProductVariant through EloquentProductRepository.
 * Feature 2 columns: sku, barcode, gtin, mpn, is_default.
 * price_minor and currency are stored as placeholders until feature 3.
 *
 * @property string $id
 * @property string $product_id
 * @property string $sku
 * @property string|null $barcode
 * @property string|null $gtin
 * @property string|null $mpn
 * @property bool $is_default
 * @property int $price_minor
 * @property int|null $compare_at_minor
 * @property int|null $cost_minor
 * @property string $currency
 * @property string|null $sale_starts_at
 * @property string|null $sale_ends_at
 * @property int|null $weight_g
 * @property int|null $length_mm
 * @property int|null $width_mm
 * @property int|null $height_mm
 */
final class ProductVariantModel extends Model
{
    protected $connection = 'product';

    protected $table = 'product_variants';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'product_id',
        'sku',
        'barcode',
        'gtin',
        'mpn',
        'is_default',
        'price_minor',
        'currency',
        'compare_at_minor',
        'cost_minor',
        'sale_starts_at',
        'sale_ends_at',
        'weight_g',
        'length_mm',
        'width_mm',
        'height_mm',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'price_minor' => 'integer',
            'compare_at_minor' => 'integer',
            'cost_minor' => 'integer',
            'weight_g' => 'integer',
            'length_mm' => 'integer',
            'width_mm' => 'integer',
            'height_mm' => 'integer',
        ];
    }
}
