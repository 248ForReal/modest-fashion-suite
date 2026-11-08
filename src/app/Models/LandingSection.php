<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingSection extends Model
{
    protected $fillable = [
        'key', 'display_type', 'is_active', 'urutan',
        'eyebrow_en', 'eyebrow_id', 'title_en', 'title_id',
        'subtitle_en', 'subtitle_id', 'body_en', 'body_id',
        'cta_label_en', 'cta_label_id', 'cta_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'urutan'    => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(LandingSectionItem::class)->orderBy('urutan');
    }

    public function activeItems(): HasMany
    {
        return $this->hasMany(LandingSectionItem::class)->where('is_active', true)->orderBy('urutan');
    }

    public function eyebrow(?string $lang = null): ?string
    {
        $lang ??= app()->getLocale();
        return $lang === 'id' ? $this->eyebrow_id : $this->eyebrow_en;
    }

    public function title(?string $lang = null): ?string
    {
        $lang ??= app()->getLocale();
        return $lang === 'id' ? $this->title_id : $this->title_en;
    }

    public function subtitle(?string $lang = null): ?string
    {
        $lang ??= app()->getLocale();
        return $lang === 'id' ? $this->subtitle_id : $this->subtitle_en;
    }

    public function body(?string $lang = null): ?string
    {
        $lang ??= app()->getLocale();
        return $lang === 'id' ? $this->body_id : $this->body_en;
    }

    public function ctaLabel(?string $lang = null): ?string
    {
        $lang ??= app()->getLocale();
        return $lang === 'id' ? $this->cta_label_id : $this->cta_label_en;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }
}
