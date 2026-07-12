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

  // ── Background Audio Controller ───────────────────────────────
  function initBackgroundAudio() {
    const musicCtrl = document.getElementById('music-ctrl');
    const musicBtn = document.getElementById('music-btn');
    const audio = document.getElementById('bg-audio');

    if (!musicBtn || !audio) return;

    // Toggle Play/Pause on click
    musicBtn.addEventListener('click', (e) => {
      e.stopPropagation(); // Avoid triggering document click
      if (audio.paused) {
        audio.volume = 0.4;
        audio.play()
          .then(() => {
            musicCtrl.classList.add('is-playing');
          })
          .catch((err) => {
            console.error('Audio playback failed:', err);
          });
      } else {
        audio.pause();
        musicCtrl.classList.remove('is-playing');
      }
    });

    // Handle user interaction for autoplay policy (smooth fade-in)
    const handleFirstInteraction = () => {
      if (audio.paused && !musicCtrl.classList.contains('is-playing')) {
        audio.volume = 0;
        audio.play().then(() => {
          musicCtrl.classList.add('is-playing');
          // Smooth fade in volume to 0.4
          let vol = 0;
          const interval = setInterval(() => {
            if (vol < 0.4) {
              vol += 0.05;
              audio.volume = vol;
            } else {
              clearInterval(interval);
            }
          }, 100);
        }).catch(() => {
          // Autoplay blocked by browser, wait for button click
        });
      }
      
      // Cleanup listeners
      window.removeEventListener('click', handleFirstInteraction);
      window.removeEventListener('scroll', handleFirstInteraction);
    };

    window.addEventListener('click', handleFirstInteraction, { once: true });
    window.addEventListener('scroll', handleFirstInteraction, { once: true });
  }

  // ── Floating Fireflies (Kunang-Kunang) Canvas ──────────────────
  function initFireflies() {
    const canvas = document.getElementById('fireflies-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width = (canvas.width = window.innerWidth);
    let height = (canvas.height = window.innerHeight);

    const particles = [];
    const particleCount = Math.min(35, Math.floor((width * height) / 30000));

    class Particle {
      constructor() {
        this.reset();
        this.y = Math.random() * height;
      }

      reset() {
        this.x = Math.random() * width;
        this.y = height + Math.random() * 20;
        this.size = Math.random() * 2 + 1;
        this.speedY = -(Math.random() * 0.4 + 0.2);
        this.speedX = 0;
        this.angle = Math.random() * Math.PI * 2;
        this.swaySpeed = Math.random() * 0.02 + 0.01;
        this.swayAmplitude = Math.random() * 0.5 + 0.2;
        this.opacity = 0;
        this.maxOpacity = Math.random() * 0.5 + 0.3;
        this.fadeSpeed = Math.random() * 0.01 + 0.005;
        this.fadingIn = true;
      }

      update() {
        this.y += this.speedY;
        this.angle += this.swaySpeed;
        this.x += Math.sin(this.angle) * this.swayAmplitude;

        if (this.fadingIn) {
          this.opacity += this.fadeSpeed;
          if (this.opacity >= this.maxOpacity) {
            this.opacity = this.maxOpacity;
            this.fadingIn = false;
          }
        } else {
          if (this.y < height * 0.15) {
            this.opacity -= this.fadeSpeed * 1.5;
          }
        }

        if (this.y < -10 || (this.opacity <= 0 && !this.fadingIn)) {
          this.reset();
        }
      }

      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(229, 192, 123, ${this.opacity})`;
        ctx.shadowBlur = this.size * 3;
        ctx.shadowColor = 'rgba(229, 192, 123, 0.8)';
        ctx.fill();
      }
    }

    for (let i = 0; i < particleCount; i++) {
      particles.push(new Particle());
    }

    let animationFrameId = null;
    let isTabActive = true;

    function animate() {
      if (!isTabActive) return;
      ctx.shadowBlur = 0;
      ctx.clearRect(0, 0, width, height);

      particles.forEach((p) => {
        p.update();
        p.draw();
      });

      animationFrameId = requestAnimationFrame(animate);
    }

    function handleResize() {
      width = canvas.width = window.innerWidth;
      height = canvas.height = window.innerHeight;
    }

    window.addEventListener('resize', handleResize, { passive: true });

    document.addEventListener('visibilitychange', () => {
      isTabActive = !document.hidden;
      if (isTabActive) {
        animate();
      } else {
        cancelAnimationFrame(animationFrameId);
      }
    });

    animate();
  }

  // ── 3D Forest Parallax Effect ────────────────────────────────
  function init3DParallax() {
    const hero = document.getElementById('hero');
    const bg = document.getElementById('parallax-bg');
    const mist = document.getElementById('parallax-mist');
    const fg = document.getElementById('parallax-fg');
    const content = document.querySelector('.hero__content');

    if (!hero || !bg || !fg) return;

    let targetX = 0;
    let targetY = 0;
    let currentX = 0;
    let currentY = 0;
    const ease = 0.08;

    window.addEventListener('mousemove', (e) => {
      const nx = (e.clientX / window.innerWidth) - 0.5;
      const ny = (e.clientY / window.innerHeight) - 0.5;
      targetX = nx * 30;
      targetY = ny * 20;
    }, { passive: true });

    let hasGyro = false;
    window.addEventListener('deviceorientation', (e) => {
      if (e.beta === null || e.gamma === null) return;
      hasGyro = true;
      const tiltX = Math.max(-30, Math.min(30, e.gamma));
      const tiltY = Math.max(-30, Math.min(30, e.beta - 45));
      targetX = (tiltX / 30) * 35;
      targetY = (tiltY / 30) * 25;
    }, { passive: true });

    function updateParallax() {
      currentX += (targetX - currentX) * ease;
      currentY += (targetY - currentY) * ease;

      bg.style.transform = `translate3d(${-currentX * 0.4}px, ${-currentY * 0.4}px, 0)`;
      if (mist) {
        mist.style.transform = `translate3d(${-currentX * 0.15}px, ${-currentY * 0.15}px, 0)`;
      }
      fg.style.transform = `translate3d(${currentX * 0.9}px, ${currentY * 0.9}px, 0)`;
      if (content) {
        content.style.transform = `translate3d(${currentX * 0.25}px, ${currentY * 0.25}px, 0)`;
      }

      animationFrameId = requestAnimationFrame(updateParallax);
    }

    let isVisible = true;
    let animationFrameId = null;

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          isVisible = entry.isIntersecting;
          if (isVisible) {
            if (!animationFrameId) updateParallax();
          } else {
            if (animationFrameId) {
              cancelAnimationFrame(animationFrameId);
              animationFrameId = null;
            }
          }
        });
      }, { threshold: 0.1 });
      observer.observe(hero);
    } else {
      updateParallax();
    }
  }

  initBranchVideoPlay();
  initBackgroundAudio();
  initFireflies();
  init3DParallax();

})();
