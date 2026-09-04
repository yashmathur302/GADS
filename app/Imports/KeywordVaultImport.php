<?php

namespace App\Imports;

use App\Enums\KeywordVaultType;
use App\Models\Industry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KeywordVaultImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $imported = 0;

    public function __construct(
        private readonly Industry $industry,
        private readonly KeywordVaultType $type,
    ) {}

    /**
     * Expects a "Keyword" column and an optional "Notes" column (matched
     * case-insensitively via WithHeadingRow). Existing keywords for this
     * industry/type are left alone rather than duplicated.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $keyword = trim((string) ($row['keyword'] ?? ''));

            if ($keyword === '') {
                continue;
            }

            $entry = $this->industry->keywordVaultEntries()->firstOrCreate(
                ['type' => $this->type, 'keyword' => $keyword],
                ['notes' => trim((string) ($row['notes'] ?? '')) ?: null]
            );

            if ($entry->wasRecentlyCreated) {
                $this->imported++;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            '*.keyword' => ['nullable', 'string', 'max:255'],
            '*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
