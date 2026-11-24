// Lock page preview slider (views/pages/lock-page.php): one picture at a time, dots are buttons,
// auto-advance every 5 s unless the visitor hovers or focuses the slider or prefers reduced motion.
let currentSlide = 0;
const lockSlider = document.querySelector('.wps-lock-page__slider');
const slides = document.querySelectorAll('.wps-slider__slide');
const totalSlides = slides.length;
const dotsContainer = document.querySelector('.wps-slider__dots');
let wpsAutoSlideInterval;

function showSlide(index) {
    currentSlide = index;
    slides.forEach((slide, i) => {
        // The class shows the slide (CSS); split lock pages keep every slide in one grid cell so the
        // box stays as tall as the tallest picture and nothing jumps when pictures change.
        slide.classList.toggle('is-active', i === index);
        slide.setAttribute('aria-hidden', i === index ? 'false' : 'true');
    });
    updateDots(index);
}

function updateDots(index) {
    if (!dotsContainer) return;
    if (totalSlides < 2) {
        dotsContainer.style.display = 'none';
        return;
    }
    if (!dotsContainer.children.length) {
        const label = dotsContainer.getAttribute('data-dot-label') || '%d';
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'wps-slider__dot';
            dot.setAttribute('aria-label', label.replace('%d', String(i + 1)));
            dot.addEventListener('click', () => {
                showSlide(i);
                resetAutoSlide();
            });
            dotsContainer.appendChild(dot);
        }
    }
    Array.prototype.forEach.call(dotsContainer.children, (dot, i) => {
        dot.classList.toggle('active', i === index);
        dot.setAttribute('aria-current', i === index ? 'true' : 'false');
    });
    dotsContainer.style.display = 'flex';
}

function prefersReducedMotion() {
    try {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch (e) {
        return false;
    }
}

function startAutoSlideLockPage() {
    if (totalSlides > 1 && !prefersReducedMotion()) {
        wpsAutoSlideInterval = setInterval(() => {
            if (lockSlider && (lockSlider.matches(':hover') || lockSlider.contains(document.activeElement))) return;
            showSlide((currentSlide + 1) % totalSlides);
        }, 5000);
    }
}

function resetAutoSlide() {
    clearInterval(wpsAutoSlideInterval);
    startAutoSlideLockPage();
}

if (totalSlides > 0) {
    showSlide(currentSlide);
    startAutoSlideLockPage();
}
