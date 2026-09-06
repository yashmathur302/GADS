<?php

namespace App\Http\Requests;

use App\Services\GoogleAds\Data\SearchContext;
use App\Services\GoogleAds\Support\Languages;
use App\Services\GoogleAds\Support\Locations;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // Optional — when left blank, each keyword forecasts at its own
            // suggested bid (see SampleKeywordForecaster).
            'max_cpc_bid' => ['nullable', 'numeric', 'min:0.01', 'max:1000'],
            'language' => ['nullable', 'string', Rule::in(array_keys(Languages::options()))],
            'location' => ['nullable', 'string', Rule::in(array_keys(Locations::options()))],
            'include_search_partners' => ['nullable', 'boolean'],
            'forecast_days' => ['nullable', 'integer', Rule::in([7, 14, 30])],
        ];
    }

    /**
     * At most 20 keywords, one per line, deduplicated. Google Ads match-type
     * syntax ([exact], "phrase") is preserved for the forecaster to parse.
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

    public function maxCpcBid(): ?float
    {
        return $this->filled('max_cpc_bid') ? (float) $this->input('max_cpc_bid') : null;
    }

    public function searchContext(): SearchContext
    {
        return new SearchContext(
            language: $this->input('language', 'en'),
            location: $this->input('location', 'US'),
            includeSearchPartners: $this->boolean('include_search_partners'),
        );
    }

    public function forecastDays(): int
    {
        return (int) $this->input('forecast_days', 30);
    }
}
