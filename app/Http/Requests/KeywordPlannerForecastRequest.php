<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class KeywordPlannerForecastRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'keywords' => ['required', 'string', 'max:5000'],
            'max_cpc_bid' => ['required', 'numeric', 'min:0.01', 'max:1000'],
        ];
    }

    /**
     * At most 20 keywords, one per line, deduplicated.
     *
     * @return string[]
     */
    public function keywordList(): array
    {
        return collect(preg_split('/[,\n]+/', (string) $this->string('keywords')))
            ->map(fn (string $keyword) => trim($keyword))
            ->filter()
            ->unique()
            ->take(20)
            ->values()
            ->all();
    }
}
