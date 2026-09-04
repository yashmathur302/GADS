<?php

namespace App\Http\Controllers;

use App\Enums\KeywordVaultType;
use App\Exports\KeywordVaultExport;
use App\Http\Requests\ImportKeywordVaultRequest;
use App\Http\Requests\StoreKeywordVaultEntryRequest;
use App\Imports\KeywordVaultImport;
use App\Models\Industry;
use App\Models\KeywordVaultEntry;
use App\Models\Niche;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KeywordVaultController extends Controller
{
    /**
     * List every industry with how many entries of this vault type it has,
     * across all of that industry's sub-categories.
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
     * List this industry's sub-categories (niches), each with its keyword
     * count for this vault type.
     */
    public function showIndustry(Request $request, Industry $industry): View
    {
        $vaultType = $this->vaultType($request);

        $niches = $industry->niches()
            ->withCount(['keywordVaultEntries' => function ($query) use ($vaultType) {
                $query->where('type', $vaultType);
            }])
            ->orderBy('name')
            ->get();

        return view('keyword-vault.niches', [
            'type' => $vaultType,
            'baseRoute' => $this->baseRoute($vaultType),
            'industry' => $industry,
            'niches' => $niches,
        ]);
    }

    /**
     * List this niche's keywords for the given vault type.
     */
    public function show(Request $request, Industry $industry, Niche $niche): View
    {
        $vaultType = $this->vaultType($request);
        $this->assertNicheBelongsToIndustry($industry, $niche);

        $entries = $niche->keywordVaultEntries()
            ->where('type', $vaultType)
            ->orderBy('keyword')
            ->get();

        return view('keyword-vault.show', [
            'type' => $vaultType,
            'baseRoute' => $this->baseRoute($vaultType),
            'industry' => $industry,
            'niche' => $niche,
            'entries' => $entries,
        ]);
    }

    /**
     * Add a keyword to this niche's vault.
     */
    public function store(StoreKeywordVaultEntryRequest $request, Industry $industry, Niche $niche): RedirectResponse
    {
        $vaultType = $this->vaultType($request);
        $this->assertNicheBelongsToIndustry($industry, $niche);

        $niche->keywordVaultEntries()->create([
            ...$request->validated(),
            'type' => $vaultType,
        ]);

        return Redirect::route($this->baseRoute($vaultType).'.show', [$industry, $niche])
            ->with('success', 'Keyword added.');
    }

    /**
     * Remove a keyword from this niche's vault.
     */
    public function destroy(Request $request, Industry $industry, Niche $niche, KeywordVaultEntry $entry): RedirectResponse
    {
        $vaultType = $this->vaultType($request);
        $this->assertNicheBelongsToIndustry($industry, $niche);

        // The entry must actually belong to this niche and vault type —
        // never trust that a valid entry ID under any URL is fair game.
        abort_unless(
            $entry->niche_id === $niche->id && $entry->type === $vaultType,
            404
        );

        $entry->delete();

        return Redirect::route($this->baseRoute($vaultType).'.show', [$industry, $niche])
            ->with('success', 'Keyword removed.');
    }

    /**
     * Download this niche's vault as an .xlsx file.
     */
    public function export(Request $request, Industry $industry, Niche $niche): BinaryFileResponse
    {
        $vaultType = $this->vaultType($request);
        $this->assertNicheBelongsToIndustry($industry, $niche);

        $entries = $niche->keywordVaultEntries()
            ->where('type', $vaultType)
            ->orderBy('keyword')
            ->get();

        $filename = Str::slug($industry->name).'-'.Str::slug($niche->name).'-'.Str::slug($vaultType->nounPlural()).'.xlsx';

        return Excel::download(new KeywordVaultExport($entries), $filename);
    }

    /**
     * Bulk-add keywords to this niche's vault from an uploaded .xlsx/.xls/
     * .csv file (a "Keyword" column, and an optional "Notes" column).
     */
    public function import(ImportKeywordVaultRequest $request, Industry $industry, Niche $niche): RedirectResponse
    {
        $vaultType = $this->vaultType($request);
        $this->assertNicheBelongsToIndustry($industry, $niche);

        $import = new KeywordVaultImport($niche, $vaultType);

        try {
            Excel::import($import, $request->file('file'));
        } catch (ExcelValidationException) {
            return Redirect::route($this->baseRoute($vaultType).'.show', [$industry, $niche])
                ->with('error', 'That file has one or more rows that are too long to import. Please fix and try again.');
        }

        return Redirect::route($this->baseRoute($vaultType).'.show', [$industry, $niche])
            ->with('success', "Imported {$import->imported} keyword(s).");
    }

    /**
     * A niche is bound by plain ID, not scoped to the industry in the URL —
     * never trust that pairing without checking it.
     */
    private function assertNicheBelongsToIndustry(Industry $industry, Niche $niche): void
    {
        abort_unless($niche->industry_id === $industry->id, 404);
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
