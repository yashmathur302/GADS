<?php

namespace App\Services\GoogleAds\Support;

use App\Services\GoogleAds\Data\SearchContext;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Derives stable, believable-looking baseline metrics for a keyword from a
 * hash of its text and search context (language/location/network) — the
 * same keyword+context always yields the same numbers, so results feel
 * consistent between the Discover and Planner tools and across repeated
 * searches, without needing to persist anything.
 *
 * This exists only because there is no live Google Ads API connection yet
 * (see App\Services\GoogleAds\*Interface for the swap-in point). None of
 * this logic survives once real API credentials are wired up.
 */
class DeterministicKeywordMetrics
{
    /**
     * @return array{searchVolume: int, marketCpc: float, competitionIndex: int, threeMonthChange: int, yoyChange: int}
     */
    public static function baseline(string $keyword, ?SearchContext $context = null): array
    {
        $context ??= new SearchContext;
        $normalized = mb_strtolower(trim($keyword));
        $seed = crc32($normalized.'|'.$context->cacheKey());

        // A local, seeded generator so this never disturbs global PHP RNG
        // state (e.g. other code relying on mt_rand/random_int elsewhere).
        $engine = new Randomizer(new Mt19937($seed));

        $competitionIndex = $engine->getInt(1, 100);

        // Longer, more specific phrases tend to have lower volume and
        // lower competition than short, generic ones — a real enough
        // pattern to make the sample data feel plausible.
        $wordCount = max(1, str_word_count($keyword));
        $volumeCeiling = (int) max(50, 40000 / $wordCount);
        $searchVolume = $engine->getInt(10, $volumeCeiling);

        $searchVolume = (int) round($searchVolume * Locations::sizeFactor($context->location));

        if ($context->includeSearchPartners) {
            $searchVolume = (int) round($searchVolume * 1.15);
        }

        $marketCpc = round($engine->getInt(50, 50 + $competitionIndex * 15) / 100, 2);

        return [
            'searchVolume' => max(0, $searchVolume),
            'marketCpc' => $marketCpc,
            'competitionIndex' => $competitionIndex,
            // Percent change vs. the prior 3 months / same month last year —
            // shown as trend columns in Google's own Discover tool.
            'threeMonthChange' => $engine->getInt(-40, 60),
            'yoyChange' => $engine->getInt(-60, 120),
        ];
    }

    /**
     * Month-by-month search volume, oldest to newest — the data behind
     * Google Ads' own historical trend chart on the Discover page, and the
     * basis for "avg. monthly searches" over a selected date range.
     *
     * @return int[]
     */
    public static function monthlySeries(string $keyword, SearchContext $context, int $months): array
    {
        $baseline = self::baseline($keyword, $context);
        $seed = crc32(mb_strtolower(trim($keyword)).'|'.$context->cacheKey().'|monthly');
        $engine = new Randomizer(new Mt19937($seed));
        $phase = $engine->getInt(0, 11);

        $series = [];

        for ($i = 0; $i < $months; $i++) {
            $seasonal = 1 + 0.3 * sin((M_PI / 6) * ($i + $phase));
            $noise = $engine->getInt(85, 115) / 100;
            $series[] = max(0, (int) round($baseline['searchVolume'] * $seasonal * $noise));
        }

        return $series;
    }

    public static function competitionLabel(int $competitionIndex): string
    {
        return match (true) {
            $competitionIndex >= 67 => 'High',
            $competitionIndex >= 34 => 'Medium',
            default => 'Low',
        };
    }
}
