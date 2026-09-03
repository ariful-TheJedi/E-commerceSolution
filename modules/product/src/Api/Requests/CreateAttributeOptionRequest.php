<?php

namespace Modules\Product\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** HTTP shape for reusable attribute options and swatch metadata. */
final class CreateAttributeOptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string'], 'slug' => ['required', 'string', 'max:255'],
            'color_hex' => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'image_path' => ['sometimes', 'nullable', 'string'], 'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
