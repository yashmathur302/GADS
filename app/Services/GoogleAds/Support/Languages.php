<?php

namespace App\Services\GoogleAds\Support;

/**
 * A handful of the languages Google Ads targeting supports.
 */
class Languages
{
    private const LANGUAGES = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'pt' => 'Portuguese',
        'hi' => 'Hindi',
        'it' => 'Italian',
        'ja' => 'Japanese',
    ];

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return self::LANGUAGES;
    }

    public static function label(string $code): string
    {
        return self::LANGUAGES[$code] ?? $code;
    }
}
