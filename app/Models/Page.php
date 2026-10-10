<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasFactory;

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_DRAFT = 'draft';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'wp_modified_at' => 'datetime',
            'data' => 'array',
        ];
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->orderBy('position');
    }

    /** Only published content is visible to the public. Drafts return 404. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function isNoindex(): bool
    {
        return $this->robots !== null && str_contains($this->robots, 'noindex');
    }

    /** Canonical URL for the <link rel="canonical"> tag. */
    public function canonicalUrl(): string
    {
        if ($this->canonical_override) {
            return $this->canonical_override;
        }

        return rtrim(config('app.url'), '/').$this->canonical_path;
    }
}
