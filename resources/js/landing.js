document.addEventListener('DOMContentLoaded', () => {
    initNav();
    initHeroCarousel();
    initReveal();
    initPopups();
    initSmoothScroll();
    initGallerySlideshow();
    initGalleryLightbox();
    initCarousels();
    initNewsModal();
});

function initNav() {
    const nav = document.querySelector('.landing-nav');
    const drawer = document.querySelector('.landing-mobile-drawer');
    const openBtn = document.querySelector('[data-landing-menu-toggle]');
    const closeBtn = document.querySelector('[data-landing-menu-close]');
    const backdrop = document.querySelector('.landing-mobile-backdrop');

    const onScroll = () => {
        if (nav) {
            nav.classList.toggle('is-scrolled', window.scrollY > 24);
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    const closeDrawer = () => drawer?.classList.remove('is-open');

    openBtn?.addEventListener('click', () => drawer?.classList.add('is-open'));
    closeBtn?.addEventListener('click', closeDrawer);
    backdrop?.addEventListener('click', closeDrawer);
    drawer?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeDrawer);
    });
}

function initHeroCarousel() {
    const slides = Array.from(document.querySelectorAll('.landing-hero-slide'));
    if (slides.length <= 1) {
        slides[0]?.classList.add('is-active');
        return;
    }

    let current = 0;
    slides[0]?.classList.add('is-active');

    window.setInterval(() => {
        slides[current]?.classList.remove('is-active');
        current = (current + 1) % slides.length;
        slides[current]?.classList.add('is-active');
    }, 6000);
}

function initReveal() {
    const elements = document.querySelectorAll('.landing-reveal');
    if (!elements.length) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px',
    });

    elements.forEach((element) => observer.observe(element));
}

function initPopups() {
    const popups = Array.from(document.querySelectorAll('.landing-popup'));
    if (!popups.length) {
        return;
    }

    const storageKeyFor = (popup) => {
        const slug = popup.getAttribute('data-popup-key') || 'landing';
        const id = popup.getAttribute('data-popup-id') || '0';
        const version = popup.getAttribute('data-popup-version') || '0';

        return `landing_popup_v2_${slug}_${id}_${version}`;
    };

    const hidePopup = (popup) => {
        popup.classList.remove('is-open');
    };

    const showPopupAt = (index) => {
        popups.forEach(hidePopup);

        const popup = popups[index];
        if (!popup) {
            return;
        }

        const storageKey = storageKeyFor(popup);
        const dismissedInSession = sessionStorage.getItem(storageKey) === 'dismissed';
        const dismissedPermanent = localStorage.getItem(storageKey) === 'dismissed';

        if (dismissedInSession || dismissedPermanent) {
            showPopupAt(index + 1);
            return;
        }

        window.setTimeout(() => popup.classList.add('is-open'), index === 0 ? 500 : 250);
    };

    popups.forEach((popup, index) => {
        popup.querySelector('[data-popup-close]')?.addEventListener('click', () => {
            const storageKey = storageKeyFor(popup);
            hidePopup(popup);
            sessionStorage.setItem(storageKey, 'dismissed');
            showPopupAt(index + 1);
        });
    });

    showPopupAt(0);
}

function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const targetId = link.getAttribute('href')?.slice(1);
            const target = targetId ? document.getElementById(targetId) : null;

            if (target) {
                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

function initGallerySlideshow() {
    document.querySelectorAll('[data-landing-slideshow]').forEach((root) => {
        const slides = Array.from(root.querySelectorAll('.landing-slideshow-slide'));
        const dots = Array.from(root.querySelectorAll('[data-slideshow-dot]'));
        const prev = root.querySelector('[data-slideshow-prev]');
        const next = root.querySelector('[data-slideshow-next]');

        if (slides.length === 0) {
            return;
        }

        let current = slides.findIndex((slide) => slide.classList.contains('is-active'));
        if (current < 0) {
            current = 0;
        }

        slides[current]?.classList.add('is-active');

        if (slides.length <= 1) {
            return;
        }

        const showSlide = (index) => {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide, slideIndex) => {
                slide.classList.toggle('is-active', slideIndex === current);
            });
            dots.forEach((dot, dotIndex) => {
                dot.classList.toggle('is-active', dotIndex === current);
            });
        };

        prev?.addEventListener('click', (event) => {
            event.stopPropagation();
            showSlide(current - 1);
        });
        next?.addEventListener('click', (event) => {
            event.stopPropagation();
            showSlide(current + 1);
        });
        dots.forEach((dot) => {
            dot.addEventListener('click', (event) => {
                event.stopPropagation();
                showSlide(Number(dot.dataset.slideshowDot || 0));
            });
        });
    });
}

function initGalleryLightbox() {
    const modal = document.getElementById('landingGalleryModal');
    const dataElement = document.getElementById('landing-gallery-data');

    if (!modal || !dataElement) {
        return;
    }

    const galleryData = JSON.parse(dataElement.textContent || '[]');
    const image = document.getElementById('landingGalleryModalImage');
    const caption = document.getElementById('landingGalleryModalCaption');
    const title = document.getElementById('landingGalleryModalTitle');
    const counter = document.getElementById('landingGalleryModalCounter');
    const prev = modal.querySelector('[data-gallery-modal-prev]');
    const next = modal.querySelector('[data-gallery-modal-next]');

    if (!image || galleryData.length === 0) {
        return;
    }

    let current = 0;

    const renderSlide = (index) => {
        const slide = galleryData[index];
        if (!slide) {
            return;
        }

        current = index;
        image.src = slide.url || '';
        image.alt = slide.title || 'Foto da galeria';

        const hasTitle = Boolean(slide.title);
        const hasCaption = Boolean(slide.caption);

        title.textContent = slide.title || `Foto ${index + 1}`;

        if (hasCaption) {
            caption.textContent = slide.caption;
            caption.hidden = false;
        } else {
            caption.textContent = '';
            caption.hidden = true;
        }

        if (galleryData.length > 1) {
            counter.textContent = `${index + 1} / ${galleryData.length}`;
        } else {
            counter.textContent = '';
        }

        if (prev) {
            prev.disabled = galleryData.length <= 1;
        }
        if (next) {
            next.disabled = galleryData.length <= 1;
        }
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modal.hidden = true;
        document.body.style.overflow = '';
        image.src = '';
    };

    const openModal = (index) => {
        renderSlide(index);
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        modal.querySelector('.landing-gallery-modal-close')?.focus();
    };

    document.querySelectorAll('.landing-gallery-slide').forEach((slideEl) => {
        const openFromSlide = () => openModal(Number(slideEl.dataset.galleryIndex || 0));

        slideEl.addEventListener('click', openFromSlide);
        slideEl.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openFromSlide();
            }
        });
    });

    prev?.addEventListener('click', () => renderSlide((current - 1 + galleryData.length) % galleryData.length));
    next?.addEventListener('click', () => renderSlide((current + 1) % galleryData.length));

    modal.querySelectorAll('[data-gallery-modal-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (!modal.classList.contains('is-open')) {
            return;
        }

        if (event.key === 'Escape') {
            closeModal();
            return;
        }

        if (event.key === 'ArrowLeft') {
            renderSlide((current - 1 + galleryData.length) % galleryData.length);
        }

        if (event.key === 'ArrowRight') {
            renderSlide((current + 1) % galleryData.length);
        }
    });
}

function initCarousels() {
    document.querySelectorAll('[data-landing-carousel]').forEach((root) => {
        const viewport = root.querySelector('.landing-carousel-viewport');
        const track = root.querySelector('.landing-carousel-track');
        const slides = Array.from(root.querySelectorAll('.landing-carousel-slide'));
        const prev = root.querySelector('[data-carousel-prev]');
        const next = root.querySelector('[data-carousel-next]');

        if (!viewport || !track || slides.length === 0) {
            return;
        }

        let index = 0;

        const getVisibleCount = () => {
            if (window.innerWidth >= 992) {
                return Math.min(3, slides.length);
            }
            if (window.innerWidth >= 768) {
                return Math.min(2, slides.length);
            }
            return 1;
        };

        const maxIndex = () => Math.max(0, slides.length - getVisibleCount());

        const update = () => {
            const visible = getVisibleCount();
            const boundedIndex = Math.min(index, maxIndex());
            index = boundedIndex;

            const slideWidth = viewport.clientWidth / visible;
            slides.forEach((slide) => {
                slide.style.flex = `0 0 ${slideWidth}px`;
                slide.style.maxWidth = `${slideWidth}px`;
            });

            track.style.transform = `translateX(-${boundedIndex * slideWidth}px)`;

            if (prev) {
                prev.disabled = boundedIndex === 0;
            }
            if (next) {
                next.disabled = boundedIndex >= maxIndex();
            }
        };

        prev?.addEventListener('click', () => {
            index = Math.max(0, index - 1);
            update();
        });

        next?.addEventListener('click', () => {
            index = Math.min(maxIndex(), index + 1);
            update();
        });

        window.addEventListener('resize', update);
        update();
    });
}

function initNewsModal() {
    const modal = document.getElementById('landingNewsModal');
    const dataElement = document.getElementById('landing-news-data');

    if (!modal || !dataElement) {
        return;
    }

    const newsData = JSON.parse(dataElement.textContent || '{}');
    const media = document.getElementById('landingNewsModalMedia');
    const tag = document.getElementById('landingNewsModalTag');
    const title = document.getElementById('landingNewsModalTitle');
    const subtitle = document.getElementById('landingNewsModalSubtitle');
    const content = document.getElementById('landingNewsModalContent');
    const link = document.getElementById('landingNewsModalLink');

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modal.hidden = true;
        document.body.style.overflow = '';
    };

    const openModal = (newsId) => {
        const item = newsData[newsId];
        if (!item) {
            return;
        }

        tag.innerHTML = `<i class="bi ${['bi-stars', 'bi-newspaper'].includes(item.tag_icon) ? item.tag_icon : 'bi-newspaper'}"></i> `;
        tag.append(document.createTextNode(item.tag || 'Notícia'));
        title.textContent = item.title || '';

        if (item.subtitle) {
            subtitle.textContent = item.subtitle;
            subtitle.hidden = false;
        } else {
            subtitle.textContent = '';
            subtitle.hidden = true;
        }

        content.textContent = item.content || '';

        if (item.image) {
            media.style.backgroundImage = `url('${item.image}')`;
            media.hidden = false;
        } else {
            media.style.backgroundImage = '';
            media.hidden = true;
        }

        if (item.link_url) {
            link.href = item.link_url;
            link.hidden = false;
        } else {
            link.href = '#';
            link.hidden = true;
        }

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        modal.querySelector('.landing-news-modal-close')?.focus();
    };

    document.querySelectorAll('.landing-news-card').forEach((card) => {
        card.addEventListener('click', () => openModal(card.dataset.newsId));
        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openModal(card.dataset.newsId);
            }
        });
    });

    modal.querySelectorAll('[data-news-modal-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
}
