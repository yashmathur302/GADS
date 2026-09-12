<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A client business under exactly one section — Keywords, Negative
 * Keywords, or Location. Each section has its own independent client list
 * (even if two clients share the same name across sections, they're
 * unrelated records), so deleting a client in one section never touches
 * another section's data.
 */
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    public const TYPE_KEYWORDS = 'keywords';

    public const TYPE_NEGATIVE_KEYWORDS = 'negative-keywords';

    public const TYPE_LOCATION = 'location';

    protected $fillable = ['name', 'industry_category', 'type'];

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    public function negativeKeywords(): HasMany
    {
        return $this->hasMany(NegativeKeyword::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function sectionIndexRouteName(): string
    {
        return match ($this->type) {
            self::TYPE_NEGATIVE_KEYWORDS => 'negative-keywords.index',
            self::TYPE_LOCATION => 'locations.index',
            default => 'keywords.index',
        };
    }

    public function sectionLabel(): string
    {
        return match ($this->type) {
            self::TYPE_NEGATIVE_KEYWORDS => __('negative keyword list'),
            self::TYPE_LOCATION => __('location list'),
            default => __('keyword list'),
        };
    }
}
