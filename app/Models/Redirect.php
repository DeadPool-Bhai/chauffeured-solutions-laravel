<?php

namespace App\Models;

use App\Support\UrlPath;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_hit_at' => 'datetime',
        ];
    }

    /** Normalise the source on save so lookups are a single indexed equality check. */
    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect) {
            $redirect->from_path = UrlPath::redirectKey($redirect->from_path);
        });
    }

    public function isGone(): bool
    {
        return $this->status_code === 410;
    }
}
