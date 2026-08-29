<?php

namespace Modules\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Infrastructure\Persistence\Factories\ProductFactory;

/**
 * Eloquent model — Persistence only. Never leak outside Infrastructure.
 *
 * @property string $id
 * @property string $title
 * @property string $slug
 * @property string $status
 * @property string $description
 * @property int $price_minor
 * @property string $currency
 */
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $connection = 'product';

    protected $table = 'products';

    protected $fillable = [
        'id',
        'title',
        'slug',
        'status',
        'description',
        'price_minor',
        'currency',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
        ];
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
