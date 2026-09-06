<?php

namespace App\Services\GoogleAds\Data;

/**
 * Targeting settings shared by both tools — language, location, and network
 * — mirroring the controls always visible in Google Ads' own Keyword
 * Planner. Feeds into DeterministicKeywordMetrics so changing them actually
 * changes the sample numbers, the same way they'd change a real query.
 */
final class SearchContext
{
    public function __construct(
        public readonly string $language = 'en',
        public readonly string $location = 'US',
        public readonly bool $includeSearchPartners = false,
    ) {}

    public function cacheKey(): string
    {
        return $this->language.'|'.$this->location.'|'.($this->includeSearchPartners ? 'partners' : 'search');
    }
}
