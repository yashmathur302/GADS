<?php

namespace App\Services;

use App\Models\Client;
use App\Services\GoogleAds\Data\MatchType;
use Illuminate\Http\UploadedFile;

/**
 * Parses an uploaded CSV of keywords and imports only the ones not already
 * present for the client, skipping duplicates — shared by the Keywords and
 * Negative Keywords import flows, which are identical except for which
 * relation on Client they write to.
 *
 * Accepts either a single "keyword" column (using Google Ads' own
 * [exact]/"phrase" bracket syntax for match type, same as the Keyword
 * Planner textarea) or two columns "Keyword,Match Type" — the shape this
 * app's own CSV export produces, so a round-trip export-then-reimport
 * works without editing the file.
 */
class KeywordCsvImporter
{
    public const MAX_ROWS = 5000;

    /**
     * @return array{imported: int, duplicates: int, truncated: bool}
     */
    public function import(UploadedFile $file, Client $client, string $relationName): array
    {
        $relation = $client->{$relationName}();

        $existing = $relation->get()
            ->map(fn ($row) => $this->dedupeKey($row->keyword, $row->match_type))
            ->flip();

        $rows = $this->parseFile($file);
        $truncated = count($rows) > self::MAX_ROWS;
        $rows = array_slice($rows, 0, self::MAX_ROWS);

        $imported = 0;
        $duplicates = 0;
        $seenInBatch = [];

        foreach ($rows as [$keyword, $matchType]) {
            $key = $this->dedupeKey($keyword, $matchType);

            if (isset($existing[$key]) || isset($seenInBatch[$key])) {
                $duplicates++;

                continue;
            }

            $seenInBatch[$key] = true;

            $relation->create(['keyword' => $keyword, 'match_type' => $matchType]);
            $imported++;
        }

        return ['imported' => $imported, 'duplicates' => $duplicates, 'truncated' => $truncated];
    }

    private function dedupeKey(string $keyword, MatchType|string $matchType): string
    {
        $value = $matchType instanceof MatchType ? $matchType->value : $matchType;

        return mb_strtolower(trim($keyword)).'|'.$value;
    }

    /**
     * @return array<int, array{0: string, 1: MatchType}>
     */
    private function parseFile(UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return [];
        }

        $isFirstRow = true;

        while (($row = fgetcsv($handle)) !== false) {
            $keyword = trim((string) ($row[0] ?? ''));

            if ($keyword === '') {
                continue;
            }

            if ($isFirstRow) {
                $isFirstRow = false;

                if (mb_strtolower($keyword) === 'keyword') {
                    continue;
                }
            }

            $explicitMatchType = isset($row[1])
                ? MatchType::tryFrom(ucfirst(mb_strtolower(trim($row[1]))))
                : null;

            $rows[] = $explicitMatchType !== null
                ? [$keyword, $explicitMatchType]
                : MatchType::parse($keyword);
        }

        fclose($handle);

        return $rows;
    }
}
