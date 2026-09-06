<?php

namespace App\Services\GoogleAds;

use App\Services\GoogleAds\Data\KeywordForecast;
use Illuminate\Support\Collection;

interface KeywordForecaster
{
    /**
     * Forecast clicks/impressions/cost for a keyword list at a given max
     * CPC bid. Mirrors Google Ads' KeywordPlanService forecast metrics.
     *
     * @param  string[]  $keywords
     * @return Collection<int, KeywordForecast>
     */
    public function forecast(array $keywords, float $maxCpcBid): Collection;
}
