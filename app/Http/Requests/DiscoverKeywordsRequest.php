<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DiscoverKeywordsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'seed_keywords' => ['nullable', 'required_without:website_url', 'string', 'max:1000'],
            'website_url' => ['nullable', 'required_without:seed_keywords', 'url', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'seed_keywords.required_without' => 'Enter at least one keyword, or a website URL.',
            'website_url.required_without' => 'Enter a website URL, or at least one keyword.',
        ];
    }

    /**
     * @return string[]
     */
    public function seedKeywords(): array
    {
        return collect(preg_split('/[,\n]+/', (string) $this->string('seed_keywords')))
            ->map(fn (string $keyword) => trim($keyword))
            ->filter()
            ->values()
            ->all();
    }
}
