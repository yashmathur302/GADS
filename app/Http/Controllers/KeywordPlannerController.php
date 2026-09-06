<?php

namespace App\Http\Controllers;

use App\Http\Requests\KeywordPlannerForecastRequest;
use App\Services\GoogleAds\KeywordForecaster;
use Illuminate\View\View;

class KeywordPlannerController extends Controller
{
    public function index(): View
    {
        return view('planner.index', [
            'results' => null,
            'maxCpcBid' => null,
            'keywordsInput' => null,
        ]);
    }

    public function forecast(KeywordPlannerForecastRequest $request, KeywordForecaster $forecaster): View
    {
        $maxCpcBid = (float) $request->validated('max_cpc_bid');

        $results = $forecaster->forecast($request->keywordList(), $maxCpcBid);

        return view('planner.index', [
            'results' => $results,
            'maxCpcBid' => $maxCpcBid,
            // Rendered directly rather than via a redirect, so the keyword
            // list has to be handed back explicitly for the textarea to
            // keep it — old() only survives a redirect.
            'keywordsInput' => $request->validated('keywords'),
        ]);
    }
}
