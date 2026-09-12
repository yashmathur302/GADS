<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\RedirectsToClientContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    use RedirectsToClientContext;

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
            'context' => ['required', Rule::in(['keywords', 'negative-keywords'])],
        ];
    }
}
