<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A client business the admin manages keyword/negative-keyword lists for.
 * Shared between the Keywords and Negative Keywords tabs — a client created
 * from either page shows up in both, so the same business is never entered
 * twice under two disconnected records.
 */
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    protected $fillable = ['name', 'industry_category'];

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    public function negativeKeywords(): HasMany
    {
        return $this->hasMany(NegativeKeyword::class);
    }
}
