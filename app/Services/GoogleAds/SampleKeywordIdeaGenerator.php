<?php

namespace App\Services\GoogleAds;

use App\Services\GoogleAds\Data\KeywordIdea;
use App\Services\GoogleAds\Data\SearchContext;
use App\Services\GoogleAds\Support\DeterministicKeywordMetrics;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Sample-data stand-in for the real Google Ads API. Swap the
 * `KeywordIdeaGenerator` binding in AppServiceProvider for a real
 * implementation (backed by googleads/google-ads-php and
 * KeywordPlanIdeaService.GenerateKeywordIdeas) once API credentials exist —
 * see README.md for what that requires.
 *
 * Deliberately does not fetch $pageUrl server-side: the real API has
 * Google's own servers crawl the page, so there is no reason for this app
 * to make outbound requests to admin-supplied URLs (and doing so would be
 * an SSRF risk). Instead a plausible seed term is derived from the URL's
 * own text (its path segments), the same way you'd guess a page's topic
 * from its slug without visiting it.
 */
class SampleKeywordIdeaGenerator implements KeywordIdeaGenerator
{
    private const MODIFIERS = [
        '%s',
        'best %s',
        '%s near me',
        '%s cost',
        '%s price',
        'affordable %s',
        '%s reviews',
        'top %s company',
        '%s services',
        'emergency %s',
    ];

    private const MAX_RESULTS = 25;

    public function generate(array $seedKeywords, ?string $pageUrl, SearchContext $context, int $dateRangeMonths = 12): Collection
    {
        $seeds = $this->resolveSeeds($seedKeywords, $pageUrl);

        $keywords = collect($seeds)
            ->flatMap(fn (string $seed) => collect(self::MODIFIERS)->map(
                fn (string $template) => trim(sprintf($template, $seed))
            ))
            ->map(fn (string $keyword) => Str::lower($keyword))
            ->unique()
            ->take(self::MAX_RESULTS)
            ->values();

        return $keywords->map(function (string $keyword) use ($context, $dateRangeMonths) {
            $baseline = DeterministicKeywordMetrics::baseline($keyword, $context);

            // "Avg. monthly searches" is averaged over the selected
            // historical window, same as Google Ads' own Discover page —
            // competition and CPC are not date-range dependent.
            $monthlySeries = DeterministicKeywordMetrics::monthlySeries($keyword, $context, $dateRangeMonths);
            $avgMonthlySearches = (int) round(array_sum($monthlySeries) / count($monthlySeries));

            return new KeywordIdea(
                keyword: $keyword,
                avgMonthlySearches: $avgMonthlySearches,
                competition: DeterministicKeywordMetrics::competitionLabel($baseline['competitionIndex']),
                competitionIndex: $baseline['competitionIndex'],
                lowRangeCpc: round($baseline['marketCpc'] * 0.6, 2),
                highRangeCpc: round($baseline['marketCpc'] * 1.4, 2),
                threeMonthChange: $baseline['threeMonthChange'],
                yoyChange: $baseline['yoyChange'],
            );
        });
    }

    /**
     * @param  string[]  $seedKeywords
     * @return string[]
     */
    private function resolveSeeds(array $seedKeywords, ?string $pageUrl): array
    {
        $seeds = array_values(array_filter(array_map('trim', $seedKeywords)));

        if ($seeds !== []) {
            return $seeds;
        }

        if ($pageUrl) {
            return [$this->seedFromUrl($pageUrl)];
        }

        return [];
    }

    private function seedFromUrl(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $slug = trim($path, '/');

        if ($slug === '') {
            $host = (string) parse_url($url, PHP_URL_HOST);

            return Str::of($host)->replace('www.', '')->before('.')->replace(['-', '_'], ' ')->toString();
        }

        $lastSegment = Str::of($slug)->afterLast('/');

        return $lastSegment->replace(['-', '_'], ' ')->toString();
    }
}
