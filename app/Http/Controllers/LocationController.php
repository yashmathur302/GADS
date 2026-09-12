<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCsvFileRequest;
use App\Models\Client;
use App\Services\LocationCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LocationController extends Controller
{
    public function index(): View
    {
        return view('locations.index', [
            'clients' => Client::ofType(Client::TYPE_LOCATION)->withCount('locations')->orderBy('name')->get(),
        ]);
    }

    public function show(Client $client): View
    {
        abort_unless($client->type === Client::TYPE_LOCATION, 404);

        return view('locations.show', [
            'client' => $client,
            'locations' => $client->locations()->orderBy('location')->get(),
        ]);
    }

    public function import(ImportCsvFileRequest $request, Client $client, LocationCsvImporter $importer): RedirectResponse
    {
        abort_unless($client->type === Client::TYPE_LOCATION, 404);

        $result = $importer->import($request->file('file'), $client);

        return redirect()
            ->route('locations.show', $client)
            ->with('status', $this->summarize($result));
    }

    public function export(Client $client): StreamedResponse
    {
        abort_unless($client->type === Client::TYPE_LOCATION, 404);

        $locations = $client->locations()->orderBy('location')->get();

        return response()->streamDownload(function () use ($locations) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['Location']);

            foreach ($locations as $location) {
                fputcsv($stream, [$location->location]);
            }

            fclose($stream);
        }, str($client->name)->slug()->value().'-locations.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array{imported: int, duplicates: int, truncated: bool}  $result
     */
    private function summarize(array $result): string
    {
        $message = __(':count :word imported.', [
            'count' => $result['imported'],
            'word' => $result['imported'] === 1 ? 'location' : 'locations',
        ]);

        if ($result['duplicates'] > 0) {
            $message .= ' '.__(':count :word skipped as duplicate (already existed).', [
                'count' => $result['duplicates'],
                'word' => $result['duplicates'] === 1 ? 'entry' : 'entries',
            ]);
        }

        if ($result['truncated']) {
            $message .= ' '.__('Only the first :max rows in the file were processed.', ['max' => LocationCsvImporter::MAX_ROWS]);
        }

        return $message;
    }
}
