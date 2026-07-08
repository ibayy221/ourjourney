@extends('layouts.admin')
@section('title', 'Kelola Memories')

@section('content')

{{-- Header --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kelola Memories</h1>
        <p class="text-sm text-gray-500 mt-1">Drag untuk mengubah urutan tampilan</p>
    </div>
    <a href="{{ route('admin.memories.create') }}"
       class="inline-flex items-center gap-2 rounded-lg bg-green-deep hover:bg-green-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Memory
    </a>
</div>

{{-- Tabs --}}
<div x-data="{ tab: 'milestone' }" class="space-y-6">

    {{-- Tab Nav --}}
    <div class="flex gap-1 rounded-xl bg-gray-100 p-1 w-fit">
        @foreach([['milestone','Milestone 🌱'],['branch','Branch 🌿'],['gallery','Gallery 🖼️']] as [$key,$label])
        <button @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'bg-white shadow text-green-deep font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="rounded-lg px-5 py-2 text-sm transition-all">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ── MILESTONE ── --}}
    <div x-show="tab === 'milestone'" x-cloak>
        @include('admin.memories._section_list', [
            'items'     => $milestones,
            'section'   => 'milestone',
            'emptyText' => 'Belum ada milestone. Tambahkan momen penting pertama!',
        ])
    </div>

    {{-- ── BRANCH ── --}}
    <div x-show="tab === 'branch'" x-cloak>
        @include('admin.memories._section_list', [
            'items'     => $branches,
            'section'   => 'branch',
            'emptyText' => 'Belum ada branch. Tambahkan kenangan per bab!',
        ])
    </div>

    {{-- ── GALLERY ── --}}
    <div x-show="tab === 'gallery'" x-cloak>
        @include('admin.memories._section_list', [
            'items'     => $galleries,
            'section'   => 'gallery',
            'emptyText' => 'Belum ada foto/video di galeri.',
        ])
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
                <h3 class="font-semibold text-gray-900">Hapus Memory</h3>
                <p class="text-sm text-gray-500">Tindakan ini tidak bisa dibatalkan</p>
            </div>
        </div>
        <p class="text-sm text-gray-600 mb-6">
            Yakin ingin menghapus <strong x-text="itemTitle"></strong>? File juga akan dihapus dari storage.
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

// Expose globally so _section_list partial can call it
window.confirmDelete = function(url, title) {
    // Find the Alpine component on the modal
    const modal = document.querySelector('[x-data="deleteModal()"]');
    if (modal && modal._x_dataStack) {
        const data = modal._x_dataStack[0];
        data.open = true;
        data.deleteUrl = url;
        data.itemTitle = title || 'item ini';
    }
};

// ─── Sortable Reorder ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-sortable]').forEach(function (el) {
        const section = el.dataset.sortable;
        Sortable.create(el, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            handle: '.drag-handle',
            onEnd: function () {
                const ids = Array.from(el.querySelectorAll('[data-id]'))
                    .map(function (item) { return parseInt(item.dataset.id); });

                fetch('{{ route('admin.memories.reorder') }}', {
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
