<?php

namespace Modules\Product\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** HTTP shape for a manual product relationship. */
final class CreateRelationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */ public function rules(): array { return ['kind' => ['required', 'in:related,upsell,cross_sell,alternative,fbt']]; }
}