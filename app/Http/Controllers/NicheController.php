<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNicheRequest;
use App\Models\Industry;
use App\Models\Niche;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class NicheController extends Controller
{
    /**
     * Add a sub-category (niche) to an industry.
     */
    public function store(StoreNicheRequest $request, Industry $industry): RedirectResponse
    {
        $name = $request->validated('name');

        $industry->niches()->create([
            'name' => $name,
            'slug' => $this->uniqueSlug($industry, $name),
        ]);

        return back()->with('success', 'Sub-category added.');
    }

    /**
     * Delete a sub-category — cascades to every keyword in it.
     */
    public function destroy(Industry $industry, Niche $niche): RedirectResponse
    {
        // A niche is bound by plain ID, not scoped to the industry in the
        // URL — never trust that pairing without checking it.
        abort_unless($niche->industry_id === $industry->id, 404);

        $niche->delete();

        return back()->with('success', 'Sub-category and its keywords were deleted.');
    }

    private function uniqueSlug(Industry $industry, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while ($industry->niches()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
