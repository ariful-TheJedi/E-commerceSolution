<?php

namespace Modules\Product\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** HTTP shape for a sellable SKU combination. */
final class CreateVariantRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */ public function rules(): array { return ['sku' => ['required', 'string'], 'option_value_ids' => ['sometimes', 'array'], 'option_value_ids.*' => ['string', 'distinct'], 'is_default' => ['sometimes', 'boolean'], 'barcode' => ['sometimes', 'nullable', 'string'], 'gtin' => ['sometimes', 'nullable', 'string'], 'mpn' => ['sometimes', 'nullable', 'string'], 'price_minor' => ['sometimes', 'integer', 'min:0'], 'currency' => ['sometimes', 'string', 'size:3', 'uppercase']]; }
}
