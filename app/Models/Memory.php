<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Memory extends Model
{
    protected $fillable = [
        'section',
        'type',
        'file_path',
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

    // Accessor untuk full URL file
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    // Accessor: apakah ini video?
    public function getIsVideoAttribute(): bool
    {
        return $this->type === 'video';
    }

    // Accessor: apakah ini foto?
    public function getIsPhotoAttribute(): bool
    {
        return $this->type === 'photo';
    }
}
