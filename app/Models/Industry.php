<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug'])]
class Industry extends Model
{
    use HasFactory;

    /**
     * Route model binding resolves industries by slug, not id.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function keywordVaultEntries(): HasMany
    {
        return $this->hasMany(KeywordVaultEntry::class);
    }
}
