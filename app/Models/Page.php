<?php

namespace App\Models;

use App\Support\UrlPath;
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

    /**
     * When an editor changes a page's URL, keep the old one working: add a 301 from the old
     * path and re-point any rule that already targeted it, so no chains form.
     */
    protected static function booted(): void
    {
        static::updating(function (Page $page) {
            if (! $page->isDirty('canonical_path')) {
                return;
            }
            $old = $page->getOriginal('canonical_path');
            $new = $page->canonical_path;

            Redirect::where('to_path', $old)->update(['to_path' => $new]);
            if (UrlPath::redirectKey($old) !== UrlPath::redirectKey($new)) {
                Redirect::updateOrCreate(
                    ['from_path' => UrlPath::redirectKey($old)],
                    ['to_path' => $new, 'status_code' => 301, 'is_active' => true, 'note' => 'Added automatically when the page URL changed'],
                );
            }
        });
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
