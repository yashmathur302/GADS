<?php

namespace App\Services\GoogleAds\Data;

/**
 * One row of Keyword Planner forecast results at a given max CPC bid —
 * mirrors the fields Google Ads' KeywordPlanService forecast metrics return.
 */
final class KeywordForecast
{
    public function __construct(
        public readonly string $keyword,
        public readonly int $impressions,
        public readonly int $clicks,
        public readonly float $avgCpc,
        public readonly float $cost,
    ) {}
}
