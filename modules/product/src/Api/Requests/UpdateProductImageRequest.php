<?php

namespace Modules\Product\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** HTTP shape for reordering or remapping one gallery image. */
final class UpdateProductImageRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */ public function rules(): array { return ['variant_id' => ['sometimes', 'nullable', 'string'], 'alt' => ['sometimes', 'nullable', 'string'], 'position' => ['sometimes', 'integer', 'min:0'], 'is_primary' => ['sometimes', 'boolean']]; }
}