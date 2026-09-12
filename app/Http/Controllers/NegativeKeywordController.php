<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportKeywordCsvRequest;
use App\Models\Client;
use App\Services\KeywordCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NegativeKeywordController extends Controller
{
    public function index(): View
    {
        return view('negative-keywords.index', [
            'clients' => Client::withCount('negativeKeywords')->orderBy('name')->get(),
        ]);
    }

    public function show(Client $client): View
    {
        return view('negative-keywords.show', [
            'client' => $client,
            'keywords' => $client->negativeKeywords()->orderBy('keyword')->get(),
        ]);
    }

    public function import(ImportKeywordCsvRequest $request, Client $client, KeywordCsvImporter $importer): RedirectResponse
    {
        $result = $importer->import($request->file('file'), $client, 'negativeKeywords');

        return redirect()
            ->route('negative-keywords.show', $client)
            ->with('status', $this->summarize($result));
    }

    public function export(Client $client): StreamedResponse
    {
        $keywords = $client->negativeKeywords()->orderBy('keyword')->get();

        return response()->streamDownload(function () use ($keywords) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Keyword', 'Match Type']);

            foreach ($keywords as $keyword) {
                fputcsv($stream, [$keyword->keyword, $keyword->match_type->value]);
            }

            fclose($stream);
        }, str($client->name)->slug()->value().'-negative-keywords.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array{imported: int, duplicates: int, truncated: bool}  $result
     */
    private function summarize(array $result): string
    {
        $message = __(':count :word imported.', [
            'count' => $result['imported'],
            'word' => $result['imported'] === 1 ? 'negative keyword' : 'negative keywords',
        ]);

        if ($result['duplicates'] > 0) {
            $message .= ' '.__(':count :word skipped as duplicate (already existed).', [
                'count' => $result['duplicates'],
                'word' => $result['duplicates'] === 1 ? 'entry' : 'entries',
            ]);
        }

        if ($result['truncated']) {
            $message .= ' '.__('Only the first :max rows in the file were processed.', ['max' => KeywordCsvImporter::MAX_ROWS]);
        }

        return $message;
    }
}
