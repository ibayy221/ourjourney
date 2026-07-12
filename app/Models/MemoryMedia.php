<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MemoryMedia extends Model
{
    protected $table = 'memory_media';

    protected $fillable = [
        'memory_id',
        'file_path',
        'type',
        'order_index',
    ];

    public function memory()
    {
        return $this->belongsTo(Memory::class);
    }

    // Accessor untuk full URL file
    public function getFileUrlAttribute(): string
    {
        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }
        return Storage::url($this->file_path);
    }

    // Accessor: apakah ini link YouTube?
    public function getIsYoutubeAttribute(): bool
    {
        return $this->type === 'video' && (
            str_contains($this->file_path, 'youtube.com') ||
            str_contains($this->file_path, 'youtu.be')
        );
    }

    // Accessor: dapatkan YouTube Video ID
    public function getYoutubeIdAttribute(): ?string
    {
        if (!$this->is_youtube) {
            return null;
        }
        
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $this->file_path, $match)) {
            return $match[1];
        }
        
        return null;
    }

    // Accessor: dapatkan Thumbnail URL
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->is_youtube) {
            $youtubeId = $this->youtube_id;
            return "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg";
        }
        
        return $this->file_url;
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
