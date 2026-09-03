<?php

namespace Modules\Product\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** HTTP shape for creating a reusable catalog specification definition. */
final class CreateAttributeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'], 'slug' => ['required', 'string', 'max:255'],
            'data_type' => ['required', 'in:text,number,boolean,enum'],
            'filterable' => ['sometimes', 'boolean'], 'sortable' => ['sometimes', 'boolean'],
            'visible_on_pdp' => ['sometimes', 'boolean'],
        ];
    }
}
