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
            'file'       => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm', 'max:20480'],
            'section'    => ['required', 'in:milestone,branch,gallery'],
            'title'      => ['nullable', 'string', 'max:255'],
            'caption'    => ['nullable', 'string'],
            'category'   => ['nullable', 'string', 'max:255'],
            'chapter'    => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
        ]);

        // Tentukan type berdasarkan mime type file
        $mime = $request->file('file')->getMimeType();
        $type = str_starts_with($mime, 'video/') ? 'video' : 'photo';

        // Simpan file ke storage/app/public/memories/
        $path = $request->file('file')->store('memories', 'public');

        // Hitung order_index berikutnya untuk section ini
        $maxOrder = Memory::section($validated['section'])->max('order_index') ?? -1;

        Memory::create([
            'section'     => $validated['section'],
            'type'        => $type,
            'file_path'   => $path,
            'title'       => $validated['title'] ?? null,
            'caption'     => $validated['caption'] ?? null,
            'category'    => $validated['category'] ?? null,
            'chapter'     => $validated['chapter'] ?? null,
            'event_date'  => $validated['event_date'] ?? null,
            'order_index' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.memories.index')
            ->with('success', 'Memory berhasil ditambahkan!');
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
            'file'       => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm', 'max:20480'],
            'section'    => ['required', 'in:milestone,branch,gallery'],
            'title'      => ['nullable', 'string', 'max:255'],
            'caption'    => ['nullable', 'string'],
            'category'   => ['nullable', 'string', 'max:255'],
            'chapter'    => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
        ]);

        $updateData = [
            'section'    => $validated['section'],
            'title'      => $validated['title'] ?? null,
            'caption'    => $validated['caption'] ?? null,
            'category'   => $validated['category'] ?? null,
            'chapter'    => $validated['chapter'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
        ];

        // Jika ada file baru, hapus file lama dan simpan yang baru
        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($memory->file_path);

            $mime = $request->file('file')->getMimeType();
            $updateData['type']      = str_starts_with($mime, 'video/') ? 'video' : 'photo';
            $updateData['file_path'] = $request->file('file')->store('memories', 'public');
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
        // Hapus file dari storage
        Storage::disk('public')->delete($memory->file_path);

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
