<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Memory extends Model
{
    protected $fillable = [
        'section',
        'title',
        'caption',
        'category',
        'chapter',
        'event_date',
        'order_index',
    ];

    protected $casts = [
        'event_date' => 'date',
        'order_index' => 'integer',
    ];

    // Relasi ke tabel memory_media
    public function media()
    {
        return $this->hasMany(MemoryMedia::class)->orderBy('order_index');
    }

    // Accessor helper: dapatkan media pertama sebagai fallback
    public function getFirstMediaAttribute()
    {
        return $this->media->first();
    }

    // Scope untuk filter per halaman/section
    public function scopeSection($query, string $section)
    {
        return $query->where('section', $section);
    }

    // Scope default order by order_index
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index')->orderBy('id');
    }

    // Accessor untuk full URL file (fallback)
    public function getFileUrlAttribute(): string
    {
        return $this->first_media?->file_url ?? '';
    }

    // Accessor: tipe media (photo/video) (fallback)
    public function getTypeAttribute(): string
    {
        return $this->first_media?->type ?? 'photo';
    }

    // Accessor: apakah ini link YouTube? (fallback)
    public function getIsYoutubeAttribute(): bool
    {
        return $this->first_media?->is_youtube ?? false;
    }

    // Accessor: dapatkan YouTube Video ID (fallback)
    public function getYoutubeIdAttribute(): ?string
    {
        return $this->first_media?->youtube_id;
    }

    // Accessor: dapatkan Thumbnail URL (fallback)
    public function getThumbnailUrlAttribute(): string
    {
        return $this->first_media?->thumbnail_url ?? '';
    }

    // Accessor: apakah ini video? (fallback)
    public function getIsVideoAttribute(): bool
    {
        return $this->first_media?->is_video ?? false;
    }

    // Accessor: apakah ini foto? (fallback)
    public function getIsPhotoAttribute(): bool
    {
        return $this->first_media?->is_photo ?? false;
    }
}
