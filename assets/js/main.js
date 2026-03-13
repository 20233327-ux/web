// PhimWeb - Main JavaScript

document.addEventListener('DOMContentLoaded', function () {

    // ── Navbar scroll effect ──────────────────────────────────
    const nav = document.getElementById('mainNav');
    if (nav) {
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 50);
        }, { passive: true });
    }

    // ── Auto-dismiss alerts ───────────────────────────────────
    setTimeout(() => {
        document.querySelectorAll('.alert:not(.alert-danger)').forEach(el => {
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 4000);

    // ── Lazy loading images ───────────────────────────────────
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    io.unobserve(img);
                }
            });
        });
        lazyImages.forEach(img => io.observe(img));
    }

    // ── Image error fallback ──────────────────────────────────
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function () {
            this.src = (window.BASE_URL || '') + '/assets/images/no-thumb.svg';
            this.onerror = null;
        });
    });

    // ── Init Plyr if not already done on watch.php ────────────
    const playerEl = document.getElementById('player');
    if (playerEl && typeof Plyr !== 'undefined' && !playerEl._plyr) {
        playerEl._plyr = new Plyr('#player', {
            controls: ['play-large','play','progress','current-time','duration','mute','volume','settings','pip','airplay','fullscreen'],
            settings: ['quality','speed','loop'],
            speed:    { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] },
            i18n: {
                play: 'Phát', pause: 'Dừng', mute: 'Tắt tiếng',
                unmute: 'Bật tiếng', speed: 'Tốc độ', normal: 'Bình thường',
                fullscreen: 'Toàn màn hình', exitFullscreen: 'Thoát toàn màn hình',
            }
        });
    }

    // ── Search input animation ────────────────────────────────
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('focus', function () {
            this.closest('.input-group')?.classList.add('focused');
        });
        searchInput.addEventListener('blur', function () {
            this.closest('.input-group')?.classList.remove('focused');
        });
    }

    // ── Genre dropdown active state ───────────────────────────
    const currentPath = window.location.pathname;
    document.querySelectorAll('.dropdown-item').forEach(item => {
        if (item.getAttribute('href') && currentPath.includes(item.getAttribute('href'))) {
            item.classList.add('active');
        }
    });

    // ── Confirm delete buttons ────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Bạn có chắc chắn muốn thực hiện thao tác này?')) {
                e.preventDefault();
            }
        });
    });

    // ── Card hover ripple effect ──────────────────────────────
    document.querySelectorAll('.movie-card').forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.zIndex = '10';
        });
        card.addEventListener('mouseleave', function () {
            this.style.zIndex = '';
        });
    });

    // ── Smooth scroll ─────────────────────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

});
