<?php
namespace Modules\Product\Api\Requests;
use Illuminate\Foundation\Http\FormRequest;
/** HTTP shape for bulk listing flag edits. */
final class BulkEditProductsRequest extends FormRequest { public function authorize(): bool { return true; } /** @return array<string, mixed> */ public function rules(): array { return ['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['string'], 'visibility' => ['sometimes', 'nullable', 'in:visible,catalog,search,hidden'], 'featured' => ['sometimes', 'boolean']]; } }