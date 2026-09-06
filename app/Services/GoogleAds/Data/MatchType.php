<?php

namespace App\Services\GoogleAds\Data;

/**
 * Google Ads keyword match types, and the plain-text syntax used to enter
 * them: [exact], "phrase", or bare text for broad — the same convention
 * Google Ads' own tools use.
 */
enum MatchType: string
{
    case Broad = 'Broad';
    case Phrase = 'Phrase';
    case Exact = 'Exact';

    /**
     * Parse one line of keyword input, stripping the match-type syntax.
     *
     * @return array{0: string, 1: self}
     */
    public static function parse(string $rawKeyword): array
    {
        $trimmed = trim($rawKeyword);

        if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
            return [trim(substr($trimmed, 1, -1)), self::Exact];
        }

        if (str_starts_with($trimmed, '"') && str_ends_with($trimmed, '"') && strlen($trimmed) > 1) {
            return [trim(substr($trimmed, 1, -1)), self::Phrase];
        }

        return [$trimmed, self::Broad];
    }

    /**
     * How this match type scales volume/CPC relative to the keyword's
     * broad-match baseline — narrower match types reach fewer, more
     * qualified searches.
     */
    public function volumeFactor(): float
    {
        return match ($this) {
            self::Broad => 1.3,
            self::Phrase => 1.0,
            self::Exact => 0.6,
        };
    }

    public function cpcFactor(): float
    {
        return match ($this) {
            self::Broad => 0.9,
            self::Phrase => 1.0,
            self::Exact => 1.1,
        };
    }
}
