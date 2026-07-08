<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        // Ambil semua gallery items, encode ke JSON untuk dikonsumsi gallery.js
        $items = Memory::section('gallery')
            ->ordered()
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'type'       => $m->type,
                'file_url'   => $m->file_url,
                'title'      => $m->title,
                'caption'    => $m->caption,
                'category'   => $m->category,
                'event_date' => $m->event_date?->format('Y-m-d'),
            ]);

        return view('gallery', compact('items'));
    }
}
