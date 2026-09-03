<?php
namespace Modules\Product\Api\Requests;
use Illuminate\Foundation\Http\FormRequest;
/** HTTP shape for assigning fresh SKU and slug to a duplicate. */
final class DuplicateProductRequest extends FormRequest { public function authorize(): bool { return true; } /** @return array<string, mixed> */ public function rules(): array { return ['sku' => ['required', 'string'], 'slug' => ['required', 'string', 'max:255']]; } }