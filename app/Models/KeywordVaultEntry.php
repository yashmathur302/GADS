<?php

namespace App\Models;

use App\Enums\KeywordVaultType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['industry_id', 'type', 'keyword', 'notes'])]
class KeywordVaultEntry extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => KeywordVaultType::class,
        ];
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }
}
