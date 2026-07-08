{{-- Shared form partial for create & edit --}}
{{--
    Variables:
    - $memory (optional, for edit mode)
    - $mode: 'create' | 'edit'
--}}
@php
    $isEdit   = isset($memory);
    $old      = fn($field, $default = '') => old($field, $isEdit ? $memory->$field : $default);
    $section  = $old('section', request('section', 'gallery'));
@endphp

<div x-data="{
    section: '{{ $section }}',
    showTitle()   { return this.section === 'milestone'; },
    showChapter() { return this.section === 'milestone' || this.section === 'branch'; },
    showDate()    { return this.section === 'milestone'; },
    previewUrl: '{{ $isEdit ? $memory->file_url : '' }}',
    previewType: '{{ $isEdit ? $memory->type : '' }}',
    handleFile(e) {
        const file = e.target.files[0];
        if (!file) return;
        this.previewType = file.type.startsWith('video/') ? 'video' : 'photo';
        this.previewUrl = URL.createObjectURL(file);
    }
}" class="space-y-6">

    {{-- File Upload --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $isEdit ? 'Ganti File (opsional)' : 'Upload File' }}
            @if(!$isEdit) <span class="text-red-500">*</span> @endif
        </label>

        {{-- Preview --}}
        <div x-show="previewUrl" class="mb-3 rounded-xl overflow-hidden bg-gray-100 max-w-xs" x-cloak>
            <template x-if="previewType === 'video'">
                <video :src="previewUrl" controls class="w-full max-h-48 object-contain"></video>
            </template>
            <template x-if="previewType !== 'video'">
                <img :src="previewUrl" alt="Preview" class="w-full max-h-48 object-contain">
            </template>
        </div>

        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-green-deep hover:bg-green-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <span class="text-sm text-gray-500">Klik untuk pilih foto atau video</span>
            <span class="text-xs text-gray-400 mt-1">Max 20MB · JPG, PNG, GIF, WebP, MP4, MOV, WebM</span>
            <input type="file" name="file" class="hidden"
                   accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm"
                   @change="handleFile($event)"
                   {{ !$isEdit ? 'required' : '' }}>
        </label>

        @error('file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Section --}}
    <div>
        <label for="section" class="block text-sm font-medium text-gray-700 mb-2">
            Section <span class="text-red-500">*</span>
        </label>
        <select id="section" name="section" x-model="section"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
            <option value="gallery"   @selected($section === 'gallery')  >Gallery 🖼️</option>
            <option value="milestone" @selected($section === 'milestone')>Milestone 🌱</option>
            <option value="branch"    @selected($section === 'branch')   >Branch 🌿</option>
        </select>
        @error('section')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Title (hanya milestone) --}}
    <div x-show="showTitle()" x-cloak>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Momen</label>
        <input type="text" id="title" name="title" value="{{ $old('title') }}"
               placeholder="Contoh: Pertama kali bertemu"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
        @error('title')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Chapter (milestone & branch) --}}
    <div x-show="showChapter()" x-cloak>
        <label for="chapter" class="block text-sm font-medium text-gray-700 mb-2">Bab / Chapter</label>
        <input type="text" id="chapter" name="chapter" value="{{ $old('chapter') }}"
               placeholder="Contoh: Bab Satu, Bab Dua"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
        @error('chapter')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Event Date (hanya milestone) --}}
    <div x-show="showDate()" x-cloak>
        <label for="event_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Momen</label>
        <input type="date" id="event_date" name="event_date"
               value="{{ $old('event_date', $isEdit && $memory->event_date ? $memory->event_date->format('Y-m-d') : '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
        @error('event_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Caption --}}
    <div>
        <label for="caption" class="block text-sm font-medium text-gray-700 mb-2">Caption</label>
        <textarea id="caption" name="caption" rows="3"
                  placeholder="Cerita singkat tentang momen ini..."
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">{{ $old('caption') }}</textarea>
        @error('caption')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Category --}}
    <div>
        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
            Kategori
            <span class="text-gray-400 font-normal">(opsional, untuk filter gallery)</span>
        </label>
        <input type="text" id="category" name="category" value="{{ $old('category') }}"
               placeholder="Contoh: Liburan, Ulang Tahun, Sehari-hari"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
        @error('category')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>
