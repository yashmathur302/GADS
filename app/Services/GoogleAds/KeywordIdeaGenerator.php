<?php

namespace App\Services\GoogleAds;

use App\Services\GoogleAds\Data\KeywordIdea;
use App\Services\GoogleAds\Data\SearchContext;
use Illuminate\Support\Collection;

interface KeywordIdeaGenerator
{
    /**
     * Generate keyword ideas from seed keywords and/or a landing page URL —
     * at least one must be given. Mirrors Google Ads'
     * KeywordPlanIdeaService.GenerateKeywordIdeas.
     *
     * @param  string[]  $seedKeywords
     * @return Collection<int, KeywordIdea>
     */
    public function generate(array $seedKeywords, ?string $pageUrl, SearchContext $context): Collection;
}
