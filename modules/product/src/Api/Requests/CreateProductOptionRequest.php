<?php

namespace Modules\Product\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** HTTP shape for a product option dimension. */
final class CreateProductOptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */ public function rules(): array { return ['name' => ['required', 'string'], 'position' => ['sometimes', 'integer', 'min:0']]; }
}
