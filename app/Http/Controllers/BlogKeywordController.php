<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCsvFileRequest;
use App\Models\BlogKeyword;
use App\Models\Client;
use App\Services\BlogKeywordCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BlogKeywordController extends Controller
{
    public function index(): View
    {
        return view('blog-keywords.index', [
            'clients' => Client::ofType(Client::TYPE_BLOG_KEYWORDS)->withCount('blogKeywords')->orderBy('name')->get(),
        ]);
    }

    public function show(Client $client): View
    {
        abort_unless($client->type === Client::TYPE_BLOG_KEYWORDS, 404);

        return view('blog-keywords.show', [
            'client' => $client,
            'blogKeywords' => $client->blogKeywords()->orderBy('keyword')->get(),
        ]);
    }

    public function import(ImportCsvFileRequest $request, Client $client, BlogKeywordCsvImporter $importer): RedirectResponse
    {
        abort_unless($client->type === Client::TYPE_BLOG_KEYWORDS, 404);

        $result = $importer->import($request->file('file'), $client);

        return redirect()
            ->route('blog-keywords.show', $client)
            ->with('status', $this->summarize($result));
    }

    public function destroy(Client $client, BlogKeyword $blogKeyword): RedirectResponse
    {
        abort_unless($client->type === Client::TYPE_BLOG_KEYWORDS, 404);
        abort_unless($blogKeyword->client_id === $client->id, 404);

        $blogKeyword->delete();

        return redirect()
            ->route('blog-keywords.show', $client)
            ->with('status', __(':keyword was deleted.', ['keyword' => $blogKeyword->keyword]));
    }

    public function export(Client $client): StreamedResponse
    {
        abort_unless($client->type === Client::TYPE_BLOG_KEYWORDS, 404);

        $blogKeywords = $client->blogKeywords()->orderBy('keyword')->get();

        return response()->streamDownload(function () use ($blogKeywords) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Keyword']);

            foreach ($blogKeywords as $blogKeyword) {
                fputcsv($stream, [$blogKeyword->keyword]);
            }

            fclose($stream);
        }, str($client->name)->slug()->value().'-blog-keywords.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array{imported: int, duplicates: int, truncated: bool}  $result
     */
    private function summarize(array $result): string
    {
        $message = __(':count :word imported.', [
            'count' => $result['imported'],
            'word' => $result['imported'] === 1 ? 'blog keyword' : 'blog keywords',
        ]);

        if ($result['duplicates'] > 0) {
            $message .= ' '.__(':count :word skipped as duplicate (already existed).', [
                'count' => $result['duplicates'],
                'word' => $result['duplicates'] === 1 ? 'entry' : 'entries',
            ]);
        }

        if ($result['truncated']) {
            $message .= ' '.__('Only the first :max rows in the file were processed.', ['max' => BlogKeywordCsvImporter::MAX_ROWS]);
        }

        return $message;
    }
}
