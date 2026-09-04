<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIndustryRequest;
use App\Models\Industry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class IndustryController extends Controller
{
    /**
     * Add an industry. Shared taxonomy for both vaults, so this redirects
     * back to whichever vault page the admin was on.
     */
    public function store(StoreIndustryRequest $request): RedirectResponse
    {
        $name = $request->validated('name');

        Industry::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
        ]);

        return back()->with('success', 'Industry added.');
    }

    /**
     * Delete an industry — cascades to its niches and every keyword in them.
     */
    public function destroy(Industry $industry): RedirectResponse
    {
        $industry->delete();

        return back()->with('success', 'Industry and all of its sub-categories and keywords were deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Industry::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
