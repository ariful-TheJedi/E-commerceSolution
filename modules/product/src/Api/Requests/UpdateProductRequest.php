<?php

namespace Modules\Product\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shape of PATCH /api/v1/products/{id}. Null / omitted fields mean leave unchanged.
 * Unique SKU is enforced in Application, not only here.
 */
final class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string'],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'brand' => ['sometimes', 'nullable', 'string'],
            'sku' => ['sometimes', 'string'],
            'barcode' => ['sometimes', 'nullable', 'string'],
            'gtin' => ['sometimes', 'nullable', 'string'],
            'mpn' => ['sometimes', 'nullable', 'string'],
            'price_minor' => ['sometimes', 'integer', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3', 'uppercase'],
            'compare_at_minor' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'cost_minor' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'sale_starts_at' => ['sometimes', 'nullable', 'date'],
            'sale_ends_at' => ['sometimes', 'nullable', 'date', 'after:sale_starts_at'],
            'tax_status' => ['sometimes', 'in:taxable,none'],
            'tax_class' => ['sometimes', 'nullable', 'string'],
            'type' => ['sometimes', 'in:physical,virtual,downloadable,grouped,bundle,external'],
            'sold_individually' => ['sometimes', 'boolean'],
            'external_url' => ['sometimes', 'nullable', 'url'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string'],
            'visibility' => ['sometimes', 'in:visible,catalog,search,hidden'],
            'featured' => ['sometimes', 'boolean'],
            'weight_g' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'length_mm' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'width_mm' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'height_mm' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'variant_weight_g' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'variant_length_mm' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'variant_width_mm' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'variant_height_mm' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'shipping_class' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
