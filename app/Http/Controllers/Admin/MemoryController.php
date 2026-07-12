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
        $milestones = Memory::section('milestone')->ordered()->get();
        $branches   = Memory::section('branch')->ordered()->get();
        $galleries  = Memory::section('gallery')->ordered()->get();

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

        if (!$request->hasFile('file') && !$request->hasFile('files') && empty($validated['youtube_url']) && empty($validated['direct_url'])) {
            return back()->withErrors(['file' => 'Pilih setidaknya satu file untuk diupload, isi link YouTube, atau masukkan URL media langsung.'])->withInput();
        }

        $section = $validated['section'];
        $maxOrder = Memory::section($section)->max('order_index') ?? -1;

        if (!empty($validated['direct_url'])) {
            $maxOrder++;
            Memory::create([
                'section'     => $section,
                'type'        => $request->input('media_type', 'photo'),
                'file_path'   => $validated['direct_url'],
                'title'       => $validated['title'] ?? null,
                'caption'     => $validated['caption'] ?? null,
                'category'    => $validated['category'] ?? null,
                'chapter'     => $validated['chapter'] ?? null,
                'event_date'  => $validated['event_date'] ?? null,
                'order_index' => $maxOrder,
            ]);

            return redirect()->route('admin.memories.index')
                ->with('success', 'Media URL langsung berhasil ditambahkan!');
        }

        if (!empty($validated['youtube_url'])) {
            // Regex to check if it's a valid YouTube link
            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $validated['youtube_url'], $match)) {
                $maxOrder++;
                Memory::create([
                    'section'     => $section,
                    'type'        => 'video',
                    'file_path'   => $validated['youtube_url'],
                    'title'       => $validated['title'] ?? null,
                    'caption'     => $validated['caption'] ?? null,
                    'category'    => $validated['category'] ?? null,
                    'chapter'     => $validated['chapter'] ?? null,
                    'event_date'  => $validated['event_date'] ?? null,
                    'order_index' => $maxOrder,
                ]);
                
                return redirect()->route('admin.memories.index')
                    ->with('success', 'Video YouTube berhasil ditambahkan!');
            } else {
                return back()->withErrors(['youtube_url' => 'Format URL YouTube tidak valid.'])->withInput();
            }
        }

        // Process files
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

            $maxOrder++;

            Memory::create([
                'section'     => $section,
                'type'        => $type,
                'file_path'   => $path,
                'title'       => $validated['title'] ?? null,
                'caption'     => $validated['caption'] ?? null,
                'category'    => $validated['category'] ?? null,
                'chapter'     => $validated['chapter'] ?? null,
                'event_date'  => $validated['event_date'] ?? null,
                'order_index' => $maxOrder,
            ]);
        }

        $count = count($filesToUpload);
        return redirect()->route('admin.memories.index')
            ->with('success', "{$count} Memory berhasil ditambahkan!");
    }

    /**
     * Form edit memory.
     */
    public function edit(Memory $memory): View
    {
        return view('admin.memories.edit', compact('memory'));
    }

    /**
     * Update memory di database.
     */
    public function update(Request $request, Memory $memory): RedirectResponse
    {
        $validated = $request->validate([
            'file'        => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm', 'max:20480'],
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

        $updateData = [
            'section'    => $validated['section'],
            'title'      => $validated['title'] ?? null,
            'caption'    => $validated['caption'] ?? null,
            'category'   => $validated['category'] ?? null,
            'chapter'    => $validated['chapter'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
        ];

        $disk = config('filesystems.default');

        if (!empty($validated['direct_url'])) {
            // Delete old local file if previous file was local
            if (!filter_var($memory->file_path, FILTER_VALIDATE_URL)) {
                Storage::disk($disk)->delete($memory->file_path);
            }
            $updateData['type'] = $request->input('media_type', 'photo');
            $updateData['file_path'] = $validated['direct_url'];
        } elseif (!empty($validated['youtube_url'])) {
            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $validated['youtube_url'], $match)) {
                // Delete old local file if previous file was local
                if (!filter_var($memory->file_path, FILTER_VALIDATE_URL)) {
                    Storage::disk($disk)->delete($memory->file_path);
                }
                $updateData['type'] = 'video';
                $updateData['file_path'] = $validated['youtube_url'];
            } else {
                return back()->withErrors(['youtube_url' => 'Format URL YouTube tidak valid.'])->withInput();
            }
        } elseif ($request->hasFile('file')) {
            // Delete old local file if previous file was local
            if (!filter_var($memory->file_path, FILTER_VALIDATE_URL)) {
                Storage::disk($disk)->delete($memory->file_path);
            }

            $mime = $request->file('file')->getMimeType();
            $updateData['type']      = str_starts_with($mime, 'video/') ? 'video' : 'photo';
            $updateData['file_path'] = $request->file('file')->store('memories', $disk);
        }

        $memory->update($updateData);

        return redirect()->route('admin.memories.index')
            ->with('success', 'Memory berhasil diperbarui!');
    }

    /**
     * Hapus memory dari database dan file dari storage.
     */
    public function destroy(Memory $memory): RedirectResponse
    {
        $disk = config('filesystems.default');

        // Hapus file dari storage jika merupakan file lokal
        if (!filter_var($memory->file_path, FILTER_VALIDATE_URL)) {
            Storage::disk($disk)->delete($memory->file_path);
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
