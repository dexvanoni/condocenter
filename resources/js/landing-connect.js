document.addEventListener('DOMContentLoaded', () => {
    initConnectNav();
    initPopups();
    initNewsModal();
    initGalleryLightbox();
});

function initConnectNav() {
    const header = document.querySelector('.cch-header');
    const drawer = document.querySelector('.cch-mobile-nav');
    const toggle = document.querySelector('[data-cch-menu-toggle]');

    const onScroll = () => {
        header?.classList.toggle('is-scrolled', window.scrollY > 12);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    const closeDrawer = () => {
        drawer?.classList.remove('is-open');
        toggle?.setAttribute('aria-expanded', 'false');
    };

    toggle?.addEventListener('click', () => {
        const open = drawer?.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    drawer?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeDrawer);
    });
}

function initPopups() {
    document.querySelectorAll('.landing-popup').forEach((popup) => {
        const key = popup.dataset.popupKey || 'landing';
        const id = popup.dataset.popupId || '0';
        const version = popup.dataset.popupVersion || '1';
        const storageKey = `landing-popup:${key}:${id}:${version}`;

        if (localStorage.getItem(storageKey)) {
            return;
        }

        window.setTimeout(() => {
            popup.classList.add('is-open');
            popup.setAttribute('aria-hidden', 'false');
        }, 600);

        popup.querySelectorAll('[data-popup-close]').forEach((button) => {
            button.addEventListener('click', () => {
                popup.classList.remove('is-open');
                popup.setAttribute('aria-hidden', 'true');
                localStorage.setItem(storageKey, '1');
            });
        });
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
        title.textContent = slide.title || `Foto ${index + 1}`;

        if (slide.caption) {
            caption.textContent = slide.caption;
            caption.hidden = false;
        } else {
            caption.textContent = '';
            caption.hidden = true;
        }

        counter.textContent = galleryData.length > 1 ? `${index + 1} / ${galleryData.length}` : '';
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
