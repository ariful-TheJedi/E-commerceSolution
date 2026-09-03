<?php
namespace Modules\Product\Api\Requests;
use Illuminate\Foundation\Http\FormRequest;
/** HTTP shape for a flat catalog tag. */
final class CreateTagRequest extends FormRequest { public function authorize(): bool { return true; } /** @return array<string, mixed> */ public function rules(): array { return ['name' => ['required', 'string'], 'slug' => ['required', 'string', 'max:255']]; } }