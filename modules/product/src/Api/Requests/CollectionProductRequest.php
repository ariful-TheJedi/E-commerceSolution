<?php
namespace Modules\Product\Api\Requests;
use Illuminate\Foundation\Http\FormRequest;
/** HTTP shape for ordering a product in a manual collection. */
final class CollectionProductRequest extends FormRequest { public function authorize(): bool { return true; } /** @return array<string, mixed> */ public function rules(): array { return ['position' => ['sometimes', 'integer', 'min:0']]; } }