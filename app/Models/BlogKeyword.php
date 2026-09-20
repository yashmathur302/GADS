<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogKeyword extends Model
{
    protected $fillable = ['client_id', 'keyword'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
