<?php

namespace Modules\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent row for reusable product.attributes; never leaves Persistence.
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $data_type
 * @property bool $filterable
 * @property bool $sortable
 * @property bool $visible_on_pdp
 */
final class AttributeDefinitionModel extends Model
{
    protected $connection = 'product';
    protected $table = 'attributes';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'name', 'slug', 'data_type', 'filterable', 'sortable', 'visible_on_pdp'];
    protected function casts(): array
    {
        return ['filterable' => 'boolean', 'sortable' => 'boolean', 'visible_on_pdp' => 'boolean'];
    }
}
