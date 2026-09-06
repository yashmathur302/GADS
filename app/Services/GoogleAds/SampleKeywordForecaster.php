<?php

namespace App\Services\GoogleAds;

use App\Services\GoogleAds\Data\KeywordForecast;
use App\Services\GoogleAds\Support\DeterministicKeywordMetrics;
use Illuminate\Support\Collection;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Sample-data stand-in for the real Google Ads API — see
 * SampleKeywordIdeaGenerator's docblock for the swap-in plan.
 *
 * Models a simplified, directionally-realistic auction: impression share
 * (and so impressions/clicks) rises with the bid but saturates once the bid
 * clears the keyword's market rate, and the average CPC actually paid stays
 * a little below the max bid rather than tracking it exactly — the same
 * shape a real forecast curve has, without claiming to be one.
 */
class SampleKeywordForecaster implements KeywordForecaster
{
    public function forecast(array $keywords, ?float $maxCpcBid): Collection
    {
        return collect($keywords)
            ->map(fn (string $keyword) => trim($keyword))
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $keyword) => $this->forecastOne($keyword, $maxCpcBid));
    }

    private function forecastOne(string $keyword, ?float $maxCpcBid): KeywordForecast
    {
        $baseline = DeterministicKeywordMetrics::baseline($keyword);
        $marketCpc = $baseline['marketCpc'];

        // No bid set — assume this keyword's own suggested bid, the same
        // default Google Ads' own Keyword Planner falls back to.
        $effectiveBid = $maxCpcBid ?? $marketCpc;

        $bidRatio = $marketCpc > 0 ? $effectiveBid / $marketCpc : 1;
        $impressionShare = min(1.0, $bidRatio ** 0.6);
        $impressions = (int) round($baseline['searchVolume'] * $impressionShare);

        $randomizer = new Randomizer(new Mt19937(crc32('forecast:'.mb_strtolower($keyword))));
        $ctr = $randomizer->getInt(200, 800) / 10000; // a stable 2%-8% CTR for this keyword
        $clicks = (int) round($impressions * $ctr);

        $avgCpc = $clicks > 0
            ? round(min($effectiveBid, $marketCpc) * ($randomizer->getInt(75, 95) / 100), 2)
            : 0.0;

        $cost = round($clicks * $avgCpc, 2);

        return new KeywordForecast(
            keyword: $keyword,
            impressions: $impressions,
            clicks: $clicks,
            avgCpc: $avgCpc,
            cost: $cost,
        );
    }
}
