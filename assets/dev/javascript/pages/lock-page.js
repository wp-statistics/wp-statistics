let currentSlide = 0;
const slides = document.querySelectorAll('.wps-slider__slide');
const totalSlides = slides.length;
const dotsContainer = document.querySelector('.wps-slider__dots');

function showSlide(index) {
    slides.forEach((slide, i) => {
        slide.style.display = (i === index) ? 'block' : 'none';
    });
    updateDots(index);
}

function updateDots(index) {
    // Clear existing dots
    dotsContainer.innerHTML = '';

    // Only create dots if there are multiple slides
    if (totalSlides > 1) {
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('span');
            dot.className = 'wps-slider__dot';
            dot.addEventListener('click', () => showSlide(i)); // Show the slide corresponding to the dot
            dot.classList.toggle('active', i === index); // Highlight the active dot
            dotsContainer.appendChild(dot);
        }
        dotsContainer.style.display = 'block'; // Show dots container
    } else {
        dotsContainer.style.display = 'none'; // Hide dots container
    }
}

// Initialize the slider
if (totalSlides > 0) {
    showSlide(currentSlide);
}