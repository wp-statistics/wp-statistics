let currentSlide = 0;
const slides = document.querySelectorAll('.wps-slider__slide');
const totalSlides = slides.length;
const dotsContainer = document.querySelector('.wps-slider__dots');
let wpsAutoSlideInterval;

function showSlide(index) {
    slides.forEach((slide, i) => {
        slide.style.display = (i === index) ? 'block' : 'none';
    });
    updateDots(index);
}

function updateDots(index) {
    dotsContainer.innerHTML = '';
    if (totalSlides > 1) {
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('span');
            dot.className = 'wps-slider__dot';
            dot.addEventListener('click', () => {
                showSlide(i);
                resetAutoSlide();
            });
            dot.classList.toggle('active', i === index);
            dotsContainer.appendChild(dot);
        }
        dotsContainer.style.display = 'block';
    } else {
        dotsContainer.style.display = 'none';
    }
}

function startAutoSlideLockPage() {
    if (totalSlides > 1) {
        wpsAutoSlideInterval = setInterval(() => {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
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
