<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        // Ambil semua gallery items yang bertipe photo (foto saja)
        $items = Memory::section('gallery')
            ->where('type', 'photo')
            ->ordered()
            ->get()
            ->map(fn($m) => [
                'id'            => $m->id,
                'type'          => $m->type,
                'file_url'      => $m->file_url,
                'title'         => $m->title,
                'caption'       => $m->caption,
                'category'      => $m->category,
                'event_date'    => $m->event_date?->format('Y-m-d'),
                'is_youtube'    => $m->is_youtube,
                'youtube_id'    => $m->youtube_id,
                'thumbnail_url' => $m->thumbnail_url,
            ]);

        return view('gallery', compact('items'));
    }

    public function cinema(): View
    {
        // Ambil semua gallery items yang bertipe video (video lokal / youtube)
        $items = Memory::section('gallery')
            ->where('type', 'video')
            ->ordered()
            ->get()
            ->map(fn($m) => [
                'id'            => $m->id,
                'type'          => $m->type,
                'file_url'      => $m->file_url,
                'title'         => $m->title,
                'caption'       => $m->caption,
                'category'      => $m->category,
                'event_date'    => $m->event_date?->format('Y-m-d'),
                'is_youtube'    => $m->is_youtube,
                'youtube_id'    => $m->youtube_id,
                'thumbnail_url' => $m->thumbnail_url,
            ]);

        return view('cinema', compact('items'));
    }
}
