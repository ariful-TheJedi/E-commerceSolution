<?php

namespace Modules\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/** Eloquent row for product.attribute_options; swatch metadata only. */
final class AttributeOptionModel extends Model
{
    protected $connection = 'product';
    protected $table = 'attribute_options';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'attribute_id', 'label', 'slug', 'position', 'color_hex', 'image_path'];
}
