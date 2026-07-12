@extends('layouts.admin')
@section('title', 'Kelola Bucket List')

@section('content')

{{-- Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kelola Bucket List</h1>
        <p class="text-sm text-gray-500 mt-1">Daftar impian dan rencana perjalanan bersama Putri Daun & Ubay</p>
    </div>
    <a href="{{ route('admin.wishlists.create') }}"
       class="inline-flex items-center gap-2 rounded-lg bg-green-deep hover:bg-green-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Impian
    </a>
</div>

{{-- Tabs --}}
<div x-data="{ tab: 'active' }" class="space-y-6">

    {{-- Tab Nav --}}
    <div class="flex gap-1 rounded-xl bg-gray-100 p-1 w-fit">
        <button @click="tab = 'active'"
                :class="tab === 'active' ? 'bg-white shadow text-green-deep font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="rounded-lg px-5 py-2 text-sm transition-all">
            Rencana Impian 🌱 ({{ $activeItems->count() }})
        </button>
        <button @click="tab = 'completed'"
                :class="tab === 'completed' ? 'bg-white shadow text-green-deep font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="rounded-lg px-5 py-2 text-sm transition-all">
            Sudah Tercapai 🌸 ({{ $completedItems->count() }})
        </button>
    </div>

    {{-- ── ACTIVE ITEMS ── --}}
    <div x-show="tab === 'active'" x-cloak>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @if($activeItems->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <span class="text-3xl mb-3">🌱</span>
                    <p class="text-sm">Belum ada impian aktif. Tambahkan impian pertama kalian!</p>
                    <a href="{{ route('admin.wishlists.create') }}" class="mt-4 text-sm text-green-deep hover:underline font-medium">
                        + Tambah sekarang
                    </a>
                </div>
            @else
                <ul data-sortable="wishlist" class="divide-y divide-gray-50">
                    @foreach($activeItems as $item)
                    <li data-id="{{ $item->id }}"
                        class="flex items-center gap-4 px-4 py-4 hover:bg-gray-50 transition-colors group">

                        {{-- Drag Handle --}}
                        <div class="drag-handle cursor-grab text-gray-300 hover:text-gray-500 flex-shrink-0 select-none">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                            </svg>
                        </div>

                        {{-- Checkbox / Toggle Button --}}
                        <form action="{{ route('admin.wishlists.toggle', $item) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="w-6 h-6 rounded-full border-2 border-gray-300 hover:border-green-soft flex items-center justify-center transition-colors group/btn" title="Tandai Sudah Tercapai">
                                <span class="w-3 h-3 rounded-full bg-green-soft opacity-0 group-hover/btn:opacity-50 transition-opacity"></span>
                            </button>
                        </form>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $item->title }}</h3>
                            @if($item->description)
                                <p class="text-xs text-gray-500 mt-1 truncate">{{ $item->description }}</p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('admin.wishlists.edit', $item) }}"
                               class="rounded-lg bg-gray-100 hover:bg-gray-200 p-2 text-gray-600 transition-colors"
                               title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button type="button"
                                    onclick="confirmDelete('{{ route('admin.wishlists.destroy', $item) }}', '{{ addslashes($item->title) }}')"
                                    class="rounded-lg bg-red-50 hover:bg-red-100 p-2 text-red-500 transition-colors"
                                    title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- ── COMPLETED ITEMS ── --}}
    <div x-show="tab === 'completed'" x-cloak>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @if($completedItems->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <span class="text-3xl mb-3">🌸</span>
                    <p class="text-sm">Belum ada impian yang tercapai. Yuk, wujudkan mimpi kalian!</p>
                </div>
            @else
                <ul class="divide-y divide-gray-50">
                    @foreach($completedItems as $item)
                    <li class="flex items-center gap-4 px-4 py-4 hover:bg-gray-50 transition-colors group">

                        {{-- Checkbox / Toggle Button --}}
                        <form action="{{ route('admin.wishlists.toggle', $item) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="w-6 h-6 rounded-full border-2 border-green-soft bg-green-50 flex items-center justify-center text-green-soft transition-colors" title="Kembalikan ke Rencana">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            </button>
                        </form>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-500 line-through truncate">{{ $item->title }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                    ✓ Tercapai pada {{ $item->completed_at ? $item->completed_at->translatedFormat('d F Y') : '-' }}
                                </span>
                                @if($item->description)
                                    <span class="text-xs text-gray-400 truncate max-w-xs">— {{ $item->description }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('admin.wishlists.edit', $item) }}"
                               class="rounded-lg bg-gray-100 hover:bg-gray-200 p-2 text-gray-600 transition-colors"
                               title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button type="button"
                                    onclick="confirmDelete('{{ route('admin.wishlists.destroy', $item) }}', '{{ addslashes($item->title) }}')"
                                    class="rounded-lg bg-red-50 hover:bg-red-100 p-2 text-red-500 transition-colors"
                                    title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

</div>

{{-- Modal Konfirmasi Hapus --}}
<div x-data="deleteModal()"
     x-show="open"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
     @keydown.escape.window="open = false">

    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4"
         @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">Hapus Impian</h3>
                <p class="text-sm text-gray-500">Tindakan ini tidak bisa dibatalkan</p>
            </div>
        </div>
        <p class="text-sm text-gray-600 mb-6">
            Yakin ingin menghapus impian <strong x-text="itemTitle"></strong>?
        </p>
        <div class="flex gap-3 justify-end">
            <button @click="open = false"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                Batal
            </button>
            <form :action="deleteUrl" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="rounded-lg bg-red-600 hover:bg-red-700 px-4 py-2 text-sm text-white transition-colors">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ─── Delete Modal ──────────────────────────────────────────────────────────────
function deleteModal() {
    return {
        open: false,
        deleteUrl: '',
        itemTitle: '',
    };
}

window.confirmDelete = function(url, title) {
    const modal = document.querySelector('[x-data="deleteModal()"]');
    if (modal && modal._x_dataStack) {
        const data = modal._x_dataStack[0];
        data.open = true;
        data.deleteUrl = url;
        data.itemTitle = title || 'impian ini';
    }
};

// ─── Sortable Reorder ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-sortable]').forEach(function (el) {
        Sortable.create(el, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            handle: '.drag-handle',
            onEnd: function () {
                const ids = Array.from(el.querySelectorAll('[data-id]'))
                    .map(function (item) { return parseInt(item.dataset.id); });

                fetch('{{ route('admin.wishlists.reorder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ ids: ids }),
                }).catch(function (err) {
                    console.error('Reorder failed:', err);
                });
            }
        });
    });
});
</script>
@endpush
