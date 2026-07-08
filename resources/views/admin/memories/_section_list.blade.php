{{--
    Partial: _section_list.blade.php
    Variables: $items (Collection), $section (string), $emptyText (string)
--}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    @if($items->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
            <svg class="w-12 h-12 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm">{{ $emptyText }}</p>
            <a href="{{ route('admin.memories.create') }}?section={{ $section }}"
               class="mt-4 text-sm text-green-deep hover:underline font-medium">
                + Tambah sekarang
            </a>
        </div>
    @else
        <ul data-sortable="{{ $section }}" class="divide-y divide-gray-50">
            @foreach($items as $item)
            <li data-id="{{ $item->id }}"
                class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50 transition-colors group">

                {{-- Drag Handle --}}
                <div class="drag-handle cursor-grab text-gray-300 hover:text-gray-500 flex-shrink-0 select-none">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                    </svg>
                </div>

                {{-- Thumbnail --}}
                <div class="w-14 h-14 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                    @if($item->type === 'video')
                        <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-500">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    @else
                        <img src="{{ $item->file_url }}" alt="{{ $item->title ?? 'Memory' }}"
                             class="w-full h-full object-cover">
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ $item->title ?: ($item->caption ? \Str::limit($item->caption, 50) : 'Tanpa judul') }}
                    </p>
                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">
                            {{ $item->type === 'video' ? '🎥 Video' : '📷 Foto' }}
                        </span>
                        @if($item->chapter)
                        <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs text-green-700">
                            {{ $item->chapter }}
                        </span>
                        @endif
                        @if($item->category)
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-700">
                            {{ $item->category }}
                        </span>
                        @endif
                        @if($item->event_date)
                        <span class="text-xs text-gray-400">
                            {{ $item->event_date->format('d M Y') }}
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                    <a href="{{ route('admin.memories.edit', $item) }}"
                       class="rounded-lg bg-gray-100 hover:bg-gray-200 p-2 text-gray-600 transition-colors"
                       title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <button type="button"
                            onclick="confirmDelete('{{ route('admin.memories.destroy', $item) }}', '{{ addslashes($item->title ?? 'item ini') }}')"
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
