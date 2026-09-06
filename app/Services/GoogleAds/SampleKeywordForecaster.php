<?php

namespace App\Services\GoogleAds;

use App\Services\GoogleAds\Data\BidSweepPoint;
use App\Services\GoogleAds\Data\DeviceBreakdown;
use App\Services\GoogleAds\Data\KeywordForecast;
use App\Services\GoogleAds\Data\MatchType;
use App\Services\GoogleAds\Data\SearchContext;
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
    public function forecast(array $keywords, ?float $maxCpcBid, SearchContext $context, int $forecastDays): Collection
    {
        return collect($keywords)
            ->map(fn (string $keyword) => trim($keyword))
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $keyword) => $this->forecastOne($keyword, $maxCpcBid, $context, $forecastDays));
    }

    public function bidSweep(array $keywords, SearchContext $context, int $forecastDays): Collection
    {
        $cleanKeywords = collect($keywords)->map(fn (string $keyword) => trim($keyword))->filter()->unique()->values();

        if ($cleanKeywords->isEmpty()) {
            return collect();
        }

        $avgMarketCpc = $this->averageMarketCpc($cleanKeywords, $context);
        $minBid = max(0.05, round($avgMarketCpc * 0.2, 2));
        $maxBid = round($avgMarketCpc * 2.5, 2);
        $steps = 12;

        return collect(range(0, $steps))->map(function (int $step) use ($cleanKeywords, $context, $forecastDays, $minBid, $maxBid, $steps) {
            $bid = round($minBid + ($maxBid - $minBid) * ($step / $steps), 2);

            $forecasts = $cleanKeywords->map(
                fn (string $keyword) => $this->forecastOne($keyword, $bid, $context, $forecastDays)
            );

            return new BidSweepPoint(
                bid: $bid,
                clicks: (int) $forecasts->sum('clicks'),
                cost: round($forecasts->sum('cost'), 2),
            );
        });
    }

    public function suggestedBid(array $keywords, SearchContext $context): float
    {
        $cleanKeywords = collect($keywords)->map(fn (string $keyword) => trim($keyword))->filter()->unique()->values();

        return $cleanKeywords->isEmpty() ? 0.0 : round($this->averageMarketCpc($cleanKeywords, $context), 2);
    }

    /**
     * @param  Collection<int, string>  $keywords
     */
    private function averageMarketCpc(Collection $keywords, SearchContext $context): float
    {
        return (float) $keywords->map(function (string $rawKeyword) use ($context) {
            [$keyword, $matchType] = MatchType::parse($rawKeyword);
            $baseline = DeterministicKeywordMetrics::baseline($keyword, $context);

            return $baseline['marketCpc'] * $matchType->cpcFactor();
        })->avg();
    }

    public function deviceBreakdown(array $keywords, SearchContext $context): DeviceBreakdown
    {
        $key = collect($keywords)->map(fn (string $keyword) => trim(mb_strtolower($keyword)))->filter()->sort()->implode('|');
        $randomizer = new Randomizer(new Mt19937(crc32('device:'.$key.'|'.$context->cacheKey())));

        $desktop = $randomizer->getInt(30, 45);
        $tablet = $randomizer->getInt(5, 15);
        $mobile = 100 - $desktop - $tablet;

        return new DeviceBreakdown(
            desktopPercent: (float) $desktop,
            mobilePercent: (float) $mobile,
            tabletPercent: (float) $tablet,
        );
    }

    private function forecastOne(string $rawKeyword, ?float $maxCpcBid, SearchContext $context, int $forecastDays): KeywordForecast
    {
        [$keyword, $matchType] = MatchType::parse($rawKeyword);

        $baseline = DeterministicKeywordMetrics::baseline($keyword, $context);
        $marketCpc = round($baseline['marketCpc'] * $matchType->cpcFactor(), 2);
        $searchVolume = (int) round($baseline['searchVolume'] * $matchType->volumeFactor());

        // No bid set — assume this keyword's own suggested bid, the same
        // default Google Ads' own Keyword Planner falls back to.
        $effectiveBid = $maxCpcBid ?? $marketCpc;

        $bidRatio = $marketCpc > 0 ? $effectiveBid / $marketCpc : 1;
        $impressionShare = min(1.0, $bidRatio ** 0.6);

        $dayFraction = max(0, $forecastDays) / 30;
        $impressions = (int) round($searchVolume * $impressionShare * $dayFraction);

        $randomizer = new Randomizer(new Mt19937(crc32('forecast:'.mb_strtolower($keyword).'|'.$matchType->value.'|'.$context->cacheKey())));
        $ctr = $randomizer->getInt(200, 800) / 10000; // a stable 2%-8% CTR for this keyword
        $clicks = (int) round($impressions * $ctr);

        $avgCpc = $clicks > 0
            ? round(min($effectiveBid, $marketCpc) * ($randomizer->getInt(75, 95) / 100), 2)
            : 0.0;

        $cost = round($clicks * $avgCpc, 2);

        return new KeywordForecast(
            keyword: $keyword,
            matchType: $matchType,
            impressions: $impressions,
            clicks: $clicks,
            avgCpc: $avgCpc,
            cost: $cost,
        );
    }
}
