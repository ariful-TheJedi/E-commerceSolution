<?php
namespace Modules\Product\Api\Requests;
use Illuminate\Foundation\Http\FormRequest;
/** HTTP shape for a manual or automatic catalog collection. */
final class CreateCollectionRequest extends FormRequest { public function authorize(): bool { return true; } /** @return array<string, mixed> */ public function rules(): array { return ['name' => ['required', 'string'], 'slug' => ['required', 'string', 'max:255'], 'kind' => ['required', 'in:manual,automatic'], 'match' => ['sometimes', 'nullable', 'in:all,any']]; } }