@extends('layouts.admin')
@section('title', 'Edit Impian')

@section('content')

<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.wishlists.index') }}"
           class="rounded-lg p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Impian</h1>
            <p class="text-sm text-gray-500">Perbarui detail impian bersama</p>
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
         x-data="{ completed: {{ old('is_completed', $wishlist->is_completed) ? 'true' : 'false' }} }">
        <form action="{{ route('admin.wishlists.update', $wishlist) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Impian *</label>
                <input type="text" name="title" id="title" required value="{{ old('title', $wishlist->title) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-green-soft focus:ring-1 focus:ring-green-soft outline-none transition-colors">
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-green-soft focus:ring-1 focus:ring-green-soft outline-none transition-colors">{{ old('description', $wishlist->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="is_completed" class="block text-sm font-medium text-gray-700 mb-2">Status Pencapaian</label>
                    <select name="is_completed" id="is_completed" x-model="completed"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-green-soft focus:ring-1 focus:ring-green-soft outline-none transition-colors">
                        <option value="0">🌱 Belum Tercapai (Rencana)</option>
                        <option value="1">🌸 Sudah Tercapai (Mimpi Jadi Nyata)</option>
                    </select>
                    @error('is_completed')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="completed == '1' || completed === true" x-transition>
                    <label for="completed_at" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Tercapai</label>
                    <input type="date" name="completed_at" id="completed_at"
                           value="{{ old('completed_at', $wishlist->completed_at ? $wishlist->completed_at->format('Y-m-d') : now()->format('Y-m-d')) }}"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-green-soft focus:ring-1 focus:ring-green-soft outline-none transition-colors">
                    @error('completed_at')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('admin.wishlists.index') }}"
                   class="flex-1 text-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="flex-1 rounded-lg bg-green-deep hover:bg-green-700 px-4 py-2.5 text-sm font-medium text-white transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
