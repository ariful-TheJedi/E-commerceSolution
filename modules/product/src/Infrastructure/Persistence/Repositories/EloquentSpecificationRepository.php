<?php

namespace Modules\Product\Infrastructure\Persistence\Repositories;

use Modules\Product\Application\Ports\SpecificationRepository;
use Modules\Product\Domain\AttributeDataType;
use Modules\Product\Domain\AttributeDefinition;
use Modules\Product\Domain\ProductAttributeValue;
use Modules\Product\Domain\AttributeOption;
use Modules\Product\Infrastructure\Persistence\Models\AttributeDefinitionModel;
use Modules\Product\Infrastructure\Persistence\Models\AttributeOptionModel;
use Modules\Product\Infrastructure\Persistence\Models\ProductAttributeValueModel;

/** Maps specification domain objects to Product-owned attribute tables. */
final class EloquentSpecificationRepository implements SpecificationRepository
{
    public function createDefinition(AttributeDefinition $definition): void
    {
        AttributeDefinitionModel::query()->create([
            'id' => $definition->id, 'name' => $definition->name, 'slug' => $definition->slug,
            'data_type' => $definition->dataType->value, 'filterable' => $definition->filterable,
            'sortable' => $definition->sortable, 'visible_on_pdp' => $definition->visibleOnPdp,
        ]);
    }

    public function addOption(AttributeOption $option): void
    {
        AttributeOptionModel::query()->create([
            'id' => $option->id, 'attribute_id' => $option->attributeId, 'label' => $option->label,
            'slug' => $option->slug, 'position' => $option->position, 'color_hex' => $option->colorHex,
            'image_path' => $option->imagePath,
        ]);
    }

    public function findDefinition(string $id): ?AttributeDefinition
    {
        $row = AttributeDefinitionModel::query()->find($id);
        if ($row === null) {
            return null;
        }

        return new AttributeDefinition(
            id: $row->id, name: $row->name, slug: $row->slug,
            dataType: AttributeDataType::from($row->data_type), filterable: (bool) $row->filterable,
            sortable: (bool) $row->sortable, visibleOnPdp: (bool) $row->visible_on_pdp,
        );
    }

    public function saveValue(ProductAttributeValue $value): void
    {
        ProductAttributeValueModel::query()->updateOrCreate(
            ['id' => $value->id],
            ['product_id' => $value->productId, 'variant_id' => $value->variantId,
             'attribute_id' => $value->attributeId, 'value_text' => $value->valueText,
             'attribute_option_id' => $value->attributeOptionId],
        );
    }

    public function valuesForProduct(string $productId): array
    {
        return ProductAttributeValueModel::query()->where('product_id', $productId)->get()
            ->map(fn ($row): ProductAttributeValue => new ProductAttributeValue(
                id: $row->id, productId: $row->product_id, attributeId: $row->attribute_id,
                variantId: $row->variant_id, valueText: $row->value_text,
                attributeOptionId: $row->attribute_option_id,
            ))->all();
    }
}
