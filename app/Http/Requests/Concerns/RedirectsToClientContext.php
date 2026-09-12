<?php

namespace App\Http\Requests\Concerns;

/**
 * Resolves the Keywords/Negative Keywords/Location index route to redirect
 * back to after a client create/update/delete, based on the validated
 * "context" field — never a raw URL, so this can't become an open
 * redirect.
 */
trait RedirectsToClientContext
{
    public function redirectRouteName(): string
    {
        return match ($this->input('context')) {
            'negative-keywords' => 'negative-keywords.index',
            'location' => 'locations.index',
            default => 'keywords.index',
        };
    }
}
