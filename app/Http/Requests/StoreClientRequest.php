<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'industry_category' => ['required', 'string', 'max:255'],
            // Which tab to return to after creating the client. Validated
            // against a fixed allowlist (not used as a raw URL) so this
            // can never become an open-redirect vector.
            'context' => ['required', Rule::in(['keywords', 'negative-keywords'])],
        ];
    }

    public function redirectRouteName(): string
    {
        return $this->input('context') === 'negative-keywords' ? 'negative-keywords.index' : 'keywords.index';
    }
}
