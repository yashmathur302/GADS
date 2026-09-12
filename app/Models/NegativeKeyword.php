<?php

namespace App\Models;

use App\Services\GoogleAds\Data\MatchType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NegativeKeyword extends Model
{
    protected $fillable = ['client_id', 'keyword', 'match_type'];

    protected function casts(): array
    {
        return [
            'match_type' => MatchType::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
