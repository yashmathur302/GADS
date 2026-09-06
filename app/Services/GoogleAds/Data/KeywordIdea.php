<?php

namespace App\Services\GoogleAds\Data;

/**
 * One row of "Discover new keywords" results — mirrors the fields Google
 * Ads' KeywordPlanIdeaService.GenerateKeywordIdeas returns.
 */
final class KeywordIdea
{
    public function __construct(
        public readonly string $keyword,
        public readonly int $avgMonthlySearches,
        public readonly string $competition,
        public readonly int $competitionIndex,
        public readonly float $lowRangeCpc,
        public readonly float $highRangeCpc,
        public readonly int $threeMonthChange,
        public readonly int $yoyChange,
    ) {}
}
