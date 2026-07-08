@extends('layouts.admin')
@section('title', 'Tambah Memory')

@section('content')

<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.memories.index') }}"
           class="rounded-lg p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Tambah Memory Baru</h1>
            <p class="text-sm text-gray-500">Upload foto atau video kenangan</p>
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.memories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @include('admin.memories._form', ['mode' => 'create'])

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('admin.memories.index') }}"
                   class="flex-1 text-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="flex-1 rounded-lg bg-green-deep hover:bg-green-700 px-4 py-2.5 text-sm font-medium text-white transition-colors">
                    Simpan Memory
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
