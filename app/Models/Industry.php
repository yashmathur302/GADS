<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    public function niches(): HasMany
    {
        return $this->hasMany(Niche::class);
    }

    /**
     * All keywords across every niche in this industry — used for the
     * industry-level count on the vault index page.
     */
    public function keywordVaultEntries(): HasManyThrough
    {
        return $this->hasManyThrough(KeywordVaultEntry::class, Niche::class);
    }
}
