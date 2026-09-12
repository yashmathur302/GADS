<?php

namespace App\Http\Requests\Concerns;

/**
 * Resolves the Keywords/Negative Keywords index route to redirect back to
 * after a client create/update/delete, based on the validated "context"
 * field — never a raw URL, so this can't become an open redirect.
 */
trait RedirectsToClientContext
{
    public function redirectRouteName(): string
    {
        return $this->input('context') === 'negative-keywords' ? 'negative-keywords.index' : 'keywords.index';
    }
}
