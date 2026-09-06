<?php

namespace App\Services\GoogleAds\Data;

/**
 * One point on the clicks-vs-cost forecast curve — the same "drag the bid"
 * chart Google Ads' Keyword Planner shows on its Forecast page.
 */
final class BidSweepPoint
{
    public function __construct(
        public readonly float $bid,
        public readonly int $clicks,
        public readonly float $cost,
    ) {}
}
