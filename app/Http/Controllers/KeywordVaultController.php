<?php

namespace App\Http\Controllers;

use App\Enums\KeywordVaultType;
use App\Http\Requests\StoreKeywordVaultEntryRequest;
use App\Models\Industry;
use App\Models\KeywordVaultEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class KeywordVaultController extends Controller
{
    /**
     * List every industry with how many entries of this vault type it has.
     */
    public function index(Request $request): View
    {
        $vaultType = $this->vaultType($request);

        $industries = Industry::query()
            ->withCount(['keywordVaultEntries' => function ($query) use ($vaultType) {
                $query->where('type', $vaultType);
            }])
            ->orderBy('name')
            ->get();

        return view('keyword-vault.industries', [
            'type' => $vaultType,
            'baseRoute' => $this->baseRoute($vaultType),
            'industries' => $industries,
        ]);
    }

    /**
     * List this industry's keywords for the given vault type.
     */
    public function show(Request $request, Industry $industry): View
    {
        $vaultType = $this->vaultType($request);

        $entries = $industry->keywordVaultEntries()
            ->where('type', $vaultType)
            ->orderBy('keyword')
            ->get();

        return view('keyword-vault.show', [
            'type' => $vaultType,
            'baseRoute' => $this->baseRoute($vaultType),
            'industry' => $industry,
            'entries' => $entries,
        ]);
    }

    /**
     * Add a keyword to this industry's vault.
     */
    public function store(StoreKeywordVaultEntryRequest $request, Industry $industry): RedirectResponse
    {
        $vaultType = $this->vaultType($request);

        $industry->keywordVaultEntries()->create([
            ...$request->validated(),
            'type' => $vaultType,
        ]);

        return Redirect::route($this->baseRoute($vaultType).'.show', $industry)
            ->with('success', 'Keyword added.');
    }

    /**
     * Remove a keyword from this industry's vault.
     */
    public function destroy(Request $request, Industry $industry, KeywordVaultEntry $entry): RedirectResponse
    {
        $vaultType = $this->vaultType($request);

        // The entry must actually belong to this industry and vault type —
        // never trust that a valid entry ID under any URL is fair game.
        abort_unless(
            $entry->industry_id === $industry->id && $entry->type === $vaultType,
            404
        );

        $entry->delete();

        return Redirect::route($this->baseRoute($vaultType).'.show', $industry)
            ->with('success', 'Keyword removed.');
    }

    /**
     * The vault type for the current request, from the route's `type`
     * default (see routes/web.php). Read via the request rather than as a
     * bound method parameter — Laravel resolves non-injected controller
     * parameters positionally against the route's parameter list, and
     * that order is easy to break by accident when it's mixed with
     * implicit model binding.
     */
    private function vaultType(Request $request): KeywordVaultType
    {
        return KeywordVaultType::from($request->route('type'));
    }

    /**
     * The named-route prefix for this vault type (see routes/web.php).
     */
    private function baseRoute(KeywordVaultType $type): string
    {
        return $type === KeywordVaultType::Keyword
            ? 'assets.keyword-vault'
            : 'assets.negative-keywords';
    }
}
