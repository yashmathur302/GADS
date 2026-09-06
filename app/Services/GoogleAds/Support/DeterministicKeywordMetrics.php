<?php

namespace App\Services\GoogleAds\Support;

use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Derives stable, believable-looking baseline metrics for a keyword from a
 * hash of its text — the same keyword always yields the same numbers, so
 * results feel consistent between the Discover and Planner tools and across
 * repeated searches, without needing to persist anything.
 *
 * This exists only because there is no live Google Ads API connection yet
 * (see App\Services\GoogleAds\*Interface for the swap-in point). None of
 * this logic survives once real API credentials are wired up.
 */
class DeterministicKeywordMetrics
{
    /**
     * @return array{searchVolume: int, marketCpc: float, competitionIndex: int}
     */
    public static function baseline(string $keyword): array
    {
        $seed = crc32(mb_strtolower(trim($keyword)));

        // A local, seeded generator so this never disturbs global PHP RNG
        // state (e.g. other code relying on mt_rand/random_int elsewhere).
        $random = new Mt19937($seed);
        $engine = new Randomizer($random);

        $competitionIndex = $engine->getInt(1, 100);

        // Longer, more specific phrases tend to have lower volume and
        // lower competition than short, generic ones — a real enough
        // pattern to make the sample data feel plausible.
        $wordCount = max(1, str_word_count($keyword));
        $volumeCeiling = (int) max(50, 40000 / $wordCount);
        $searchVolume = $engine->getInt(10, $volumeCeiling);

        $marketCpc = round($engine->getInt(50, 50 + $competitionIndex * 15) / 100, 2);

        return [
            'searchVolume' => $searchVolume,
            'marketCpc' => $marketCpc,
            'competitionIndex' => $competitionIndex,
        ];
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
