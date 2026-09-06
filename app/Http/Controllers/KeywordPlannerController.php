<?php

namespace App\Http\Controllers;

use App\Http\Requests\KeywordPlannerForecastRequest;
use App\Services\GoogleAds\Data\SearchContext;
use App\Services\GoogleAds\KeywordForecaster;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KeywordPlannerController extends Controller
{
    private const CSV_HEADINGS = ['Keyword', 'Match Type', 'Impressions', 'Clicks', 'Avg. CPC', 'Cost'];

    public function index(): View
    {
        return view('planner.index', [
            'results' => null,
            'maxCpcBid' => null,
            'keywordsInput' => null,
            'context' => new SearchContext,
            'forecastDays' => 30,
            'bidSweep' => null,
            'deviceBreakdown' => null,
        ]);
    }

    public function forecast(KeywordPlannerForecastRequest $request, KeywordForecaster $forecaster): View
    {
        $maxCpcBid = $request->maxCpcBid();
        $context = $request->searchContext();
        $forecastDays = $request->forecastDays();
        $keywords = $request->keywordList();

        $results = $forecaster->forecast($keywords, $maxCpcBid, $context, $forecastDays);

        return view('planner.index', [
            'results' => $results,
            'maxCpcBid' => $maxCpcBid,
            // Rendered directly rather than via a redirect, so these have to
            // be handed back explicitly — old() only survives a redirect.
            'keywordsInput' => $request->validated('keywords'),
            'context' => $context,
            'forecastDays' => $forecastDays,
            'bidSweep' => $forecaster->bidSweep($keywords, $context, $forecastDays),
            'deviceBreakdown' => $forecaster->deviceBreakdown($keywords, $context),
            'markerBid' => $maxCpcBid ?? $forecaster->suggestedBid($keywords, $context),
        ]);
    }

    public function export(KeywordPlannerForecastRequest $request, KeywordForecaster $forecaster): StreamedResponse
    {
        $results = $forecaster->forecast(
            $request->keywordList(),
            $request->maxCpcBid(),
            $request->searchContext(),
            $request->forecastDays(),
        );

        return response()->streamDownload(function () use ($results) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, self::CSV_HEADINGS);

            foreach ($results as $forecast) {
                fputcsv($stream, [
                    $forecast->keyword,
                    $forecast->matchType->value,
                    $forecast->impressions,
                    $forecast->clicks,
                    $forecast->avgCpc,
                    $forecast->cost,
                ]);
            }

            fclose($stream);
        }, 'keyword-forecast.csv', ['Content-Type' => 'text/csv']);
    }
}
