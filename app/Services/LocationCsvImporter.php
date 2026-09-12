<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Http\UploadedFile;

/**
 * Parses an uploaded CSV of target locations (one per row, or one per
 * line if plain text) and imports only the ones not already present for
 * the client, skipping duplicates.
 */
class LocationCsvImporter
{
    public const MAX_ROWS = 5000;

    /**
     * @return array{imported: int, duplicates: int, truncated: bool}
     */
    public function import(UploadedFile $file, Client $client): array
    {
        $existing = $client->locations()->get()
            ->map(fn ($row) => mb_strtolower(trim($row->location)))
            ->flip();

        $rows = $this->parseFile($file);
        $truncated = count($rows) > self::MAX_ROWS;
        $rows = array_slice($rows, 0, self::MAX_ROWS);

        $imported = 0;
        $duplicates = 0;
        $seenInBatch = [];

        foreach ($rows as $location) {
            $key = mb_strtolower(trim($location));

            if (isset($existing[$key]) || isset($seenInBatch[$key])) {
                $duplicates++;

                continue;
            }

            $seenInBatch[$key] = true;

            $client->locations()->create(['location' => $location]);
            $imported++;
        }

        return ['imported' => $imported, 'duplicates' => $duplicates, 'truncated' => $truncated];
    }

    /**
     * @return string[]
     */
    private function parseFile(UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return [];
        }

        $isFirstRow = true;

        // A location is always one whole value (e.g. "New York, NY") —
        // read raw lines rather than fgetcsv(), which would otherwise
        // split that value into two columns on the internal comma.
        while (($line = fgets($handle)) !== false) {
            $location = trim($line);

            // Allow (but don't require) a location to be wrapped in
            // quotes CSV-style, for anyone exporting from a spreadsheet
            // that quotes fields containing commas.
            if (strlen($location) >= 2 && $location[0] === '"' && str_ends_with($location, '"')) {
                $location = str_replace('""', '"', substr($location, 1, -1));
            }

            if ($location === '') {
                continue;
            }

            if ($isFirstRow) {
                $isFirstRow = false;

                if (mb_strtolower($location) === 'location') {
                    continue;
                }
            }

            $rows[] = $location;
        }

        fclose($handle);

        return $rows;
    }
}
