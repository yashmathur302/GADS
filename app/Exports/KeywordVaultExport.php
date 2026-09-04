<?php

namespace App\Exports;

use App\Models\KeywordVaultEntry;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KeywordVaultExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, KeywordVaultEntry>  $entries
     */
    public function __construct(private readonly Collection $entries) {}

    public function collection(): Collection
    {
        return $this->entries;
    }

    public function headings(): array
    {
        return ['Keyword', 'Notes'];
    }

    /**
     * @param  KeywordVaultEntry  $entry
     */
    public function map($entry): array
    {
        return [$entry->keyword, $entry->notes];
    }
}
