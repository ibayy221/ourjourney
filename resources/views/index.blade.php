<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Putri Daun & Ubay  Our Journey</title>
    <meta name="description" content="Sebuah catatan perjalanan cinta kami — Putri Daun dan Ubay. Dari benih kecil yang tumbuh menjadi pohon yang kuat.">

    {{-- Public CSS (design tokens + animations) --}}
    <link rel="stylesheet" href="{{ asset('css/public.css') }}">

    {{-- GSAP 3 + ScrollTrigger via CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    
    {{-- Alpine.js for interactive carousels --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════════
     NAVIGATION
══════════════════════════════════════════════════════════════ --}}
<nav class="pub-nav" role="navigation" aria-label="Navigasi utama">
    <span class="pub-nav__brand">Putri Daun <span>&amp;</span> Ubay</span>
    <ul class="pub-nav__links">
        <li><a href="#milestones" class="active">Perjalanan</a></li>
        <li><a href="#chapters">Bab</a></li>
        <li><a href="#wishlist">Wishlist</a></li>
        <li><a href="/gallery">Galeri</a></li>
        <li><a href="/cinema">Cinema</a></li>
    </ul>
</nav>

{{-- ══════════════════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════════════ --}}
<section class="hero" id="hero" aria-label="Hero">
    {{-- Decorative background petal blobs --}}
    <div class="hero__bg-petals" aria-hidden="true">
        <svg width="100%" height="100%" viewBox="0 0 1200 800" fill="none" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
            <circle cx="200" cy="150" r="320" fill="rgba(143,162,131,0.08)"/>
            <circle cx="1050" cy="650" r="280" fill="rgba(201,123,132,0.07)"/>
            <circle cx="900" cy="100" r="180" fill="rgba(199,161,90,0.06)"/>
        </svg>
    </div>

    <p class="hero__eyebrow">✦ sebuah perjalanan cinta ✦</p>
    <h1 class="hero__title">Our <em>Journey</em></h1>
    <p class="hero__subtitle">Dari benih kecil yang jatuh, tumbuh sebuah pohon yang kuat — milik kami berdua.</p>

    <div class="hero__seed" aria-hidden="true" title="Benih cinta kami"></div>

    <a href="#milestones" class="hero__scroll-hint" aria-label="Gulir ke bawah">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 5v14M5 12l7 7 7-7"/>
        </svg>
        <span>scroll</span>
    </a>
</section>

{{-- ══════════════════════════════════════════════════════════════
     TREE SECTION (SVG trunk grows with scroll)
══════════════════════════════════════════════════════════════ --}}
<section class="tree-section" id="tree-section" aria-hidden="true"
         style="height: {{ max(400, ($milestones->count() + 1) * 350) }}px; position: relative;">

    {{-- Sticky SVG container --}}
    <div class="tree-svg-container">
        <svg id="trunk-svg"
             viewBox="0 0 200 800"
             width="160"
             height="640"
             xmlns="http://www.w3.org/2000/svg"
             style="overflow: visible;">

            {{-- Organic trunk path — curves inspired by a real tree --}}
            <path id="trunk-path"
                  d="M100,790
                     C100,750 98,720 102,700
                     C106,680 95,655 100,640
                     C105,625 108,600 100,580
                     C92,560 97,540 103,520
                     C109,500 96,480 100,460
                     C104,440 108,415 98,395
                     C88,375 104,350 100,330
                     C96,310 103,285 100,260
                     C97,235 107,210 100,185
                     C93,160 101,135 100,110
                     C99,85 98,55 100,30"
                  fill="none"
                  stroke="#435A3C"
                  stroke-width="4.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"/>

            {{-- Small branch left near bottom --}}
            <path d="M100,620 Q75,595 55,580" fill="none" stroke="#8FA283" stroke-width="2.5" stroke-linecap="round" opacity="0.5"/>
            {{-- Small branch right --}}
            <path d="M100,510 Q128,488 148,478" fill="none" stroke="#8FA283" stroke-width="2.5" stroke-linecap="round" opacity="0.5"/>
            {{-- Mid branch left --}}
            <path d="M100,380 Q68,355 44,348" fill="none" stroke="#8FA283" stroke-width="2" stroke-linecap="round" opacity="0.4"/>
            {{-- Mid branch right --}}
            <path d="M100,260 Q136,238 158,228" fill="none" stroke="#8FA283" stroke-width="2" stroke-linecap="round" opacity="0.4"/>
            {{-- Upper branch left --}}
            <path d="M100,155 Q72,130 52,118" fill="none" stroke="#C97B84" stroke-width="1.8" stroke-linecap="round" opacity="0.5"/>
            {{-- Leaf blobs along trunk --}}
            <ellipse cx="50" cy="576" rx="12" ry="8" fill="#8FA283" opacity="0.25" transform="rotate(-20,50,576)"/>
            <ellipse cx="152" cy="475" rx="10" ry="7" fill="#8FA283" opacity="0.25" transform="rotate(15,152,475)"/>
            <ellipse cx="40" cy="345" rx="9" ry="6" fill="#C97B84" opacity="0.2" transform="rotate(-10,40,345)"/>
            <ellipse cx="162" cy="225" rx="11" ry="7" fill="#8FA283" opacity="0.25" transform="rotate(25,162,225)"/>
            <ellipse cx="48" cy="115" rx="8" ry="6" fill="#C97B84" opacity="0.2" transform="rotate(-15,48,115)"/>
        </svg>

        {{-- Sprout tip following the trunk head --}}
        <div id="trunk-sprout"
             style="position:absolute; top:50%; left:50%; width:20px; height:20px; pointer-events:none; transform-origin: center bottom;">
            <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 18 C10 18 10 12 10 8 C10 4 14 1 18 2 C18 6 14 9 10 8" fill="#8FA283" opacity="0.9"/>
                <path d="M10 18 C10 18 10 12 10 8 C10 4 6 1 2 2 C2 6 6 9 10 8" fill="#C97B84" opacity="0.7"/>
            </svg>
        </div>
    </div>

</section>

{{-- ══════════════════════════════════════════════════════════════
     MILESTONES
══════════════════════════════════════════════════════════════ --}}
<section id="milestones" aria-label="Tonggak perjalanan" style="padding: 0 0 80px;">
    <div class="container">
        @if($milestones->isEmpty())
            <div class="empty-state">
                <div class="empty-state__icon">🌱</div>
                <h2 class="empty-state__title">Perjalanan sedang dimulai…</h2>
                <p class="empty-state__text">Momen-momen spesial akan muncul di sini segera.</p>
            </div>
        @else
            <div class="milestones-track">
                @foreach($milestones as $index => $milestone)
                    <article class="milestone-card {{ $index % 2 === 0 ? 'is-left' : 'is-right' }}"
                             id="milestone-{{ $milestone->id }}">

                        <div class="milestone-card__content">
                            @if($milestone->event_date)
                                <p class="milestone-card__date">
                                    {{ $milestone->event_date->translatedFormat('d F Y') }}
                                </p>
                            @endif
                            <h2 class="milestone-card__title">{{ $milestone->title ?? 'Momen Spesial' }}</h2>
                            @if($milestone->caption)
                                <p class="milestone-card__caption">{{ $milestone->caption }}</p>
                            @endif
                        </div>

                        <div class="milestone-card__dot" aria-hidden="true">
                            <div class="milestone-card__dot-circle"></div>
                            @if(!$loop->last)
                                <div class="milestone-card__dot-line"></div>
                            @endif
                        </div>

                        <div class="milestone-card__media">
                            @if($milestone->media->count() > 1)
                                <div class="media-carousel" x-data="{ activeIndex: 0, total: {{ $milestone->media->count() }} }">
                                    <div class="media-carousel__track" :style="'transform: translateX(-' + (activeIndex * 100) + '%)'">
                                        @foreach($milestone->media as $mediaItem)
                                            <div class="media-carousel__slide">
                                                @if($mediaItem->type === 'video')
                                                    @if($mediaItem->is_youtube)
                                                        <iframe src="https://www.youtube.com/embed/{{ $mediaItem->youtube_id }}" frameborder="0" allowfullscreen class="w-full h-full object-cover"></iframe>
                                                    @else
                                                        <video src="{{ $mediaItem->file_url }}" muted loop playsinline controls class="w-full h-full object-cover"></video>
                                                    @endif
                                                @else
                                                    <img src="{{ $mediaItem->file_url }}" alt="{{ $milestone->title ?? 'Foto momen' }}" class="w-full h-full object-cover" loading="lazy">
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <button class="media-carousel__btn is-prev" @click="activeIndex = (activeIndex === 0) ? total - 1 : activeIndex - 1" x-show="total > 1">‹</button>
                                    <button class="media-carousel__btn is-next" @click="activeIndex = (activeIndex === total - 1) ? 0 : activeIndex + 1" x-show="total > 1">›</button>
                                    <div class="media-carousel__indicators" x-show="total > 1">
                                        <template x-for="(item, idx) in total" :key="idx">
                                            <span class="media-carousel__dot" :class="activeIndex === idx ? 'is-active' : ''" @click="activeIndex = idx"></span>
                                        </template>
                                    </div>
                                </div>
                            @elseif($milestone->media->count() === 1)
                                @php $mediaItem = $milestone->media->first(); @endphp
                                @if($mediaItem->type === 'video')
                                    @if($mediaItem->is_youtube)
                                        <iframe src="https://www.youtube.com/embed/{{ $mediaItem->youtube_id }}" frameborder="0" allowfullscreen style="width:100%;height:100%;object-fit:cover;"></iframe>
                                    @else
                                        <video src="{{ $mediaItem->file_url }}" muted loop playsinline controls style="width:100%;height:100%;object-fit:cover;" aria-label="{{ $milestone->title ?? 'Video momen' }}"></video>
                                    @endif
                                @else
                                    <img src="{{ $mediaItem->file_url }}" alt="{{ $milestone->title ?? 'Foto momen' }}" loading="lazy">
                                @endif
                            @endif
                        </div>

                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════════
     BRANCH CHAPTERS (kenangan per bab)
══════════════════════════════════════════════════════════════ --}}
@if($branches->isNotEmpty())
<section id="chapters" class="branch-section" aria-label="Bab kenangan">
    <div class="container">
        <div style="text-align:center; margin-bottom:60px;">
            <p style="font-family:'Space Mono',monospace; font-size:0.7rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--green-soft); margin-bottom:12px;">
                kenangan kami
            </p>
            <h2 style="font-size:clamp(1.8rem,4vw,3rem); color:var(--green-deep); font-weight:300; font-style:italic;">
                Bab demi Bab
            </h2>
        </div>

        @foreach($branches as $chapterName => $items)
            <div class="branch-chapter" id="chapter-{{ Str::slug($chapterName ?? 'chapter') }}">
                <p class="branch-chapter__label">{{ $chapterName ?? 'Kenangan' }}</p>
                <h3 class="branch-chapter__title">{{ $items->first()->chapter ?? $chapterName }}</h3>
                <div class="branch-grid">
                    @foreach($items as $item)
                        <div class="branch-card">
                            <div class="branch-item" tabindex="0"
                                 aria-label="{{ $item->caption ?? 'Memory' }}" style="overflow: hidden; position: relative;">
                                @if($item->media->count() > 1)
                                    <div class="media-carousel" x-data="{ activeIndex: 0, total: {{ $item->media->count() }} }" style="width:100%;height:100%;">
                                        <div class="media-carousel__track" :style="'transform: translateX(-' + (activeIndex * 100) + '%)'" style="width:100%;height:100%;">
                                            @foreach($item->media as $mediaItem)
                                                <div class="media-carousel__slide" style="width:100%;height:100%;">
                                                    @if($mediaItem->type === 'video')
                                                        @if($mediaItem->is_youtube)
                                                            <iframe src="https://www.youtube.com/embed/{{ $mediaItem->youtube_id }}" frameborder="0" allowfullscreen class="w-full h-full object-cover"></iframe>
                                                        @else
                                                            <video src="{{ $mediaItem->file_url }}" muted loop playsinline class="w-full h-full object-cover"></video>
                                                        @endif
                                                    @else
                                                        <img src="{{ $mediaItem->file_url }}" alt="{{ $item->caption ?? 'Kenangan' }}" class="w-full h-full object-cover" loading="lazy">
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        <button class="media-carousel__btn is-prev" @click.stop="activeIndex = (activeIndex === 0) ? total - 1 : activeIndex - 1" x-show="total > 1" style="font-size: 1.2rem;">‹</button>
                                        <button class="media-carousel__btn is-next" @click.stop="activeIndex = (activeIndex === total - 1) ? 0 : activeIndex + 1" x-show="total > 1" style="font-size: 1.2rem;">›</button>
                                        <div class="media-carousel__indicators" x-show="total > 1">
                                            <template x-for="(mItem, idx) in total" :key="idx">
                                                <span class="media-carousel__dot" :class="activeIndex === idx ? 'is-active' : ''" @click.stop="activeIndex = idx"></span>
                                            </template>
                                        </div>
                                    </div>
                                @elseif($item->media->count() === 1)
                                    @php $mediaItem = $item->media->first(); @endphp
                                    @if($mediaItem->type === 'video')
                                        @if($mediaItem->is_youtube)
                                            <iframe src="https://www.youtube.com/embed/{{ $mediaItem->youtube_id }}" frameborder="0" allowfullscreen style="width:100%;height:100%;object-fit:cover;"></iframe>
                                        @else
                                            <video src="{{ $mediaItem->file_url }}" muted loop playsinline style="width:100%;height:100%;object-fit:cover;"></video>
                                        @endif
                                    @else
                                        <img src="{{ $mediaItem->file_url }}" alt="{{ $item->caption ?? 'Kenangan' }}" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
                                    @endif
                                @endif
                            </div>
                            @if($item->caption)
                                <p class="branch-card__caption">{{ $item->caption }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════════════════════
     WISHLIST (Daftar Impian Bersama)
══════════════════════════════════════════════════════════════ --}}
<section id="wishlist" class="wishlist-section" aria-label="Daftar impian bersama">
    <div class="container">
        <div class="wishlist-header">
            <p class="wishlist-eyebrow">mimpi & harapan</p>
            <h2 class="wishlist-title">Wishlist <em>Bersama</em></h2>
            <p class="wishlist-subtitle">Hal-hal manis yang ingin kami wujudkan dan yang telah menjadi nyata 🌱</p>
        </div>

        @if($wishlists->isEmpty())
            <div class="empty-state">
                <div class="empty-state__icon">🌱</div>
                <h2 class="empty-state__title">Perjalanan Mimpi Kita…</h2>
                <p class="empty-state__text">Rencana-rencana indah akan segera muncul di sini.</p>
            </div>
        @else
            <div class="wishlist-grid">
                @foreach($wishlists as $wishlist)
                    <div class="wishlist-item {{ $wishlist->is_completed ? 'is-completed' : '' }}">
                        <div class="wishlist-item__status">
                            @if($wishlist->is_completed)
                                <div class="wishlist-item__icon-completed" title="Sudah Tercapai">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 6L9 17l-5-5"/>
                                    </svg>
                                </div>
                            @else
                                <div class="wishlist-item__icon-active" title="Dalam Rencana">
                                    <span class="wishlist-item__dot"></span>
                                </div>
                            @endif
                        </div>
                        <div class="wishlist-item__info">
                            <h3 class="wishlist-item__title">{{ $wishlist->title }}</h3>
                            @if($wishlist->description)
                                <p class="wishlist-item__description">{{ $wishlist->description }}</p>
                            @endif
                            @if($wishlist->is_completed && $wishlist->completed_at)
                                <p class="wishlist-item__completed-at">Tercapai pada {{ $wishlist->completed_at->translatedFormat('d M Y') }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════════
     COUNTER — hari bersama
══════════════════════════════════════════════════════════════ --}}
<section id="counter-section" class="counter-section" aria-label="Hitungan hari bersama">
    <div class="container">
        <p class="counter-eyebrow">sudah bersama selama</p>
        <div class="counter-number">
            <span id="days-counter" data-start-date="{{ $startDate }}">
                …
            </span>
        </div>
        <p class="counter-label">hari yang penuh cinta 🌿</p>
        <a href="/gallery" class="counter-cta" id="gallery-cta-link">
            Lihat Semua Kenangan
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════════════ --}}
<footer class="pub-footer" role="contentinfo">
    <p class="pub-footer__text">
        dibuat dengan <span class="pub-footer__heart">♥</span> untuk Putri Daun &amp; Ubay
        &nbsp;·&nbsp; {{ date('Y') }}
    </p>
</footer>

{{-- Script --}}
<script src="{{ asset('js/script.js') }}"></script>

{{-- Milestone videos: autoplay on scroll ─────────── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isMobile = window.matchMedia('(max-width: 720px)').matches;
    document.querySelectorAll('.milestone-card video').forEach(function (video) {
        if (isMobile) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) { video.play().catch(function(){}); }
                    else { video.pause(); }
                });
            }, { threshold: 0.5 });
            io.observe(video);
        } else {
            var card = video.closest('.milestone-card');
            if (card) {
                card.addEventListener('mouseenter', function () { video.play().catch(function(){}); });
                card.addEventListener('mouseleave', function () { video.pause(); });
            }
        }
    });
});
</script>

</body>
</html>
