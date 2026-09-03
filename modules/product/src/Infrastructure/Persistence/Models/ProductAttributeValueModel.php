<?php

namespace Modules\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent row for product.product_attribute_values; assignment only.
 * @property string $id
 * @property string $product_id
 * @property string|null $variant_id
 * @property string $attribute_id
 * @property string|null $value_text
 * @property string|null $attribute_option_id
 */
final class ProductAttributeValueModel extends Model
{
    protected $connection = 'product';
    protected $table = 'product_attribute_values';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'product_id', 'variant_id', 'attribute_id', 'value_text', 'attribute_option_id'];
}
