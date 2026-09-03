<?php

namespace Modules\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent row for product.products. Never leave Persistence/.
 *
 * Maps to the Domain Product aggregate through EloquentProductRepository.
 * Connection is `product` (schema product). Feature 1: title, status,
 * visibility, featured. Feature 2: short_description, description, brand
 * (text), default_variant_id. type, slug, and tax_status are stored with
 * catalog defaults until those features own them.
 *
 * @property string $id
 * @property string $title
 * @property string|null $short_description
 * @property string|null $description
 * @property string|null $brand
 * @property string $status
 * @property string $visibility
 * @property bool $featured
 * @property bool $sold_individually
 * @property string|null $default_variant_id
 * @property string $type
 * @property string $slug
 * @property string|null $external_url
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string $tax_status
 * @property string|null $tax_class
 * @property int|null $weight_g
 * @property int|null $length_mm
 * @property int|null $width_mm
 * @property int|null $height_mm
 * @property string|null $shipping_class
 */
final class ProductModel extends Model
{
    protected $connection = 'product';

    protected $table = 'products';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'short_description',
        'description',
        'brand',
        'status',
        'visibility',
        'featured',
        'sold_individually',
        'default_variant_id',
        'type',
        'slug',
        'external_url',
        'meta_title',
        'meta_description',
        'tax_status',
        'tax_class',
        'weight_g',
        'length_mm',
        'width_mm',
        'height_mm',
        'shipping_class',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'sold_individually' => 'boolean',
        ];
    }
}
