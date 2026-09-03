<?php
namespace Modules\Product\Api\Requests;
use Illuminate\Foundation\Http\FormRequest;
/** HTTP shape for assigning a product category. */
final class CategoryAssignmentRequest extends FormRequest { public function authorize(): bool { return true; } /** @return array<string, mixed> */ public function rules(): array { return ['canonical' => ['sometimes', 'boolean'], 'position' => ['sometimes', 'integer', 'min:0']]; } }