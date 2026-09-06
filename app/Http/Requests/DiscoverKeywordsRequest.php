<?php

namespace App\Http\Requests;

use App\Services\GoogleAds\Data\SearchContext;
use App\Services\GoogleAds\Support\Languages;
use App\Services\GoogleAds\Support\Locations;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscoverKeywordsRequest extends FormRequest
{
    private const SORTABLE = ['keyword', 'avg_monthly_searches', 'competition_index', 'low_range_cpc', 'high_range_cpc'];

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
            'language' => ['nullable', 'string', Rule::in(array_keys(Languages::options()))],
            'location' => ['nullable', 'string', Rule::in(array_keys(Locations::options()))],
            'include_search_partners' => ['nullable', 'boolean'],
            'min_searches' => ['nullable', 'integer', 'min:0'],
            'competition' => ['nullable', 'array'],
            'competition.*' => [Rule::in(['Low', 'Medium', 'High'])],
            // "column:direction", e.g. "avg_monthly_searches:desc" — set by
            // clicking a sortable column header (see discover/index.blade.php).
            'sort_spec' => ['nullable', 'string', 'regex:/^[a-z_]+:(asc|desc)$/'],
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

    public function searchContext(): SearchContext
    {
        return new SearchContext(
            language: $this->input('language', 'en'),
            location: $this->input('location', 'US'),
            includeSearchPartners: $this->boolean('include_search_partners'),
        );
    }

    public function sortColumn(): string
    {
        $column = explode(':', (string) $this->input('sort_spec'))[0] ?? '';

        return in_array($column, self::SORTABLE, true) ? $column : 'avg_monthly_searches';
    }

    public function sortDirection(): string
    {
        $direction = explode(':', (string) $this->input('sort_spec'))[1] ?? '';

        return $direction === 'asc' ? 'asc' : 'desc';
    }

    /**
     * @return string[]
     */
    public function competitionFilter(): array
    {
        return array_intersect((array) $this->input('competition', []), ['Low', 'Medium', 'High']);
    }

    public function minSearches(): int
    {
        return (int) $this->input('min_searches', 0);
    }
}
