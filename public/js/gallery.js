/* ============================================================
   gallery.js — Gallery Page
   Masonry reveal, category filters, lightbox with
   scale-from-origin, keyboard nav, and video autoplay.
   ============================================================ */

(function () {
  'use strict';

  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ── State ───────────────────────────────────────────────────────
  let lightboxItems = [];   // array of { el, type, src, caption }
  let currentIndex = 0;
  let lightboxOpen = false;
  let originRect = null;    // for scale-from-origin

  // ── DOM refs ────────────────────────────────────────────────────
  const grid = document.getElementById('masonry-grid');
  const lightbox = document.getElementById('lightbox');
  const lbMedia = document.getElementById('lb-media');
  const lbGlow = document.getElementById('lb-glow');
  const lbCaption = document.getElementById('lb-caption');
  const lbClose = document.getElementById('lb-close');
  const lbPrev = document.getElementById('lb-prev');
  const lbNext = document.getElementById('lb-next');
  const lbCounter = document.getElementById('lb-counter');

  if (!grid) return;

  // ── Build lightbox items list ───────────────────────────────────
  function buildLightboxItems() {
    lightboxItems = Array.from(grid.querySelectorAll('.masonry-item:not(.hidden)')).map((el) => ({
      el,
      type: el.dataset.type,
      src: el.dataset.src,
      caption: el.dataset.caption || '',
      isYoutube: el.dataset.isYoutube === 'true',
      youtubeId: el.dataset.youtubeId || '',
    }));
  }

  // ── Masonry reveal via GSAP ScrollTrigger.batch ─────────────────
  function initReveal() {
    if (prefersReduced) {
      document.querySelectorAll('.masonry-item').forEach((el) => {
        el.style.opacity = 1;
        el.style.transform = 'none';
      });
      return;
    }

    gsap.registerPlugin(ScrollTrigger);

    ScrollTrigger.batch('.masonry-item', {
      start: 'top 95%',
      interval: 0.06,
      onEnter: (batch) => {
        gsap.to(batch, {
          opacity: 1,
          scale: 1,
          y: 0,
          duration: 0.65,
          stagger: 0.05,
          ease: 'power2.out',
          overwrite: 'auto',
        });
      },
    });
  }

  // ── Category Filters ─────────────────────────────────────────────
  function initFilters() {
    const filterBtns = document.querySelectorAll('.gallery-filter-btn');

    filterBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        const cat = btn.dataset.category;

        // Update active state
        filterBtns.forEach((b) => b.classList.remove('is-active'));
        btn.classList.add('is-active');

        // Show/hide items
        const allItems = grid.querySelectorAll('.masonry-item');
        allItems.forEach((item) => {
          if (cat === 'all' || item.dataset.category === cat) {
            item.classList.remove('hidden');
          } else {
            item.classList.add('hidden');
          }
        });

        // Rebuild lightbox list after filtering
        buildLightboxItems();

        // Refresh ScrollTrigger after layout change
        if (!prefersReduced) {
          ScrollTrigger.refresh();
        }
      });
    });
  }

  // ── Lightbox helpers ─────────────────────────────────────────────
  function openLightbox(index) {
    currentIndex = index;
    const item = lightboxItems[index];
    if (!item) return;

    originRect = item.el.getBoundingClientRect();

    renderLightboxMedia(item);
    lightbox.classList.add('is-open');
    lightboxOpen = true;
    document.body.style.overflow = 'hidden';

    updateCounter();
  }

  function closeLightbox() {
    lightbox.classList.remove('is-open');
    lightboxOpen = false;
    document.body.style.overflow = '';

    // Pause any playing video
    const video = lbMedia.querySelector('video');
    if (video) video.pause();

    if (lbGlow) {
      const glowVideo = lbGlow.querySelector('video');
      if (glowVideo) glowVideo.pause();
    }
  }

  function renderLightboxMedia(item) {
    lbMedia.innerHTML = '';
    if (lbGlow) lbGlow.innerHTML = '';
    lbCaption.textContent = item.caption;

    if (item.type === 'video') {
      if (item.isYoutube && item.youtubeId) {
        // Main video player
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube.com/embed/${item.youtubeId}?autoplay=1&rel=0`;
        iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
        iframe.allowFullscreen = true;
        lbMedia.appendChild(iframe);

        // Ambient glow (blurred youtube thumbnail image)
        if (lbGlow) {
          const glowImg = document.createElement('img');
          glowImg.src = `https://img.youtube.com/vi/${item.youtubeId}/hqdefault.jpg`;
          lbGlow.appendChild(glowImg);
        }
      } else {
        // Main video player
        const video = document.createElement('video');
        video.src = item.src;
        video.controls = true;
        video.autoplay = true;
        video.playsInline = true;
        video.muted = false;
        lbMedia.appendChild(video);

        // Ambient glow (blurred duplicated video element, muted, synced)
        if (lbGlow) {
          const glowVideo = document.createElement('video');
          glowVideo.src = item.src;
          glowVideo.autoplay = true;
          glowVideo.loop = true;
          glowVideo.muted = true;
          glowVideo.playsInline = true;
          lbGlow.appendChild(glowVideo);

          // Sync the time of the glow video with the main video
          video.addEventListener('timeupdate', () => {
            if (Math.abs(glowVideo.currentTime - video.currentTime) > 0.3) {
              glowVideo.currentTime = video.currentTime;
            }
          });
          video.addEventListener('play', () => glowVideo.play().catch(() => {}));
          video.addEventListener('pause', () => {
            glowVideo.pause();
          });
        }
      }
    } else {
      // Main image
      const img = document.createElement('img');
      img.src = item.src;
      img.alt = item.caption || 'Memory';
      lbMedia.appendChild(img);

      // Ambient glow (blurred copy of same image)
      if (lbGlow) {
        const glowImg = document.createElement('img');
        glowImg.src = item.src;
        glowImg.alt = '';
        lbGlow.appendChild(glowImg);
      }
    }
  }

  function navigateLightbox(dir) {
    const total = lightboxItems.length;
    currentIndex = (currentIndex + dir + total) % total;

    // Pause current video before switching
    const currentVideo = lbMedia.querySelector('video');
    if (currentVideo) currentVideo.pause();

    if (!prefersReduced) {
      gsap.fromTo(lbMedia,
        { opacity: 0, x: dir * 40 },
        { opacity: 1, x: 0, duration: 0.35, ease: 'power2.out' }
      );
    }

    renderLightboxMedia(lightboxItems[currentIndex]);
    updateCounter();
  }

  function updateCounter() {
    if (lbCounter) {
      lbCounter.textContent = `${currentIndex + 1} / ${lightboxItems.length}`;
    }
  }

  // ── Attach lightbox triggers ──────────────────────────────────────
  function attachItemClicks() {
    grid.addEventListener('click', (e) => {
      const item = e.target.closest('.masonry-item');
      if (!item || item.classList.contains('hidden')) return;

      buildLightboxItems();
      const index = lightboxItems.findIndex((i) => i.el === item);
      if (index !== -1) openLightbox(index);
    });
  }

  // ── Keyboard navigation ──────────────────────────────────────────
  document.addEventListener('keydown', (e) => {
    if (!lightboxOpen) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') navigateLightbox(-1);
    if (e.key === 'ArrowRight') navigateLightbox(1);
  });

  // ── Close on backdrop click ──────────────────────────────────────
  lightbox && lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) closeLightbox();
  });

  lbClose && lbClose.addEventListener('click', closeLightbox);
  lbPrev && lbPrev.addEventListener('click', () => navigateLightbox(-1));
  lbNext && lbNext.addEventListener('click', () => navigateLightbox(1));

  // ── Video autoplay in masonry (IntersectionObserver) ─────────────
  function initVideoAutoplay() {
    const isMobile = window.matchMedia('(max-width: 720px)').matches;

    document.querySelectorAll('.masonry-item[data-type="video"]').forEach((item) => {
      const video = item.querySelector('video');
      if (!video) return;

      video.muted = true;
      video.loop = true;
      video.playsInline = true;

      if (isMobile) {
        const io = new IntersectionObserver(
          ([entry]) => {
            if (entry.isIntersecting) {
              video.play().catch(() => {});
            } else {
              video.pause();
            }
          },
          { threshold: 0.5 }
        );
        io.observe(item);
      } else {
        item.addEventListener('mouseenter', () => video.play().catch(() => {}));
        item.addEventListener('mouseleave', () => { video.pause(); video.currentTime = 0; });
      }
    });
  }

  // ── Nav scroll shadow ────────────────────────────────────────────
  const nav = document.querySelector('.pub-nav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });
  }

  // ── Init ────────────────────────────────────────────────────────
  buildLightboxItems();
  initReveal();
  initFilters();
  attachItemClicks();
  initVideoAutoplay();

})();
