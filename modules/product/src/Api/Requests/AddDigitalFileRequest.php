<?php

namespace Modules\Product\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** HTTP shape for catalog download metadata; Orders owns entitlement. */
final class AddDigitalFileRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */ public function rules(): array { return ['path' => ['required', 'string'], 'variant_id' => ['sometimes', 'nullable', 'string'], 'download_limit' => ['sometimes', 'nullable', 'integer', 'min:1'], 'expires_after_days' => ['sometimes', 'nullable', 'integer', 'min:1']]; }
}