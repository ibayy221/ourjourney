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
    
    $existingMedia = $isEdit ? $memory->media->map(fn($m) => [
        'id' => $m->id,
        'type' => $m->is_youtube ? 'youtube' : (filter_var($m->file_path, FILTER_VALIDATE_URL) ? 'direct' : 'existing'),
        'previewUrl' => $m->file_url,
        'name' => basename($m->file_path),
        'url' => $m->file_path,
        'directType' => $m->type,
        'mediaType' => $m->type,
    ])->toArray() : [];
@endphp

<div id="memory-form-container"
     data-section="{{ $section }}"
     data-is-edit="{{ $isEdit ? 'true' : 'false' }}"
     data-source-type="{{ $isEdit && $memory->is_youtube ? 'youtube' : ($isEdit && filter_var($memory->file_path, FILTER_VALIDATE_URL) ? 'direct' : 'file') }}"
     data-preview-url="{{ $isEdit && !$memory->is_youtube && !filter_var($memory->file_path, FILTER_VALIDATE_URL) ? $memory->file_url : '' }}"
     data-preview-type="{{ $isEdit && !$memory->is_youtube && !filter_var($memory->file_path, FILTER_VALIDATE_URL) ? $memory->type : '' }}"
     data-youtube-url="{{ $isEdit && $memory->is_youtube ? $memory->file_path : '' }}"
     data-youtube-id="{{ $isEdit && $memory->is_youtube ? $memory->youtube_id : '' }}"
     data-direct-url="{{ $isEdit && !$memory->is_youtube && filter_var($memory->file_path, FILTER_VALIDATE_URL) ? $memory->file_path : '' }}"
     data-media-type="{{ $isEdit ? $memory->type : 'photo' }}"
     data-existing-media='@json($existingMedia)'
     x-data="{
        section: '',
        isEdit: false,
        sourceType: 'file',
        previewUrl: '',
        previewType: '',
        youtubeUrl: '',
        youtubeId: '',
        directUrl: '',
        mediaType: 'photo',
        mediaList: [],
        rawFiles: [], // Menyimpan file asli yang diunggah
        
        // Input bantu untuk modal tambah URL
        inputUrl: '',
        inputUrlType: 'youtube', // 'youtube' atau 'direct'
        inputDirectType: 'photo', // 'photo' atau 'video'
        showUrlModal: false,

        showTitle()   { return this.section === 'milestone'; },
        showChapter() { return this.section === 'milestone' || this.section === 'branch'; },
        showDate()    { return this.section === 'milestone'; },

        init() {
            // Load dari data-attributes
            const el = this.$el;
            this.section = el.dataset.section || 'gallery';
            this.isEdit = el.dataset.isEdit === 'true';
            this.sourceType = el.dataset.sourceType || 'file';
            this.previewUrl = el.dataset.previewUrl || '';
            this.previewType = el.dataset.previewType || '';
            this.youtubeUrl = el.dataset.youtubeUrl || '';
            this.youtubeId = el.dataset.youtubeId || '';
            this.directUrl = el.dataset.directUrl || '';
            this.mediaType = el.dataset.mediaType || 'photo';
            this.mediaList = JSON.parse(el.dataset.existingMedia || '[]');

            this.$watch('youtubeUrl', value => {
                this.youtubeId = this.parseYoutubeId(value);
            });
        },
    
    parseYoutubeId(url) {
        if (!url) return '';
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|shorts\/)([^#\&\?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : '';
    },
    
    // Handler tambah file di mode Multi-Media
    handleMultiFileSelect(e) {
        if (!e.target.files.length) return;
        this.addMultiFiles(Array.from(e.target.files));
    },

    addMultiFiles(files) {
        files.forEach(file => {
            const fileIndex = this.rawFiles.length;
            this.rawFiles.push(file);
            
            const mediaType = file.type.startsWith('video/') ? 'video' : 'photo';
            const previewUrl = URL.createObjectURL(file);
            
            this.mediaList.push({
                id: '',
                type: 'file',
                previewUrl: previewUrl,
                name: file.name,
                url: '',
                directType: '',
                mediaType: mediaType,
                fileIndex: fileIndex
            });
        });
        this.syncFilesInput();
    },

    // Tambah link (YouTube / Direct URL) di mode Multi-Media
    addLinkItem() {
        if (!this.inputUrl) return;

        if (this.inputUrlType === 'youtube') {
            const ytId = this.parseYoutubeId(this.inputUrl);
            if (!ytId) {
                alert('Format link YouTube tidak valid!');
                return;
            }
            this.mediaList.push({
                id: '',
                type: 'youtube',
                previewUrl: 'https://img.youtube.com/vi/' + ytId + '/hqdefault.jpg',
                name: 'YouTube Video',
                url: this.inputUrl,
                directType: '',
                mediaType: 'video'
            });
        } else {
            this.mediaList.push({
                id: '',
                type: 'direct',
                previewUrl: this.inputUrl,
                name: 'Direct URL Media (' + (this.inputDirectType === 'photo' ? 'Foto' : 'Video') + ')',
                url: this.inputUrl,
                directType: this.inputDirectType,
                mediaType: this.inputDirectType
            });
        }

        this.inputUrl = '';
        this.showUrlModal = false;
    },

    removeMediaItem(index) {
        const item = this.mediaList[index];
        if (item.type === 'file') {
            URL.revokeObjectURL(item.previewUrl);
        }
        this.mediaList.splice(index, 1);
        this.syncFilesInput();
    },

    moveItemUp(index) {
        if (index === 0) return;
        const temp = this.mediaList[index];
        this.mediaList[index] = this.mediaList[index - 1];
        this.mediaList[index - 1] = temp;
    },

    moveItemDown(index) {
        if (index === this.mediaList.length - 1) return;
        const temp = this.mediaList[index];
        this.mediaList[index] = this.mediaList[index + 1];
        this.mediaList[index + 1] = temp;
    },

    syncFilesInput() {
        const dataTransfer = new DataTransfer();
        // Hanya sync file-file yang masih aktif dirujuk di mediaList
        this.mediaList.forEach(item => {
            if (item.type === 'file' && item.fileIndex !== undefined) {
                const file = this.rawFiles[item.fileIndex];
                if (file) {
                    dataTransfer.items.add(file);
                }
            }
        });
        if (this.$refs.multiFilesInput) {
            this.$refs.multiFilesInput.files = dataTransfer.files;
        }
    },
    
    // --- SINGLE FILE HANDLERS (Untuk Gallery) ---
    handleSingleFileSelect(e) {
        if (!e.target.files.length) return;
        const file = e.target.files[0];
        this.previewType = file.type.startsWith('video/') ? 'video' : 'photo';
        this.previewUrl = URL.createObjectURL(file);
    }
}" class="space-y-6">

    {{-- =========================================================================
         SECTION MULTI-MEDIA BUILDER (Hanya Milestone & Branch)
         ========================================================================= --}}
    <div x-show="section !== 'gallery'" class="space-y-4">
        <label class="block text-sm font-medium text-gray-700">Daftar Media Momen (Carousel) <span class="text-red-500">*</span></label>
        
        {{-- List Media Terpasang --}}
        <div class="space-y-3 bg-gray-50 p-4 rounded-2xl border border-gray-200">
            <template x-if="mediaList.length === 0">
                <div class="text-center py-6 text-gray-400 text-sm">
                    ⚠️ Belum ada foto atau video dalam momen ini.
                </div>
            </template>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <template x-for="(item, index) in mediaList" :key="index">
                    <div class="flex items-center gap-3 bg-white p-3 rounded-xl border border-gray-200 shadow-sm relative group">
                        
                        {{-- Hidden inputs to submit multi-media structured data --}}
                        <input type="hidden" name="media_ids[]" :value="item.id">
                        <input type="hidden" name="media_types[]" :value="item.type">
                        <input type="hidden" name="media_urls[]" :value="item.url">
                        <input type="hidden" name="media_direct_types[]" :value="item.directType">
                        <input type="hidden" name="media_file_indices[]" :value="index">

                        {{-- Media Thumbnail Preview --}}
                        <div class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 flex items-center justify-center border border-gray-100">
                            <template x-if="item.mediaType === 'video' && item.type !== 'youtube' && !item.previewUrl.startsWith('http')">
                                <div class="bg-gray-200 text-gray-600 w-full h-full flex items-center justify-center">
                                    🎥
                                </div>
                            </template>
                            <template x-if="item.mediaType === 'video' && item.type !== 'youtube' && item.previewUrl.startsWith('http')">
                                <video :src="item.previewUrl" class="w-full h-full object-cover"></video>
                            </template>
                            <template x-if="item.type === 'youtube'">
                                <img :src="item.previewUrl" class="w-full h-full object-cover">
                            </template>
                            <template x-if="item.mediaType === 'photo'">
                                <img :src="item.previewUrl" class="w-full h-full object-cover">
                            </template>
                        </div>

                        {{-- Metadata --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-700 truncate" x-text="item.name"></p>
                            <span class="inline-block mt-1 text-[10px] uppercase font-mono px-1.5 py-0.5 rounded font-bold"
                                  :class="{
                                      'bg-blue-100 text-blue-800': item.type === 'file',
                                      'bg-red-100 text-red-800': item.type === 'youtube',
                                      'bg-purple-100 text-purple-800': item.type === 'direct',
                                      'bg-green-100 text-green-800': item.type === 'existing'
                                  }"
                                  x-text="item.type"></span>
                        </div>

                        {{-- Order & Delete Controls --}}
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="moveItemUp(index)" :disabled="index === 0"
                                    class="p-1 rounded hover:bg-gray-100 text-gray-400 hover:text-gray-600 disabled:opacity-30">
                                🔼
                            </button>
                            <button type="button" @click="moveItemDown(index)" :disabled="index === mediaList.length - 1"
                                    class="p-1 rounded hover:bg-gray-100 text-gray-400 hover:text-gray-600 disabled:opacity-30">
                                🔽
                            </button>
                            <button type="button" @click="removeMediaItem(index)"
                                    class="p-1 rounded hover:bg-red-50 text-red-400 hover:text-red-600">
                                🗑️
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Add Buttons --}}
        <div class="flex flex-wrap gap-3">
            {{-- File Uploader Button --}}
            <label class="flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-deep border border-green-200 font-semibold px-4 py-2 rounded-xl text-sm cursor-pointer transition-colors">
                📁 Unggah File Foto/Video
                <input type="file" x-ref="multiFilesInput" name="files[]" class="hidden" multiple
                       accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm"
                       @change="handleMultiFileSelect($event)">
            </label>

            {{-- Link Dialog Toggle --}}
            <button type="button" @click="showUrlModal = true"
                    class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
                🔗 Tambah URL YouTube / Direct
            </button>
        </div>

        {{-- Dialog Modal Tambah URL --}}
        <div x-show="showUrlModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50" x-cloak>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100 space-y-4" @click.away="showUrlModal = false">
                <h3 class="text-base font-bold text-gray-900">Tambah URL Media</h3>
                
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Pilih Tipe Link</label>
                    <select x-model="inputUrlType" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="youtube">🔗 Video YouTube</option>
                        <option value="direct">🌐 Link URL Langsung (Direct URL)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1" x-text="inputUrlType === 'youtube' ? 'URL Video YouTube' : 'URL Direct File'"></label>
                    <input type="url" x-model="inputUrl" placeholder="https://..." class="w-full rounded-lg border-gray-300 text-sm">
                </div>

                <div x-show="inputUrlType === 'direct'" x-cloak>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Tipe Media URL</label>
                    <select x-model="inputDirectType" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="photo">📷 Foto (Gambar)</option>
                        <option value="video">🎥 Video (File Langsung)</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="showUrlModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs font-semibold text-gray-700">Batal</button>
                    <button type="button" @click="addLinkItem()" class="px-4 py-2 bg-green-deep hover:bg-green-700 text-white rounded-lg text-xs font-semibold">Tambahkan</button>
                </div>
            </div>
        </div>
    </div>


    {{-- =========================================================================
         SECTION SINGLE MEDIA (Hanya untuk Gallery)
         ========================================================================= --}}
    <div x-show="section === 'gallery'" class="bg-gray-50/50 rounded-2xl border border-gray-100 p-4">
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

            {{-- Preview --}}
            <div x-show="previewUrl" class="mb-3 rounded-xl overflow-hidden bg-gray-100 max-w-xs" x-cloak>
                <template x-if="previewType === 'video'">
                    <video :src="previewUrl" controls class="w-full max-h-48 object-contain"></video>
                </template>
                <template x-if="previewType !== 'video'">
                    <img :src="previewUrl" alt="Preview" class="w-full max-h-48 object-contain">
                </template>
            </div>

            <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-gray-300 hover:border-green-deep hover:bg-green-50 rounded-xl cursor-pointer transition-all duration-200">
                <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span class="text-sm font-medium text-gray-600">Klik atau seret foto ke sini</span>
                <span class="text-xs text-gray-400 mt-1">Max 20MB · JPG, PNG, GIF, WebP, MP4</span>
                
                <input type="file" name="file" class="hidden"
                       accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/mpeg,video/webm"
                       @change="handleSingleFileSelect($event)"
                       :required="sourceType === 'file' && section === 'gallery' && !isEdit && !previewUrl">
            </label>
        </div>

        {{-- 🔗 Link YouTube Section --}}
        <div x-show="sourceType === 'youtube'" x-cloak class="space-y-4">
            <div>
                <label for="youtube_url" class="block text-sm font-medium text-gray-700 mb-2">URL Video YouTube <span class="text-red-500">*</span></label>
                <input type="url" id="youtube_url" name="youtube_url" 
                       x-model="youtubeUrl"
                       placeholder="Contoh: https://www.youtube.com/watch?v=xxxxxxxxxxx"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm"
                       :required="sourceType === 'youtube' && section === 'gallery' && !'{{ $isEdit }}'">
                
                <div x-show="youtubeId" class="mt-4 rounded-xl overflow-hidden bg-black aspect-video max-w-sm relative border border-gray-200" x-cloak>
                    <iframe :src="'https://www.youtube.com/embed/' + youtubeId" class="absolute inset-0 w-full h-full" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>

        {{-- 🌐 Link URL Langsung Section --}}
        <div x-show="sourceType === 'direct'" x-cloak class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="direct_url" class="block text-sm font-medium text-gray-700 mb-2">URL Media <span class="text-red-500">*</span></label>
                    <input type="url" id="direct_url" name="direct_url" 
                           x-model="directUrl"
                           placeholder="Contoh: https://i.ibb.co/xxxxxx/foto.jpg"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm"
                           :required="sourceType === 'direct' && section === 'gallery' && !'{{ $isEdit }}'">
                </div>
                <div>
                    <label for="media_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe Media <span class="text-red-500">*</span></label>
                    <select id="media_type" name="media_type" x-model="mediaType"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
                        <option value="photo">📷 Foto</option>
                        <option value="video">🎥 Video</option>
                    </select>
                </div>
            </div>
            
            <div x-show="directUrl && directUrl.startsWith('http')" class="mt-4 rounded-xl overflow-hidden bg-gray-100 max-w-xs border border-gray-200 relative h-36" x-cloak>
                <template x-if="mediaType === 'video'">
                    <video :src="directUrl" controls class="w-full h-full object-contain"></video>
                </template>
                <template x-if="mediaType === 'photo'">
                    <img :src="directUrl" alt="Preview" class="w-full h-full object-contain" x-on:error="$el.src = 'https://placehold.co/400x300?text=Format+URL+Foto+Salah'">
                </template>
            </div>
        </div>
    </div>

    {{-- Section Select --}}
    <div>
        <label for="section" class="block text-sm font-medium text-gray-700 mb-2">Section <span class="text-red-500">*</span></label>
        <select id="section" name="section" x-model="section"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
            <option value="gallery"   @selected($section === 'gallery')  >Gallery 🖼️</option>
            <option value="milestone" @selected($section === 'milestone')>Milestone 🌱</option>
            <option value="branch"    @selected($section === 'branch')   >Branch 🌿</option>
        </select>
    </div>

    {{-- Title (hanya milestone) --}}
    <div x-show="showTitle()" x-cloak>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Momen</label>
        <input type="text" id="title" name="title" value="{{ $old('title') }}"
               placeholder="Contoh: Pertama kali bertemu"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
    </div>

    {{-- Chapter (milestone & branch) --}}
    <div x-show="showChapter()" x-cloak>
        <label for="chapter" class="block text-sm font-medium text-gray-700 mb-2">Bab / Chapter</label>
        <input type="text" id="chapter" name="chapter" value="{{ $old('chapter') }}"
               placeholder="Contoh: Bab Satu, Bab Dua"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
    </div>

    {{-- Event Date (hanya milestone) --}}
    <div x-show="showDate()" x-cloak>
        <label for="event_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Momen</label>
        <input type="date" id="event_date" name="event_date"
               value="{{ $old('event_date', $isEdit && $memory->event_date ? $memory->event_date->format('Y-m-d') : '') }}"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
    </div>

    {{-- Caption --}}
    <div>
        <label for="caption" class="block text-sm font-medium text-gray-700 mb-2">Caption</label>
        <textarea id="caption" name="caption" rows="3"
                  placeholder="Cerita singkat tentang momen ini..."
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">{{ $old('caption') }}</textarea>
    </div>

    {{-- Category --}}
    <div>
        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-gray-400 font-normal">(opsional)</span></label>
        <input type="text" id="category" name="category" value="{{ $old('category') }}"
               placeholder="Contoh: Liburan, Ulang Tahun"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-deep focus:ring-green-deep text-sm">
    </div>

</div>
