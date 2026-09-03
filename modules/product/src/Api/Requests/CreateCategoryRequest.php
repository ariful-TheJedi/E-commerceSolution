<?php
namespace Modules\Product\Api\Requests;
use Illuminate\Foundation\Http\FormRequest;
/** HTTP shape for a nested catalog category. */
final class CreateCategoryRequest extends FormRequest { public function authorize(): bool { return true; } /** @return array<string, mixed> */ public function rules(): array { return ['name' => ['required', 'string'], 'slug' => ['required', 'string', 'max:255'], 'parent_id' => ['sometimes', 'nullable', 'string'], 'position' => ['sometimes', 'integer', 'min:0']]; } }