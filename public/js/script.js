/* ============================================================
   script.js — Our Journey Landing Page
   GSAP 3 + ScrollTrigger: growing tree animation, milestone
   reveals, branch clusters, and the "days together" counter.
   ============================================================ */

(function () {
  'use strict';

  // ── prefers-reduced-motion guard ────────────────────────────────
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ── Register GSAP plugins ────────────────────────────────────────
  gsap.registerPlugin(ScrollTrigger);

  // ── Nav shadow on scroll ────────────────────────────────────────
  const nav = document.querySelector('.pub-nav');
  if (nav) {
    ScrollTrigger.create({
      start: 'top -60',
      onUpdate: (self) => {
        nav.classList.toggle('scrolled', self.progress > 0);
      },
    });
  }

  // ── Helper: reveal on enter ─────────────────────────────────────
  function revealOnEnter(targets, fromVars = {}, toVars = {}) {
    if (prefersReduced) {
      gsap.set(targets, { opacity: 1, ...toVars });
      return;
    }
    gsap.utils.toArray(targets).forEach((el) => {
      gsap.fromTo(
        el,
        { opacity: 0, y: 40, ...fromVars },
        {
          opacity: 1,
          y: 0,
          duration: 0.9,
          ease: 'power3.out',
          scrollTrigger: {
            trigger: el,
            start: 'top 88%',
            toggleActions: 'play none none none',
          },
          ...toVars,
        }
      );
    });
  }

  // ────────────────────────────────────────────────────────────────
  // SVG TRUNK GROWING ANIMATION
  // ────────────────────────────────────────────────────────────────
  const trunkPath = document.getElementById('trunk-path');
  const sprout = document.getElementById('trunk-sprout');
  const treeSection = document.getElementById('tree-section');
  const trunkSvg = document.getElementById('trunk-svg');

  if (trunkPath && !prefersReduced) {
    const totalLength = trunkPath.getTotalLength();
    const sproutGroups = document.querySelectorAll('.sprout-group');

    // Set initial state: fully hidden stroke
    gsap.set(trunkPath, {
      strokeDasharray: totalLength,
      strokeDashoffset: totalLength,
    });
    gsap.set(sprout, { opacity: 0, scale: 0.2, transformOrigin: 'center bottom' });

    // Animate trunk drawing with scroll scrub
    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: treeSection,
        start: 'top top',
        end: 'bottom bottom',
        scrub: 1.2,
      },
    });

    tl.to(trunkPath, {
      strokeDashoffset: 0,
      ease: 'none',
    });

    // Track sprout tip at current draw progress
    ScrollTrigger.create({
      trigger: treeSection,
      start: 'top top',
      end: 'bottom bottom',
      scrub: true,
      onUpdate: (self) => {
        if (!trunkPath || !sprout) return;
        const progress = self.progress;
        const drawnLength = totalLength * progress;
        const pt = trunkPath.getPointAtLength(drawnLength);
        const svgRect = trunkSvg.getBoundingClientRect();
        const viewBox = trunkSvg.viewBox.baseVal;

        // Convert SVG coords → % for positioning
        const xPct = (pt.x / viewBox.width) * 100;
        const yPct = (pt.y / viewBox.height) * 100;

        gsap.set(sprout, {
          left: xPct + '%',
          top: yPct + '%',
          opacity: progress > 0.02 ? 1 : 0,
          scale: 0.5 + progress * 0.8,
        });

        // Sprout branches and leaves as trunk progress passes their threshold
        sproutGroups.forEach((group) => {
          const triggerVal = parseFloat(group.dataset.triggerProgress || '0');
          const isGrown = progress >= triggerVal;

          gsap.to(group, {
            scale: isGrown ? 1 : 0,
            opacity: isGrown ? 1 : 0,
            duration: 0.4,
            ease: isGrown ? 'back.out(2)' : 'power2.inOut',
            overwrite: 'auto'
          });
        });
      },
    });
  } else if (trunkPath && prefersReduced) {
    // Just show full trunk immediately
    gsap.set(trunkPath, { strokeDasharray: 'none', strokeDashoffset: 0 });
    gsap.set(sprout, { opacity: 1, scale: 1 });
  }

  // ────────────────────────────────────────────────────────────────
  // MILESTONE CARDS — alternating left/right reveals
  // ────────────────────────────────────────────────────────────────
  if (!prefersReduced) {
    gsap.utils.toArray('.milestone-card').forEach((card) => {
      const isRight = card.classList.contains('is-right');
      gsap.fromTo(
        card,
        { opacity: 0, x: isRight ? -40 : 40, y: 30 },
        {
          opacity: 1,
          x: 0,
          y: 0,
          duration: 1,
          ease: 'power3.out',
          scrollTrigger: {
            trigger: card,
            start: 'top 85%',
            toggleActions: 'play none none none',
          },
        }
      );
    });
  } else {
    document.querySelectorAll('.milestone-card').forEach((c) => {
      c.style.opacity = 1;
      c.style.transform = 'none';
    });
  }

  // ────────────────────────────────────────────────────────────────
  // BRANCH CLUSTERS — slight rotation reveal
  // ────────────────────────────────────────────────────────────────
  if (!prefersReduced) {
    gsap.utils.toArray('.branch-chapter').forEach((chapter, i) => {
      const dir = i % 2 === 0 ? 1 : -1;
      gsap.fromTo(
        chapter,
        { opacity: 0, y: 40, rotate: dir * 1.5 },
        {
          opacity: 1,
          y: 0,
          rotate: 0,
          duration: 1,
          ease: 'power3.out',
          scrollTrigger: {
            trigger: chapter,
            start: 'top 88%',
            toggleActions: 'play none none none',
          },
        }
      );
    });

    // Branch items stagger
    document.querySelectorAll('.branch-chapter').forEach((chapter) => {
      const items = gsap.utils.toArray(chapter.querySelectorAll('.branch-item'));
      if (!items.length) return;
      ScrollTrigger.batch(items, {
        start: 'top 90%',
        onEnter: (batch) => {
          gsap.fromTo(
            batch,
            { opacity: 0, scale: 0.9 },
            { opacity: 1, scale: 1, stagger: 0.08, duration: 0.6, ease: 'power2.out' }
          );
        },
      });
    });
  } else {
    document.querySelectorAll('.branch-chapter, .branch-item').forEach((el) => {
      el.style.opacity = 1;
      el.style.transform = 'none';
    });
  }

  // ────────────────────────────────────────────────────────────────
  // COUNTER SECTION — days together
  // ────────────────────────────────────────────────────────────────
  const counterEl = document.getElementById('days-counter');
  const counterSection = document.getElementById('counter-section');

  if (counterEl && counterSection) {
    let startDate = null;
    const dateStr = counterEl.dataset.startDate;
    if (dateStr) {
      const parts = dateStr.split('T')[0].split('-');
      if (parts.length === 3) {
        startDate = new Date(parts[0], parseInt(parts[1], 10) - 1, parts[2], 0, 0, 0);
      } else {
        startDate = new Date(dateStr);
      }
    }

    if (startDate && !isNaN(startDate.getTime())) {
      const msDiff = Date.now() - startDate.getTime();
      const totalDays = Math.max(0, Math.floor(msDiff / (1000 * 60 * 60 * 24)));

      if (!prefersReduced) {
        ScrollTrigger.create({
          trigger: counterSection,
          start: 'top 75%',
          once: true,
          onEnter: () => {
            const counterObj = { val: 0 };
            gsap.to(counterObj, {
              val: totalDays,
              duration: 2.5,
              ease: 'power2.out',
              onUpdate() {
                counterEl.textContent = Math.round(counterObj.val).toLocaleString('id-ID');
              },
            });
            // fade in counter section
            gsap.fromTo(
              gsap.utils.toArray(counterSection.querySelectorAll('.counter-eyebrow, .counter-label, .counter-cta')),
              { opacity: 0, y: 24 },
              { opacity: 1, y: 0, stagger: 0.15, duration: 0.8, ease: 'power3.out' }
            );
          },
        });
      } else {
        counterEl.textContent = totalDays.toLocaleString('id-ID');
      }
    } else {
      // No start date configured yet
      counterEl.textContent = '…';
    }
  }

  // ────────────────────────────────────────────────────────────────
  // HERO ENTRANCE
  // ────────────────────────────────────────────────────────────────
  if (!prefersReduced) {
    const heroTl = gsap.timeline({ delay: 0.2 });
    heroTl
      .fromTo('.hero__eyebrow',  { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7, ease: 'power3.out' })
      .fromTo('.hero__title',    { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out' }, '-=0.4')
      .fromTo('.hero__subtitle', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7, ease: 'power3.out' }, '-=0.5')
      .fromTo('.hero__seed',     { opacity: 0, scale: 0 }, { opacity: 1, scale: 1, duration: 0.5, ease: 'back.out(2)' }, '-=0.3')
      .fromTo('.hero__scroll-hint', { opacity: 0 }, { opacity: 1, duration: 0.6 }, '-=0.1');
  }

  // ────────────────────────────────────────────────────────────────
  // Video: autoplay on hover (desktop) / IntersectionObserver (mobile)
  // ────────────────────────────────────────────────────────────────
  function initBranchVideoPlay() {
    document.querySelectorAll('.branch-item video').forEach((video) => {
      video.muted = true;
      video.loop = true;
      video.playsInline = true;

      const isMobile = window.matchMedia('(max-width: 720px)').matches;

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
        io.observe(video);
      } else {
        const item = video.closest('.branch-item');
        if (item) {
          item.addEventListener('mouseenter', () => video.play().catch(() => {}));
          item.addEventListener('mouseleave', () => { video.pause(); video.currentTime = 0; });
        }
      }
    });
  }

  initBranchVideoPlay();

})();
