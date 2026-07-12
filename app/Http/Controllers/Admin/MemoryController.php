<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Memory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MemoryController extends Controller
{
    /**
     * Tampilkan semua memory dikelompokkan per section.
     */
    public function index(): View
    {
        $milestones = Memory::with('media')->section('milestone')->ordered()->get();
        $branches   = Memory::with('media')->section('branch')->ordered()->get();
        $galleries  = Memory::with('media')->section('gallery')->ordered()->get();

        return view('admin.memories.index', compact('milestones', 'branches', 'galleries'));
    }

    /**
     * Form tambah memory baru.
     */
    public function create(): View
    {
        return view('admin.memories.create');
    }

    /**
     * Simpan memory baru ke database dan storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file'        => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm', 'max:20480'],
            'files'       => ['nullable', 'array'],
            'files.*'     => ['file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm', 'max:20480'],
            'youtube_url' => ['nullable', 'url'],
            'direct_url'  => ['nullable', 'url'],
            'media_type'  => ['nullable', 'in:photo,video'],
            'section'     => ['required', 'in:milestone,branch,gallery'],
            'title'       => ['nullable', 'string', 'max:255'],
            'caption'     => ['nullable', 'string'],
            'category'    => ['nullable', 'string', 'max:255'],
            'chapter'     => ['nullable', 'string', 'max:255'],
            'event_date'  => ['nullable', 'date'],
        ]);

        $section = $validated['section'];
        $maxOrder = Memory::section($section)->max('order_index') ?? -1;
        $maxOrder++;

        if ($section === 'gallery') {
            // Untuk gallery: satu item = satu record Memory terpisah
            if (!empty($validated['direct_url'])) {
                $memory = Memory::create([
                    'section'     => $section,
                    'title'       => $validated['title'] ?? null,
                    'caption'     => $validated['caption'] ?? null,
                    'category'    => $validated['category'] ?? null,
                    'chapter'     => $validated['chapter'] ?? null,
                    'event_date'  => $validated['event_date'] ?? null,
                    'order_index' => $maxOrder++,
                ]);
                $memory->media()->create([
                    'file_path'   => $validated['direct_url'],
                    'type'        => $request->input('media_type', 'photo'),
                    'order_index' => 0,
                ]);
            }
            
            if (!empty($validated['youtube_url'])) {
                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $validated['youtube_url'], $match)) {
                    $memory = Memory::create([
                        'section'     => $section,
                        'title'       => $validated['title'] ?? null,
                        'caption'     => $validated['caption'] ?? null,
                        'category'    => $validated['category'] ?? null,
                        'chapter'     => $validated['chapter'] ?? null,
                        'event_date'  => $validated['event_date'] ?? null,
                        'order_index' => $maxOrder++,
                    ]);
                    $memory->media()->create([
                        'file_path'   => $validated['youtube_url'],
                        'type'        => 'video',
                        'order_index' => 0,
                    ]);
                }
            }

            $filesToUpload = [];
            if ($request->hasFile('file')) {
                $filesToUpload[] = $request->file('file');
            } elseif ($request->hasFile('files')) {
                $filesToUpload = $request->file('files');
            }

            $disk = config('filesystems.default');
            foreach ($filesToUpload as $file) {
                $mime = $file->getMimeType();
                $type = str_starts_with($mime, 'video/') ? 'video' : 'photo';
                $path = $file->store('memories', $disk);

                $memory = Memory::create([
                    'section'     => $section,
                    'title'       => $validated['title'] ?? null,
                    'caption'     => $validated['caption'] ?? null,
                    'category'    => $validated['category'] ?? null,
                    'chapter'     => $validated['chapter'] ?? null,
                    'event_date'  => $validated['event_date'] ?? null,
                    'order_index' => $maxOrder++,
                ]);
                $memory->media()->create([
                    'file_path'   => $path,
                    'type'        => $type,
                    'order_index' => 0,
                ]);
            }

            return redirect()->route('admin.memories.index')
                ->with('success', 'Item galeri berhasil ditambahkan!');
        } else {
            // Untuk milestone dan branch: satukan semua media dalam 1 record Memory (carousel)
            $memory = Memory::create([
                'section'     => $section,
                'title'       => $validated['title'] ?? null,
                'caption'     => $validated['caption'] ?? null,
                'category'    => $validated['category'] ?? null,
                'chapter'     => $validated['chapter'] ?? null,
                'event_date'  => $validated['event_date'] ?? null,
                'order_index' => $maxOrder,
            ]);

            $mediaTypes = $request->input('media_types', []);
            $mediaUrls = $request->input('media_urls', []);
            $mediaDirectTypes = $request->input('media_direct_types', []);
            $mediaFileIndices = $request->input('media_file_indices', []);
            
            $disk = config('filesystems.default');
            $order = 0;

            foreach ($mediaTypes as $index => $type) {
                $filePath = '';
                $mediaType = 'photo';
                
                if ($type === 'file') {
                    $fileIndex = $mediaFileIndices[$index] ?? null;
                    if ($fileIndex !== null && $request->hasFile("files.{$fileIndex}")) {
                        $file = $request->file("files.{$fileIndex}");
                        $mime = $file->getMimeType();
                        $mediaType = str_starts_with($mime, 'video/') ? 'video' : 'photo';
                        $filePath = $file->store('memories', $disk);
                    } else {
                        continue;
                    }
                } elseif ($type === 'youtube') {
                    $url = $mediaUrls[$index] ?? '';
                    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
                        $filePath = $url;
                        $mediaType = 'video';
                    } else {
                        continue;
                    }
                } elseif ($type === 'direct') {
                    $url = $mediaUrls[$index] ?? '';
                    if (!empty($url)) {
                        $filePath = $url;
                        $mediaType = $mediaDirectTypes[$index] ?? 'photo';
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
                
                $memory->media()->create([
                    'file_path'   => $filePath,
                    'type'        => $mediaType,
                    'order_index' => $order++,
                ]);
            }

            // Fallback jika tidak ada item media terstruktur yang dikirim
            if ($memory->media()->count() === 0) {
                $disk = config('filesystems.default');
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $mime = $file->getMimeType();
                    $mediaType = str_starts_with($mime, 'video/') ? 'video' : 'photo';
                    $path = $file->store('memories', $disk);
                    $memory->media()->create([
                        'file_path' => $path,
                        'type' => $mediaType,
                        'order_index' => 0,
                    ]);
                } elseif (!empty($validated['youtube_url'])) {
                    $memory->media()->create([
                        'file_path' => $validated['youtube_url'],
                        'type' => 'video',
                        'order_index' => 0,
                    ]);
                } elseif (!empty($validated['direct_url'])) {
                    $memory->media()->create([
                        'file_path' => $validated['direct_url'],
                        'type' => $request->input('media_type', 'photo'),
                        'order_index' => 0,
                    ]);
                }
            }

            return redirect()->route('admin.memories.index')
                ->with('success', 'Milestone/Bab berhasil ditambahkan!');
        }
    }

    /**
     * Form edit memory.
     */
    public function edit(Memory $memory): View
    {
        $memory->load('media');
        return view('admin.memories.edit', compact('memory'));
    }

    /**
     * Update memory di database.
     */
    public function update(Request $request, Memory $memory): RedirectResponse
    {
        $validated = $request->validate([
            'file'        => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm', 'max:20480'],
            'files'       => ['nullable', 'array'],
            'files.*'     => ['file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm', 'max:20480'],
            'youtube_url' => ['nullable', 'url'],
            'direct_url'  => ['nullable', 'url'],
            'media_type'  => ['nullable', 'in:photo,video'],
            'section'     => ['required', 'in:milestone,branch,gallery'],
            'title'       => ['nullable', 'string', 'max:255'],
            'caption'     => ['nullable', 'string'],
            'category'    => ['nullable', 'string', 'max:255'],
            'chapter'     => ['nullable', 'string', 'max:255'],
            'event_date'  => ['nullable', 'date'],
        ]);

        $memory->update([
            'section'    => $validated['section'],
            'title'      => $validated['title'] ?? null,
            'caption'    => $validated['caption'] ?? null,
            'category'   => $validated['category'] ?? null,
            'chapter'    => $validated['chapter'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
        ]);

        $disk = config('filesystems.default');

        if ($validated['section'] === 'gallery') {
            // Update single media untuk gallery
            $mediaItem = $memory->media()->first();
            
            if (!empty($validated['direct_url'])) {
                if ($mediaItem && !filter_var($mediaItem->file_path, FILTER_VALIDATE_URL)) {
                    Storage::disk($disk)->delete($mediaItem->file_path);
                }
                $memory->media()->updateOrCreate(
                    ['id' => $mediaItem?->id ?? 0],
                    [
                        'file_path'   => $validated['direct_url'],
                        'type'        => $request->input('media_type', 'photo'),
                        'order_index' => 0,
                    ]
                );
            } elseif (!empty($validated['youtube_url'])) {
                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $validated['youtube_url'], $match)) {
                    if ($mediaItem && !filter_var($mediaItem->file_path, FILTER_VALIDATE_URL)) {
                        Storage::disk($disk)->delete($mediaItem->file_path);
                    }
                    $memory->media()->updateOrCreate(
                        ['id' => $mediaItem?->id ?? 0],
                        [
                            'file_path'   => $validated['youtube_url'],
                            'type'        => 'video',
                            'order_index' => 0,
                        ]
                    );
                }
            } elseif ($request->hasFile('file')) {
                if ($mediaItem && !filter_var($mediaItem->file_path, FILTER_VALIDATE_URL)) {
                    Storage::disk($disk)->delete($mediaItem->file_path);
                }
                $mime = $request->file('file')->getMimeType();
                $type = str_starts_with($mime, 'video/') ? 'video' : 'photo';
                $path = $request->file('file')->store('memories', $disk);
                $memory->media()->updateOrCreate(
                    ['id' => $mediaItem?->id ?? 0],
                    [
                        'file_path'   => $path,
                        'type'        => $type,
                        'order_index' => 0,
                    ]
                );
            }
        } else {
            // Update multi media untuk milestones dan branches
            $mediaIds = $request->input('media_ids', []);
            $mediaTypes = $request->input('media_types', []);
            $mediaUrls = $request->input('media_urls', []);
            $mediaDirectTypes = $request->input('media_direct_types', []);
            $mediaFileIndices = $request->input('media_file_indices', []);

            // 1. Hapus media yang di-delete oleh user
            $currentMedia = $memory->media;
            foreach ($currentMedia as $item) {
                if (!in_array($item->id, $mediaIds)) {
                    if (!filter_var($item->file_path, FILTER_VALIDATE_URL)) {
                        Storage::disk($disk)->delete($item->file_path);
                    }
                    $item->delete();
                }
            }

            // 2. Tambahkan/update media
            $order = 0;
            foreach ($mediaTypes as $index => $type) {
                $mediaId = $mediaIds[$index] ?? null;
                
                if (!empty($mediaId)) {
                    $item = \App\Models\MemoryMedia::find($mediaId);
                    if ($item) {
                        $item->update(['order_index' => $order++]);
                    }
                } else {
                    $filePath = '';
                    $mediaType = 'photo';
                    
                    if ($type === 'file') {
                        $fileIndex = $mediaFileIndices[$index] ?? null;
                        if ($fileIndex !== null && $request->hasFile("files.{$fileIndex}")) {
                            $file = $request->file("files.{$fileIndex}");
                            $mime = $file->getMimeType();
                            $mediaType = str_starts_with($mime, 'video/') ? 'video' : 'photo';
                            $filePath = $file->store('memories', $disk);
                        } else {
                            continue;
                        }
                    } elseif ($type === 'youtube') {
                        $url = $mediaUrls[$index] ?? '';
                        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
                            $filePath = $url;
                            $mediaType = 'video';
                        } else {
                            continue;
                        }
                    } elseif ($type === 'direct') {
                        $url = $mediaUrls[$index] ?? '';
                        if (!empty($url)) {
                            $filePath = $url;
                            $mediaType = $mediaDirectTypes[$index] ?? 'photo';
                        } else {
                            continue;
                        }
                    } else {
                        continue;
                    }

                    $memory->media()->create([
                        'file_path'   => $filePath,
                        'type'        => $mediaType,
                        'order_index' => $order++,
                    ]);
                }
            }
        }

        return redirect()->route('admin.memories.index')
            ->with('success', 'Memory berhasil diperbarui!');
    }

    /**
     * Hapus memory dari database dan file dari storage.
     */
    public function destroy(Memory $memory): RedirectResponse
    {
        $disk = config('filesystems.default');

        foreach ($memory->media as $item) {
            if (!filter_var($item->file_path, FILTER_VALIDATE_URL)) {
                Storage::disk($disk)->delete($item->file_path);
            }
        }

        $memory->delete();

        return redirect()->route('admin.memories.index')
            ->with('success', 'Memory berhasil dihapus!');
    }

    /**
     * Update order_index massal via AJAX (drag-and-drop reorder).
     * Menerima: { section: 'milestone', ids: [3,1,5,2] }
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            Memory::where('id', $id)->update(['order_index' => $index]);
        }

        return response()->json(['status' => 'ok']);
    }
}
