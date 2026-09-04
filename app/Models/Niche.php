<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['industry_id', 'name', 'slug'])]
class Niche extends Model
{
    use HasFactory;

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function keywordVaultEntries(): HasMany
    {
        return $this->hasMany(KeywordVaultEntry::class);
    }
}
