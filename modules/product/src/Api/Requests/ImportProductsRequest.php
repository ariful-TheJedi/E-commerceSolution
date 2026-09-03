<?php
namespace Modules\Product\Api\Requests;
use Illuminate\Foundation\Http\FormRequest;
/** HTTP shape for the supported product CSV import. */
final class ImportProductsRequest extends FormRequest { public function authorize(): bool { return true; } /** @return array<string, mixed> */ public function rules(): array { return ['csv' => ['required', 'string']]; } }