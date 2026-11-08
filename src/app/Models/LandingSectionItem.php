<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingSectionItem extends Model
{
    protected $fillable = [
        'landing_section_id', 'is_active', 'urutan', 'image_path',
        'label_en', 'label_id', 'title_en', 'title_id',
        'body_en', 'body_id', 'link_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'urutan'    => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(LandingSection::class, 'landing_section_id');
    }

    public function label(?string $lang = null): ?string
    {
        $lang ??= app()->getLocale();
        return $lang === 'id' ? $this->label_id : $this->label_en;
    }

    public function title(?string $lang = null): ?string
    {
        $lang ??= app()->getLocale();
        return $lang === 'id' ? $this->title_id : $this->title_en;
    }

    public function body(?string $lang = null): ?string
    {
        $lang ??= app()->getLocale();
        return $lang === 'id' ? $this->body_id : $this->body_en;
    }

    public function imageUrl(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }
        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
