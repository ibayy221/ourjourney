<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Kenangan — Putri Daun &amp; Ubay</title>
    <meta name="description" content="Semua video kenangan perjalanan cinta Putri Daun dan Ubay.">

    {{-- Public CSS --}}
    <link rel="stylesheet" href="{{ asset('css/public.css') }}">

    {{-- GSAP 3 + ScrollTrigger --}}
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════════
     NAV
     ══════════════════════════════════════════════════════════════ --}}
<nav class="pub-nav" role="navigation" aria-label="Navigasi utama">
    <a href="/" class="pub-nav__brand">Putri Daun <span>&amp;</span> Ubay</a>
    <ul class="pub-nav__links">
        <li><a href="/">Perjalanan</a></li>
        <li><a href="/gallery">Galeri</a></li>
        <li><a href="/cinema" class="active">Cinema</a></li>
    </ul>
</nav>

{{-- ══════════════════════════════════════════════════════════════
     CINEMA HERO
     ══════════════════════════════════════════════════════════════ --}}
<header class="gallery-hero" role="banner">
    <p style="font-family:'Space Mono',monospace; font-size:0.7rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--green-soft); margin-bottom:16px;">
        ✦ kenangan bergerak ✦
    </p>
    <h1 class="gallery-hero__title">Cinema <em style="font-style:italic; color:var(--bloom);">Kenangan</em></h1>
    <p class="gallery-hero__subtitle">Suara, gerak, dan tawa manis yang terekam selamanya.</p>
</header>

{{-- ══════════════════════════════════════════════════════════════
     CATEGORY FILTERS
     ══════════════════════════════════════════════════════════════ --}}
@php
    $categories = $items->pluck('category')->filter()->unique()->values();
@endphp

@if($categories->isNotEmpty())
<div class="gallery-filters" role="group" aria-label="Filter kategori">
    <button class="gallery-filter-btn is-active"
            data-category="all"
            id="filter-all"
            type="button">
        Semua
    </button>
    @foreach($categories as $cat)
        <button class="gallery-filter-btn"
                data-category="{{ $cat }}"
                id="filter-{{ Str::slug($cat) }}"
                type="button">
            {{ $cat }}
        </button>
    @endforeach
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     CINEMA GRID
     ══════════════════════════════════════════════════════════════ --}}
@if($items->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">🎥</div>
        <h2 class="empty-state__title">Cinema masih kosong</h2>
        <p class="empty-state__text">Video kenangan akan tampil di sini.</p>
    </div>
@else
    <div class="masonry-grid cinema-grid" id="masonry-grid" role="list" aria-label="Galeri video kenangan">
        @foreach($items as $item)
            <div class="masonry-item"
                 role="listitem"
                 tabindex="0"
                 data-id="{{ $item['id'] }}"
                 data-type="{{ $item['type'] }}"
                 data-src="{{ $item['file_url'] }}"
                 data-caption="{{ $item['caption'] ?? '' }}"
                 data-category="{{ $item['category'] ?? '' }}"
                 data-is-youtube="{{ $item['is_youtube'] ? 'true' : 'false' }}"
                 data-youtube-id="{{ $item['youtube_id'] }}"
                 aria-label="{{ $item['caption'] ?? 'Video kenangan' }}"
                 style="cursor:pointer;">

                @if($item['is_youtube'])
                    {{-- YouTube Video: show YouTube high-quality thumbnail --}}
                    <img src="{{ $item['thumbnail_url'] }}"
                         alt="{{ $item['caption'] ?? 'Video kenangan' }}"
                         loading="lazy">
                @else
                    {{-- Local Video: show placeholder, play on hover --}}
                    <video src="{{ $item['file_url'] }}"
                           muted
                           loop
                           playsinline
                           preload="metadata">
                    </video>
                @endif
                
                <div class="masonry-item__play-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </div>

                {{-- Hover overlay --}}
                <div class="masonry-item__overlay" aria-hidden="true">
                    @if($item['category'])
                        <p class="masonry-item__category">{{ $item['category'] }}</p>
                    @endif
                    @if($item['caption'])
                        <p class="masonry-item__caption">{{ $item['caption'] }}</p>
                    @endif
                </div>

            </div>
        @endforeach
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     FOOTER
     ══════════════════════════════════════════════════════════════ --}}
<footer class="pub-footer" role="contentinfo">
    <p class="pub-footer__text">
        dibuat dengan <span class="pub-footer__heart">♥</span> untuk Putri Daun &amp; Ubay
        &nbsp;·&nbsp; {{ date('Y') }}
    </p>
</footer>

{{-- ══════════════════════════════════════════════════════════════
     LIGHTBOX
     ══════════════════════════════════════════════════════════════ --}}
<div id="lightbox" class="lightbox" role="dialog" aria-modal="true" aria-label="Lightbox kenangan">

    {{-- Close button --}}
    <button id="lb-close" class="lightbox__close" aria-label="Tutup lightbox">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
    </button>

    {{-- Previous --}}
    <button id="lb-prev" class="lightbox__nav lightbox__nav--prev" aria-label="Sebelumnya">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
    </button>

    {{-- Media wrapper --}}
    <div class="lightbox__media-wrap">
        <div id="lb-media"></div>
        <div class="lightbox__info">
            <p id="lb-caption" class="lightbox__caption"></p>
        </div>
    </div>

    {{-- Next --}}
    <button id="lb-next" class="lightbox__nav lightbox__nav--next" aria-label="Berikutnya">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
        </svg>
    </button>

    {{-- Counter --}}
    <div id="lb-counter" class="lightbox__counter" aria-live="polite"></div>

</div>

{{-- Gallery JS --}}
<script src="{{ asset('js/gallery.js') }}"></script>

{{-- Keyboard accessibility: open lightbox on Enter/Space --}}
<script>
document.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ') {
        var focused = document.activeElement;
        if (focused && focused.classList.contains('masonry-item')) {
            e.preventDefault();
            focused.click();
        }
    }
});
</script>

</body>
</html>
