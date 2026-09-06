<?php

namespace App\Services\GoogleAds\Data;

/**
 * Share of forecast impressions/clicks by device — Google Ads' Forecast
 * page always shows this split.
 */
final class DeviceBreakdown
{
    public function __construct(
        public readonly float $desktopPercent,
        public readonly float $mobilePercent,
        public readonly float $tabletPercent,
    ) {}
}
