<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscoverKeywordsRequest;
use App\Services\GoogleAds\Data\SearchContext;
use App\Services\GoogleAds\KeywordIdeaGenerator;
use App\Services\GoogleAds\Support\DeterministicKeywordMetrics;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DiscoverKeywordsController extends Controller
{
    private const CSV_HEADINGS = [
        'Keyword', 'Avg. Monthly Searches', 'Competition', 'Competition Index',
        'Low Range CPC', 'High Range CPC', '3 Month Change (%)', 'YoY Change (%)',
    ];

    public function index(): View
    {
        return view('discover.index', [
            'results' => null,
            'context' => new SearchContext,
            'sort' => 'avg_monthly_searches',
            'dir' => 'desc',
            'minSearches' => 0,
            'competitionFilter' => [],
            'dateRangeMonths' => 12,
            'trend' => null,
            'seedKeywordsInput' => null,
            'websiteUrlInput' => null,
        ]);
    }

    public function search(DiscoverKeywordsRequest $request, KeywordIdeaGenerator $generator): View
    {
        $results = $this->generateFiltered($request, $generator);
        $dateRangeMonths = $request->dateRangeMonths();

        return view('discover.index', [
            'results' => $results,
            'context' => $request->searchContext(),
            'sort' => $request->sortColumn(),
            'dir' => $request->sortDirection(),
            'minSearches' => $request->minSearches(),
            'competitionFilter' => $request->competitionFilter(),
            'dateRangeMonths' => $dateRangeMonths,
            'trend' => $results->isNotEmpty()
                ? $this->buildTrend($results, $request->searchContext(), $dateRangeMonths)
                : null,
            // Rendered directly rather than via a redirect, so these have to
            // be handed back explicitly — old() only survives a redirect.
            'seedKeywordsInput' => $request->input('seed_keywords'),
            'websiteUrlInput' => $request->input('website_url'),
        ]);
    }

    public function export(DiscoverKeywordsRequest $request, KeywordIdeaGenerator $generator): StreamedResponse
    {
        $results = $this->generateFiltered($request, $generator);

        return response()->streamDownload(function () use ($results) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, self::CSV_HEADINGS);

            foreach ($results as $idea) {
                fputcsv($stream, [
                    $idea->keyword,
                    $idea->avgMonthlySearches,
                    $idea->competition,
                    $idea->competitionIndex,
                    $idea->lowRangeCpc,
                    $idea->highRangeCpc,
                    $idea->threeMonthChange,
                    $idea->yoyChange,
                ]);
            }

            fclose($stream);
        }, 'keyword-ideas.csv', ['Content-Type' => 'text/csv']);
    }

    private function generateFiltered(DiscoverKeywordsRequest $request, KeywordIdeaGenerator $generator): Collection
    {
        $results = $generator->generate(
            $request->seedKeywords(),
            $request->input('website_url') ?: null,
            $request->searchContext(),
            $request->dateRangeMonths(),
        );

        return $this->filterAndSort($results, $request);
    }

    /**
     * Aggregate month-by-month search volume across the displayed keyword
     * ideas — the data behind the historical trend chart on Google Ads' own
     * Discover page.
     *
     * @return array{labels: string[], values: int[]}
     */
    private function buildTrend(Collection $results, SearchContext $context, int $months): array
    {
        $totals = array_fill(0, $months, 0);

        foreach ($results as $idea) {
            foreach (DeterministicKeywordMetrics::monthlySeries($idea->keyword, $context, $months) as $i => $value) {
                $totals[$i] += $value;
            }
        }

        $labels = [];
        for ($i = 0; $i < $months; $i++) {
            $labels[] = Carbon::now()->subMonths($months - 1 - $i)->format('M Y');
        }

        return ['labels' => $labels, 'values' => $totals];
    }

    private function filterAndSort(Collection $results, DiscoverKeywordsRequest $request): Collection
    {
        $competitionFilter = $request->competitionFilter();
        $minSearches = $request->minSearches();

        $filtered = $results->filter(function ($idea) use ($competitionFilter, $minSearches) {
            if ($competitionFilter !== [] && ! in_array($idea->competition, $competitionFilter, true)) {
                return false;
            }

            return $idea->avgMonthlySearches >= $minSearches;
        });

        $propertyMap = [
            'keyword' => 'keyword',
            'avg_monthly_searches' => 'avgMonthlySearches',
            'competition_index' => 'competitionIndex',
            'low_range_cpc' => 'lowRangeCpc',
            'high_range_cpc' => 'highRangeCpc',
        ];

        return $filtered
            ->sortBy($propertyMap[$request->sortColumn()], SORT_REGULAR, $request->sortDirection() === 'desc')
            ->values();
    }
}
