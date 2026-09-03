<?php
namespace Modules\Product\Api\Requests;
use Illuminate\Foundation\Http\FormRequest;
/** HTTP shape for an automatic collection catalog rule. */
final class CollectionRuleRequest extends FormRequest { public function authorize(): bool { return true; } /** @return array<string, mixed> */ public function rules(): array { return ['field' => ['required', 'in:type,tag,brand,attribute'], 'operator' => ['required', 'in:eq,neq,in'], 'value' => ['required', 'string']]; } }