<?php

namespace Modules\Product\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** HTTP shape for adding one product gallery image. */
final class AddProductImageRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */ public function rules(): array { return ['path' => ['required', 'string'], 'variant_id' => ['sometimes', 'nullable', 'string'], 'alt' => ['sometimes', 'nullable', 'string'], 'position' => ['sometimes', 'integer', 'min:0'], 'is_primary' => ['sometimes', 'boolean']]; }
}