<?php

namespace Modules\Product\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** HTTP shape for assigning one reusable specification to a product or variant. */
final class AssignProductAttributeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'attribute_id' => ['required', 'string'], 'variant_id' => ['sometimes', 'nullable', 'string'],
            'value' => ['sometimes', 'nullable', 'string'],
            'attribute_option_id' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
