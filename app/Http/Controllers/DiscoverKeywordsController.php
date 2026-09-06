<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscoverKeywordsRequest;
use App\Services\GoogleAds\Data\SearchContext;
use App\Services\GoogleAds\KeywordIdeaGenerator;
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
            'seedKeywordsInput' => null,
            'websiteUrlInput' => null,
        ]);
    }

    public function search(DiscoverKeywordsRequest $request, KeywordIdeaGenerator $generator): View
    {
        $results = $this->generateFiltered($request, $generator);

        return view('discover.index', [
            'results' => $results,
            'context' => $request->searchContext(),
            'sort' => $request->sortColumn(),
            'dir' => $request->sortDirection(),
            'minSearches' => $request->minSearches(),
            'competitionFilter' => $request->competitionFilter(),
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
        );

        return $this->filterAndSort($results, $request);
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
