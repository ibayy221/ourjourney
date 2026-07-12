<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Galeri Kenangan — Putri Daun &amp; Ubay</title>
    <meta name="description" content="Semua foto dan video kenangan perjalanan cinta Putri Daun dan Ubay.">

    {{-- Public CSS --}}
    <link rel="stylesheet" href="{{ asset('css/public.css') }}?v=1.0.5">

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
        <li><a href="/gallery" class="active">Galeri</a></li>
        <li><a href="/cinema">Cinema</a></li>
    </ul>
</nav>

{{-- ══════════════════════════════════════════════════════════════
     GALLERY HERO
══════════════════════════════════════════════════════════════ --}}
<header class="gallery-hero" role="banner">
    <p style="font-family:'Space Mono',monospace; font-size:0.7rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--green-soft); margin-bottom:16px;">
        ✦ kenangan kami ✦
    </p>
    <h1 class="gallery-hero__title">Galeri <em style="font-style:italic; color:var(--bloom);">Kenangan</em></h1>
    <p class="gallery-hero__subtitle">Setiap foto adalah halaman dari buku cerita kita.</p>
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
     MASONRY GRID
══════════════════════════════════════════════════════════════ --}}
@if($items->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">🖼️</div>
        <h2 class="empty-state__title">Galeri masih kosong</h2>
        <p class="empty-state__text">Foto dan video kenangan akan tampil di sini.</p>
    </div>
@else
    <div class="masonry-grid" id="masonry-grid" role="list" aria-label="Galeri foto dan video">
        @foreach($items as $item)
            <div class="masonry-item"
                 role="listitem"
                 tabindex="0"
                 data-id="{{ $item['id'] }}"
                 data-type="{{ $item['type'] }}"
                 data-src="{{ $item['file_url'] }}"
                 data-caption="{{ $item['caption'] ?? '' }}"
                 data-category="{{ $item['category'] ?? '' }}"
                 data-is-youtube="false"
                 data-youtube-id=""
                 aria-label="{{ $item['caption'] ?? 'Foto kenangan' }}"
                 style="cursor:pointer;">

                <img src="{{ $item['file_url'] }}"
                     alt="{{ $item['caption'] ?? 'Foto kenangan' }}"
                     loading="lazy">

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

    <div class="lightbox__media-wrap" style="position: relative;">
        <div id="lb-glow" class="lightbox__glow"></div>
        <div id="lb-media" style="position: relative; z-index: 2;"></div>
        <div class="lightbox__info" style="position: relative; z-index: 2;">
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
