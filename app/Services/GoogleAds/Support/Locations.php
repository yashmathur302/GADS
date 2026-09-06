<?php

namespace App\Services\GoogleAds\Support;

/**
 * A handful of the locations Google Ads targeting supports, each with a
 * rough relative search-volume size versus the US — enough to make
 * switching locations visibly change the sample numbers.
 */
class Locations
{
    private const LOCATIONS = [
        'US' => ['label' => 'United States', 'sizeFactor' => 1.00],
        'GB' => ['label' => 'United Kingdom', 'sizeFactor' => 0.35],
        'CA' => ['label' => 'Canada', 'sizeFactor' => 0.28],
        'AU' => ['label' => 'Australia', 'sizeFactor' => 0.22],
        'IN' => ['label' => 'India', 'sizeFactor' => 0.85],
        'DE' => ['label' => 'Germany', 'sizeFactor' => 0.42],
        'FR' => ['label' => 'France', 'sizeFactor' => 0.38],
        'BR' => ['label' => 'Brazil', 'sizeFactor' => 0.55],
    ];

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_map(fn (array $location) => $location['label'], self::LOCATIONS);
    }

    public static function label(string $code): string
    {
        return self::LOCATIONS[$code]['label'] ?? $code;
    }

    public static function sizeFactor(string $code): float
    {
        return self::LOCATIONS[$code]['sizeFactor'] ?? 1.0;
    }
}
