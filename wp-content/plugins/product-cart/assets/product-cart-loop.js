
(function () {
    'use strict';

    let activeSlides = [];
    let activeIndex = 0;
    let previousFocus = null;
    let previousBodyOverflow = '';

    function getSlides(gallery) {
        return Array.from(
            gallery.querySelectorAll('.pc-gallery-slide')
        );
    }

    function updateGallery(gallery, requestedIndex) {
        const slides = getSlides(gallery);

        if (!slides.length) return;

        const index =
            ((requestedIndex % slides.length) + slides.length) %
            slides.length;

        gallery.dataset.index = String(index);

        const track = gallery.querySelector('.pc-gallery-track');

        if (track) {
            track.style.transform =
                'translateX(-' + index * 100 + '%)';
        }

        const current = gallery.querySelector('.pc-gallery-current');

        if (current) {
            current.textContent = String(index + 1);
        }

        slides.forEach(function (slide, i) {
            slide.setAttribute(
                'aria-current',
                i === index ? 'true' : 'false'
            );
        });
    }

    function createLightbox() {
        let lightbox = document.querySelector('.pc-lightbox');

        if (lightbox) return lightbox;

        lightbox = document.createElement('div');
        lightbox.className = 'pc-lightbox';
        lightbox.hidden = true;
        lightbox.setAttribute('role', 'dialog');
        lightbox.setAttribute('aria-modal', 'true');
        lightbox.setAttribute('aria-label', 'Product image viewer');

        lightbox.innerHTML = `
            <button type="button" class="pc-lightbox-close"
                aria-label="Close image viewer">&times;</button>
            <button type="button" class="pc-lightbox-arrow pc-lightbox-prev"
                aria-label="Previous image">&#10094;</button>
            <div class="pc-lightbox-content">
                <img class="pc-lightbox-image" src="" alt="">
            </div>
            <button type="button" class="pc-lightbox-arrow pc-lightbox-next"
                aria-label="Next image">&#10095;</button>
            <div class="pc-lightbox-counter" aria-live="polite"></div>
        `;

        document.body.appendChild(lightbox);

        lightbox.querySelector('.pc-lightbox-close')
            .addEventListener('click', closeLightbox);

        lightbox.querySelector('.pc-lightbox-prev')
            .addEventListener('click', function () {
                showLightboxImage(activeIndex - 1);
            });

        lightbox.querySelector('.pc-lightbox-next')
            .addEventListener('click', function () {
                showLightboxImage(activeIndex + 1);
            });

        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        return lightbox;
    }

    function showLightboxImage(requestedIndex) {
        if (!activeSlides.length) return;

        activeIndex =
            ((requestedIndex % activeSlides.length) +
                activeSlides.length) % activeSlides.length;

        const slide = activeSlides[activeIndex];
        const thumbnail = slide.querySelector('img');
        const image = document.querySelector(
            '.pc-lightbox .pc-lightbox-image'
        );

        image.src = slide.dataset.fullSrc;
        image.alt = thumbnail ? thumbnail.alt : 'Product image';

        const counter = document.querySelector(
            '.pc-lightbox .pc-lightbox-counter'
        );

        counter.textContent =
            (activeIndex + 1) + ' / ' + activeSlides.length;

        const lightbox = image.closest('.pc-lightbox');

        lightbox.querySelector('.pc-lightbox-prev').hidden =
            activeSlides.length < 2;

        lightbox.querySelector('.pc-lightbox-next').hidden =
            activeSlides.length < 2;
    }

    function openLightbox(gallery, slide) {
        activeSlides = getSlides(gallery);
        activeIndex = activeSlides.indexOf(slide);

        if (activeIndex < 0) return;

        previousFocus = document.activeElement;

        const lightbox = createLightbox();

        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        lightbox.hidden = false;
        showLightboxImage(activeIndex);

        lightbox.querySelector('.pc-lightbox-close').focus();
    }

    function closeLightbox() {
        const lightbox = document.querySelector('.pc-lightbox');

        if (!lightbox || lightbox.hidden) return;

        lightbox.hidden = true;
        document.body.style.overflow = previousBodyOverflow;

        const image = lightbox.querySelector('.pc-lightbox-image');
        image.removeAttribute('src');
        image.alt = '';

        if (previousFocus && previousFocus.isConnected) {
            previousFocus.focus();
        }

        activeSlides = [];
        activeIndex = 0;
        previousFocus = null;
    }

    function initializeGallery(gallery) {
        if (gallery.dataset.pcInitialized === 'true') return;

        gallery.dataset.pcInitialized = 'true';

        const slides = getSlides(gallery);

        if (!slides.length) return;

        updateGallery(gallery, 0);

        const previous = gallery.querySelector('.pc-gallery-prev');
        const next = gallery.querySelector('.pc-gallery-next');

        if (previous) {
            previous.addEventListener('click', function () {
                updateGallery(
                    gallery,
                    Number(gallery.dataset.index || 0) - 1
                );
            });
        }

        if (next) {
            next.addEventListener('click', function () {
                updateGallery(
                    gallery,
                    Number(gallery.dataset.index || 0) + 1
                );
            });
        }

        slides.forEach(function (slide) {
            slide.addEventListener('click', function () {
                openLightbox(gallery, slide);
            });
        });

        const viewport = gallery.querySelector('.pc-gallery-viewport');

        if (viewport) {
            let startX = 0;
            let startY = 0;

            viewport.addEventListener('touchstart', function (event) {
                if (!event.touches.length) return;

                startX = event.touches[0].clientX;
                startY = event.touches[0].clientY;
            }, { passive: true });

            viewport.addEventListener('touchend', function (event) {
                if (!event.changedTouches.length) return;

                const deltaX = event.changedTouches[0].clientX - startX;
                const deltaY = event.changedTouches[0].clientY - startY;

                if (
                    Math.abs(deltaX) < 45 ||
                    Math.abs(deltaX) < Math.abs(deltaY)
                ) {
                    return;
                }

                updateGallery(
                    gallery,
                    Number(gallery.dataset.index || 0) +
                        (deltaX < 0 ? 1 : -1)
                );
            }, { passive: true });
        }
    }

    function initializeAllGalleries() {
        document.querySelectorAll('.pc-product-gallery')
            .forEach(initializeGallery);
    }

    document.addEventListener('keydown', function (event) {
        const lightbox = document.querySelector('.pc-lightbox');

        if (!lightbox || lightbox.hidden) return;

        if (event.key === 'Escape') {
            closeLightbox();
        } else if (event.key === 'ArrowRight') {
            showLightboxImage(activeIndex + 1);
        } else if (event.key === 'ArrowLeft') {
            showLightboxImage(activeIndex - 1);
        } else if (event.key === 'Tab') {
            const controls = Array.from(
                lightbox.querySelectorAll(
                    'button:not([hidden]):not(:disabled)'
                )
            );

            if (!controls.length) return;

            const first = controls[0];
            const last = controls[controls.length - 1];

            if (
                event.shiftKey &&
                document.activeElement === first
            ) {
                event.preventDefault();
                last.focus();
            } else if (
                !event.shiftKey &&
                document.activeElement === last
            ) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initializeAllGalleries
        );
    } else {
        initializeAllGalleries();
    }

    // Support widgets rendered dynamically by Elementor.
    if (window.jQuery) {
        window.jQuery(window).on(
            'elementor/frontend/init',
            function () {
                if (
                    window.elementorFrontend &&
                    window.elementorFrontend.hooks
                ) {
                    window.elementorFrontend.hooks.addAction(
                        'frontend/element_ready/product-cart-loop.default',
                        function ($scope) {
                            $scope[0]
                                .querySelectorAll('.pc-product-gallery')
                                .forEach(initializeGallery);
                        }
                    );
                }
            }
        );
    }
})();


// zoom


/* Lightbox Zoom: click, double-click, wheel and drag */
let pcZoomLevel = 1;
let pcZoomX = 0;
let pcZoomY = 0;
let pcIsDragging = false;
let pcDragStartX = 0;
let pcDragStartY = 0;
let pcDragOriginX = 0;
let pcDragOriginY = 0;

function pcApplyZoom(image) {
    image.style.transform =
        `translate(${pcZoomX}px, ${pcZoomY}px) scale(${pcZoomLevel})`;

    image.classList.toggle('pc-zoomed', pcZoomLevel > 1);
}

function pcResetZoom(image) {
    pcZoomLevel = 1;
    pcZoomX = 0;
    pcZoomY = 0;
    pcIsDragging = false;

    image.classList.remove('pc-dragging');
    pcApplyZoom(image);
}

function pcInitLightboxZoom() {
    const lightbox = document.querySelector('.pc-lightbox');

    if (!lightbox || lightbox.dataset.zoomInitialized === 'true') {
        return;
    }

    lightbox.dataset.zoomInitialized = 'true';

    const image = lightbox.querySelector('.pc-lightbox-image');

    // Click to toggle zoom.
    image.addEventListener('click', function (event) {
        event.stopPropagation();

        if (pcZoomLevel > 1) {
            pcResetZoom(image);
        } else {
            pcZoomLevel = 2;
            pcApplyZoom(image);
        }
    });

    // Double-click to zoom in or reset.
    image.addEventListener('dblclick', function (event) {
        event.preventDefault();

        if (pcZoomLevel > 1) {
            pcResetZoom(image);
        } else {
            pcZoomLevel = 2.5;
            pcApplyZoom(image);
        }
    });

    // Mouse wheel to zoom.
    image.addEventListener('wheel', function (event) {
        event.preventDefault();

        pcZoomLevel += event.deltaY < 0 ? 0.25 : -0.25;
        pcZoomLevel = Math.max(1, Math.min(4, pcZoomLevel));

        if (pcZoomLevel === 1) {
            pcZoomX = 0;
            pcZoomY = 0;
        }

        pcApplyZoom(image);
    }, { passive: false });

    // Drag the zoomed image.
    image.addEventListener('pointerdown', function (event) {
        if (pcZoomLevel <= 1) return;

        pcIsDragging = true;
        pcDragStartX = event.clientX;
        pcDragStartY = event.clientY;
        pcDragOriginX = pcZoomX;
        pcDragOriginY = pcZoomY;

        image.classList.add('pc-dragging');
        image.setPointerCapture(event.pointerId);
        event.preventDefault();
    });

    image.addEventListener('pointermove', function (event) {
        if (!pcIsDragging) return;

        pcZoomX = pcDragOriginX + event.clientX - pcDragStartX;
        pcZoomY = pcDragOriginY + event.clientY - pcDragStartY;

        pcApplyZoom(image);
    });

    function stopDragging() {
        pcIsDragging = false;
        image.classList.remove('pc-dragging');
    }

    image.addEventListener('pointerup', stopDragging);
    image.addEventListener('pointercancel', stopDragging);

    // Reset zoom whenever a different gallery image is displayed.
    const observer = new MutationObserver(function () {
        pcResetZoom(image);
    });

    observer.observe(image, {
        attributes: true,
        attributeFilter: ['src']
    });
}

// Initialize after the existing lightbox code creates its element.
document.addEventListener('click', function (event) {
    if (event.target.closest('.pc-gallery-slide')) {
        setTimeout(pcInitLightboxZoom, 0);
    }
});

pcInitLightboxZoom();

