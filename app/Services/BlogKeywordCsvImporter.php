<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Http\UploadedFile;

/**
 * Parses an uploaded CSV of blog keyword phrases (one per row, or one per
 * line if plain text) and imports only the ones not already present for
 * the client, skipping duplicates.
 */
class BlogKeywordCsvImporter
{
    public const MAX_ROWS = 5000;

    /**
     * @return array{imported: int, duplicates: int, truncated: bool}
     */
    public function import(UploadedFile $file, Client $client): array
    {
        $existing = $client->blogKeywords()->get()
            ->map(fn ($row) => mb_strtolower(trim($row->keyword)))
            ->flip();

        $rows = $this->parseFile($file);
        $truncated = count($rows) > self::MAX_ROWS;
        $rows = array_slice($rows, 0, self::MAX_ROWS);

        $imported = 0;
        $duplicates = 0;
        $seenInBatch = [];

        foreach ($rows as $keyword) {
            $key = mb_strtolower(trim($keyword));

            if (isset($existing[$key]) || isset($seenInBatch[$key])) {
                $duplicates++;

                continue;
            }

            $seenInBatch[$key] = true;

            $client->blogKeywords()->create(['keyword' => $keyword]);
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

        // A blog keyword phrase can contain a comma (e.g. "plumbing tips,
        // tricks") — read raw lines rather than fgetcsv(), which would
        // otherwise split that value into two columns.
        while (($line = fgets($handle)) !== false) {
            $keyword = trim($line);

            // Allow (but don't require) a value to be wrapped in quotes
            // CSV-style, for anyone exporting from a spreadsheet that
            // quotes fields containing commas.
            if (strlen($keyword) >= 2 && $keyword[0] === '"' && str_ends_with($keyword, '"')) {
                $keyword = str_replace('""', '"', substr($keyword, 1, -1));
            }

            if ($keyword === '') {
                continue;
            }

            if ($isFirstRow) {
                $isFirstRow = false;

                if (mb_strtolower($keyword) === 'keyword') {
                    continue;
                }
            }

            $rows[] = $keyword;
        }

        fclose($handle);

        return $rows;
    }
}
