<?php

namespace App\Enums;

enum KeywordVaultType: string
{
    case Keyword = 'keyword';
    case Negative = 'negative';

    public function label(): string
    {
        return match ($this) {
            self::Keyword => 'Keyword Vault',
            self::Negative => 'Negative Keyword Vault',
        };
    }

    public function nounPlural(): string
    {
        return match ($this) {
            self::Keyword => 'keywords',
            self::Negative => 'negative keywords',
        };
    }

    public function nounSingular(): string
    {
        return match ($this) {
            self::Keyword => 'keyword',
            self::Negative => 'negative keyword',
        };
    }
}
