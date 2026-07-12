<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    /**
     * Tampilkan semua wishlist dikelompokkan berdasarkan status selesai.
     */
    public function index(): View
    {
        $activeItems = Wishlist::where('is_completed', false)->ordered()->get();
        $completedItems = Wishlist::where('is_completed', true)->orderByDesc('completed_at')->orderBy('order_index')->get();

        return view('admin.wishlists.index', compact('activeItems', 'completedItems'));
    }

    /**
     * Form tambah wishlist baru.
     */
    public function create(): View
    {
        return view('admin.wishlists.create');
    }

    /**
     * Simpan wishlist baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $maxOrder = Wishlist::where('is_completed', false)->max('order_index') ?? -1;

        Wishlist::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_completed'=> false,
            'order_index' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.wishlists.index')
            ->with('success', 'Wishlist berhasil ditambahkan!');
    }

    /**
     * Form edit wishlist.
     */
    public function edit(Wishlist $wishlist): View
    {
        return view('admin.wishlists.edit', compact('wishlist'));
    }

    /**
     * Update wishlist di database.
     */
    public function update(Request $request, Wishlist $wishlist): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'is_completed' => ['required', 'boolean'],
            'completed_at' => ['nullable', 'required_if:is_completed,1', 'date'],
        ]);

        $isCompleted = (bool) $validated['is_completed'];

        $updateData = [
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? ($validated['completed_at'] ?? now()->format('Y-m-d')) : null,
        ];

        $wishlist->update($updateData);

        return redirect()->route('admin.wishlists.index')
            ->with('success', 'Wishlist berhasil diperbarui!');
    }

    /**
     * Hapus wishlist dari database.
     */
    public function destroy(Wishlist $wishlist): RedirectResponse
    {
        $wishlist->delete();

        return redirect()->route('admin.wishlists.index')
            ->with('success', 'Wishlist berhasil dihapus!');
    }

    /**
     * Toggle status is_completed secara instan dari list.
     */
    public function toggle(Wishlist $wishlist): RedirectResponse
    {
        $newStatus = !$wishlist->is_completed;
        
        $wishlist->update([
            'is_completed' => $newStatus,
            'completed_at' => $newStatus ? now()->format('Y-m-d') : null,
        ]);

        $message = $newStatus ? 'Impian berhasil dicapai! 🎉' : 'Impian dikembalikan ke daftar rencana. 🌱';

        return redirect()->route('admin.wishlists.index')->with('success', $message);
    }

    /**
     * Update order_index massal via AJAX (drag-and-drop reorder).
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            Wishlist::where('id', $id)->update(['order_index' => $index]);
        }

        return response()->json(['status' => 'ok']);
    }
}
