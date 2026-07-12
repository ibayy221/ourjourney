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
    
    // Media Source: 'file', 'youtube', or 'direct'
    sourceType: '{{ $isEdit && $memory->is_youtube ? 'youtube' : ($isEdit && filter_var($memory->file_path, FILTER_VALIDATE_URL) ? 'direct' : 'file') }}',
    
    // For single file (edit mode)
    previewUrl: '{{ $isEdit && !$memory->is_youtube && !filter_var($memory->file_path, FILTER_VALIDATE_URL) ? $memory->file_url : '' }}',
    previewType: '{{ $isEdit && !$memory->is_youtube && !filter_var($memory->file_path, FILTER_VALIDATE_URL) ? $memory->type : '' }}',
    
    // For multiple files (create mode)
    filesList: [],
    isDragOver: false,
    
    // For YouTube input
    youtubeUrl: '{{ $isEdit && $memory->is_youtube ? $memory->file_path : '' }}',
    youtubeId: '{{ $isEdit && $memory->is_youtube ? $memory->youtube_id : '' }}',
    
    // For Direct URL input
    directUrl: '{{ $isEdit && !$memory->is_youtube && filter_var($memory->file_path, FILTER_VALIDATE_URL) ? $memory->file_path : '' }}',
    mediaType: '{{ $isEdit ? $memory->type : 'photo' }}',
    
    init() {
        this.$watch('youtubeUrl', value => {
            this.youtubeId = this.parseYoutubeId(value);
        });
    },
    
    parseYoutubeId(url) {
        if (!url) return '';
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : '';
    },
    
    handleFileSelect(e) {
        if (!e.target.files.length) return;
        this.addFiles(Array.from(e.target.files));
    },
    
    handleDrop(e) {
        if (!e.dataTransfer.files.length) return;
        this.addFiles(Array.from(e.dataTransfer.files));
    },
    
    addFiles(files) {
        if ('{{ $isEdit }}') {
            const file = files[0];
            if (!file) return;
            this.previewType = file.type.startsWith('video/') ? 'video' : 'photo';
            this.previewUrl = URL.createObjectURL(file);
            
            // Sync single file input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            this.$refs.fileInput.files = dataTransfer.files;
        } else {
            files.forEach(file => {
                const type = file.type.startsWith('video/') ? 'video' : 'photo';
                const previewUrl = URL.createObjectURL(file);
                this.filesList.push({
                    file: file,
                    previewUrl: previewUrl,
                    type: type,
                    name: file.name
                });
            });
            this.syncFilesInput();
        }
    },
    
    removeFile(index) {
        URL.revokeObjectURL(this.filesList[index].previewUrl);
        this.filesList.splice(index, 1);
        this.syncFilesInput();
    },
    
    syncFilesInput() {
        const dataTransfer = new DataTransfer();
        this.filesList.forEach(item => {
            dataTransfer.items.add(item.file);
        });
        this.$refs.fileInput.files = dataTransfer.files;
    }
}" class="space-y-6">

    {{-- Media Input (File Upload vs YouTube URL) --}}
    <div class="bg-gray-50/50 rounded-2xl border border-gray-100 p-4">
        {{-- Toggle Tabs --}}
        <div class="flex gap-1 rounded-xl bg-gray-100 p-1 w-fit mb-4">
            <button type="button" @click="sourceType = 'file'"
                    :class="sourceType === 'file' ? 'bg-white shadow text-green-deep font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="rounded-lg px-4 py-1.5 text-xs transition-all">
                📁 Upload File
            </button>
            <button type="button" @click="sourceType = 'youtube'"
                    :class="sourceType === 'youtube' ? 'bg-white shadow text-green-deep font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="rounded-lg px-4 py-1.5 text-xs transition-all">
                🔗 Link YouTube
            </button>
            <button type="button" @click="sourceType = 'direct'"
                    :class="sourceType === 'direct' ? 'bg-white shadow text-green-deep font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="rounded-lg px-4 py-1.5 text-xs transition-all">
                🌐 Link URL Langsung
            </button>
        </div>

        {{-- 📁 File Upload Section --}}
        <div x-show="sourceType === 'file'" x-cloak>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ $isEdit ? 'Ganti File (opsional)' : 'Upload File' }}
                @if(!$isEdit) <span class="text-red-500">*</span> @endif
            </label>

            {{-- Preview for Edit Mode (Single File) --}}
            @if($isEdit)
            <div x-show="previewUrl" class="mb-3 rounded-xl overflow-hidden bg-gray-100 max-w-xs" x-cloak>
                <template x-if="previewType === 'video'">
                    <video :src="previewUrl" controls class="w-full max-h-48 object-contain"></video>
                </template>
                <template x-if="previewType !== 'video'">
                    <img :src="previewUrl" alt="Preview" class="w-full max-h-48 object-contain">
                </template>
            </div>
            @endif

            {{-- Preview Grid for Create Mode (Multiple Files) --}}
            @if(!$isEdit)
            <div x-show="filesList.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4" x-cloak>
                <template x-for="(item, index) in filesList" :key="index">
                    <div class="relative group rounded-xl overflow-hidden border border-gray-200 bg-white shadow-sm flex flex-col h-36">
                        {{-- Media Preview --}}
                        <div class="w-full flex-1 bg-gray-50 overflow-hidden relative flex items-center justify-center">
                            <template x-if="item.type === 'video'">
                                <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-500">
                                    <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </template>
                            <template x-if="item.type !== 'video'">
                                <img :src="item.previewUrl" class="w-full h-full object-cover" />
                            </template>
                            
                            {{-- Video badge --}}
                            <template x-if="item.type === 'video'">
                                <span class="absolute bottom-2 left-2 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded font-mono">
                                    🎥 VIDEO
                                </span>
                            </template>
                        </div>
                        
                        {{-- Info --}}
                        <div class="p-2 border-t border-gray-100 bg-gray-50 flex items-center justify-between text-xs text-gray-500">
                            <span class="truncate pr-2 font-mono" x-text="item.name"></span>
                            <button type="button" @click="removeFile(index)"
                                    class="text-red-500 hover:text-red-700 bg-white hover:bg-red-50 p-1 rounded-md shadow-sm border border-gray-200 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
            @endif

            {{-- Drag & Drop Area --}}
            <label
                @dragover.prevent="isDragOver = true"
                @dragleave.prevent="isDragOver = false"
                @drop.prevent="isDragOver = false; handleDrop($event)"
                :class="isDragOver ? 'border-green-deep bg-green-50/50' : 'border-gray-300 hover:border-green-deep hover:bg-green-50'"
                class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-xl cursor-pointer transition-all duration-200">
                <svg class="w-10 h-10 text-gray-400 mb-2 transition-transform duration-200" :class="isDragOver ? 'scale-110 text-green-deep' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span class="text-sm font-medium text-gray-600" x-text="isDragOver ? 'Lepaskan file di sini!' : 'Klik atau drag file ke sini'"></span>
                <span class="text-xs text-gray-400 mt-1" x-show="!isDragOver">Max 20MB per file · JPG, PNG, GIF, WebP, MP4, MOV, WebM</span>
                
                @if($isEdit)
                    <input type="file" name="file" x-ref="fileInput" class="hidden"
                           accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm"
                           @change="handleFileSelect($event)"
                           :required="sourceType === 'file' && !'{{ $isEdit }}' && !previewUrl">
                @else
                    <input type="file" name="files[]" x-ref="fileInput" class="hidden"
                           accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm"
                           multiple
                           @change="handleFileSelect($event)"
                           :required="sourceType === 'file' && filesList.length === 0">
                @endif
            </label>
        </div>

        {{-- 🔗 Link YouTube Section --}}
        <div x-show="sourceType === 'youtube'" x-cloak class="space-y-4">
            <div>
                <label for="youtube_url" class="block text-sm font-medium text-gray-700 mb-2">
                    URL Video YouTube <span class="text-red-500">*</span>
                </label>
                <input type="url" id="youtube_url" name="youtube_url" 
                       x-model="youtubeUrl"
                       placeholder="Contoh: https://www.youtube.com/watch?v=xxxxxxxxxxx atau https://youtu.be/xxxxxxxxxxx"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm"
                       :required="sourceType === 'youtube' && !'{{ $isEdit }}'">
                <p class="mt-1 text-xs text-gray-400">
                    Mendukung format link YouTube biasa atau tautan pendek (youtu.be).
                </p>
                
                {{-- YouTube Video Live Preview --}}
                <div x-show="youtubeId" class="mt-4 rounded-xl overflow-hidden bg-black aspect-video max-w-sm relative border border-gray-200 shadow-sm" x-cloak>
                    <iframe :src="'https://www.youtube.com/embed/' + youtubeId" 
                            class="absolute inset-0 w-full h-full" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>

        {{-- 🌐 Link URL Langsung Section --}}
        <div x-show="sourceType === 'direct'" x-cloak class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="direct_url" class="block text-sm font-medium text-gray-700 mb-2">
                        URL Media (Foto/Video) <span class="text-red-500">*</span>
                    </label>
                    <input type="url" id="direct_url" name="direct_url" 
                           x-model="directUrl"
                           placeholder="Contoh: https://i.ibb.co/xxxxxx/foto.jpg"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm"
                           :required="sourceType === 'direct' && !'{{ $isEdit }}'">
                </div>
                <div>
                    <label for="media_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipe Media <span class="text-red-500">*</span>
                    </label>
                    <select id="media_type" name="media_type" x-model="mediaType"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
                        <option value="photo">📷 Foto</option>
                        <option value="video">🎥 Video</option>
                    </select>
                </div>
            </div>
            <p class="mt-1 text-xs text-gray-400">
                Gunakan ini untuk memasukkan URL langsung dari file gambar/video yang di-host di luar (seperti ImgBB, PostImages, Google Drive, dll.).
            </p>
            
            {{-- Preview of Direct URL --}}
            <div x-show="directUrl && directUrl.startsWith('http')" class="mt-4 rounded-xl overflow-hidden bg-gray-100 max-w-xs border border-gray-200 shadow-sm relative h-36" x-cloak>
                <template x-if="mediaType === 'video'">
                    <video :src="directUrl" controls class="w-full h-full object-contain"></video>
                </template>
                <template x-if="mediaType === 'photo'">
                    <img :src="directUrl" alt="Preview" class="w-full h-full object-contain" @error="$el.src = 'https://placehold.co/400x300?text=Format+URL+Foto+Salah'">
                </template>
            </div>
        </div>

        @error('file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('files')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('files.*')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('youtube_url')
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
