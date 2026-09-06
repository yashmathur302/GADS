<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscoverKeywordsRequest;
use App\Services\GoogleAds\KeywordIdeaGenerator;
use Illuminate\View\View;

class DiscoverKeywordsController extends Controller
{
    public function index(): View
    {
        return view('discover.index', [
            'results' => null,
        ]);
    }

    public function search(DiscoverKeywordsRequest $request, KeywordIdeaGenerator $generator): View
    {
        $results = $generator->generate(
            $request->seedKeywords(),
            $request->input('website_url') ?: null,
        );

        return view('discover.index', [
            'results' => $results,
        ]);
    }
}
