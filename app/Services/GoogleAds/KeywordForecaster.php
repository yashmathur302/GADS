<?php

namespace App\Services\GoogleAds;

use App\Services\GoogleAds\Data\BidSweepPoint;
use App\Services\GoogleAds\Data\DeviceBreakdown;
use App\Services\GoogleAds\Data\KeywordForecast;
use App\Services\GoogleAds\Data\SearchContext;
use Illuminate\Support\Collection;

interface KeywordForecaster
{
    /**
     * Forecast clicks/impressions/cost for a keyword list at a given max
     * CPC bid, over the given number of forecast days. Mirrors Google Ads'
     * KeywordPlanService forecast metrics.
     *
     * A null bid means "no bid set" — each keyword forecasts at its own
     * suggested bid rather than one bid applied across the whole list.
     * Keywords may use Google Ads' match-type syntax: [exact], "phrase",
     * or bare text for broad.
     *
     * @param  string[]  $keywords
     * @return Collection<int, KeywordForecast>
     */
    public function forecast(array $keywords, ?float $maxCpcBid, SearchContext $context, int $forecastDays): Collection;

    /**
     * Total clicks/cost across the keyword list at a spread of candidate
     * bids — the data behind Google Ads' "drag the bid" forecast chart.
     *
     * @param  string[]  $keywords
     * @return Collection<int, BidSweepPoint>
     */
    public function bidSweep(array $keywords, SearchContext $context, int $forecastDays): Collection;

    /**
     * Share of forecast traffic by device.
     *
     * @param  string[]  $keywords
     */
    public function deviceBreakdown(array $keywords, SearchContext $context): DeviceBreakdown;

    /**
     * The blended suggested bid across the keyword list — what "no bid set"
     * effectively forecasts at, and where the forecast chart marks "your bid".
     *
     * @param  string[]  $keywords
     */
    public function suggestedBid(array $keywords, SearchContext $context): float;
}
