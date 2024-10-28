const skipButtons = document.querySelectorAll('.js-wps-premiumModalClose');
const modal = document.querySelector('.js-wps-premiumModal');
const welcomeContent = document.querySelector('.js-wps-premiumModalWelcomeContent');
const premiumStepsContent = document.querySelector('.js-wps-premiumModalSteps');
const premiumSteps = document.querySelectorAll('.js-wps-premiumModalStep');
const premiumWelcomeSteps = document.querySelectorAll('.js-wps-premiumModal-welcome .js-wps-premiumModalStep');
const exploreButton = document.querySelector('.js-wps-premiumModalExploreBtn');
const premiumFeatures = document.querySelectorAll('.js-wps-premiumStepFeature');
const premiumBtn = document.querySelectorAll('.js-wps-openPremiumModal');
const upgradeButton = document.querySelectorAll('.js-wps-premiumModalUpgradeBtn');
const firstStepHeader = document.querySelectorAll('.js-wps-premium-first-step__head');
const stepsHeader = document.querySelectorAll('.js-wps-premium-steps__head');

let autoSlideInterval;
let currentStepIndex = 1;

const closeModal=()=> {
     if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
     }
}

const openModal = (target, href) => {
     if (modal){
         modal.style.display = 'block';
         document.body.style.overflow = 'hidden';
     }
     const targetIndex = Array.from(premiumFeatures).findIndex(step => step.getAttribute('data-modal') === target);
      if (targetIndex !== -1) {
        currentStepIndex = targetIndex;
         if(welcomeContent){
             welcomeContent.style.display = 'none';
          }
          showStep(currentStepIndex+1);
          premiumStepsContent.style.display = 'block';
          stopAutoSlide();
    }
  }



if(premiumBtn.length>0){
    premiumBtn.forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const href = button.getAttribute('href');
            const target = button.getAttribute('data-target');
            openModal(target, href);
        });
    });
}

if (skipButtons.length>0) {
    skipButtons.forEach(button => {
        button.addEventListener('click', closeModal);
    });
}

// Hide the premium steps initially
premiumSteps.forEach(step => {
    step.classList.remove('wps-modal__premium-step--active');
});

if (exploreButton) {
    exploreButton.addEventListener('click', function () {
        currentStepIndex = 0;
        welcomeContent.style.display = 'none';
        premiumStepsContent.style.display = 'block';
         // Start auto-slide through steps
        showStep(currentStepIndex);
        startAutoSlide();
    });
}

// Function to show a specific step and sync the sidebar
const showStep = (index) => {

    document.querySelectorAll('.wps-premium-step__image').forEach((img) => {
        img.src = img.dataset.src;
    });

    if (index < 0 || index >= premiumSteps.length) return;
    premiumSteps.forEach(step => step.classList.remove('wps-modal__premium-step--active'));
    upgradeButton.forEach(btn => btn.classList.remove('wps-premium-step__action-btn--upgrade--active'));
    premiumFeatures.forEach(feature => feature.classList.remove('active'));

    premiumSteps[index].classList.add('wps-modal__premium-step--active');
    upgradeButton[index].classList.add('wps-premium-step__action-btn--upgrade--active');

    const toggleDisplay = (elements, displayStyle) => {
        elements.forEach(element => {
            element.style.display = displayStyle;
        });
    };

    if (index > 0) {
        toggleDisplay(firstStepHeader, 'none');
        toggleDisplay(stepsHeader, 'block');
        premiumFeatures[index - 1].classList.add('active');
    } else {
        toggleDisplay(firstStepHeader, 'block');
        toggleDisplay(stepsHeader, 'none');
    }

}

// Function to start the auto-slide process
const startAutoSlide = () => {
        autoSlideInterval = setInterval(() => {
        currentStepIndex = (currentStepIndex + 1) % premiumWelcomeSteps.length; // Loop through steps
        showStep(currentStepIndex); // Show the current step and sync sidebar
    }, 5000); // Adjust time interval to 5 seconds
}

const stopAutoSlide = () => {
    clearInterval(autoSlideInterval);
};

// Event listeners for each premium step feature
if (premiumFeatures.length>0) {
    premiumFeatures.forEach((feature, index) => {
        feature.addEventListener('click', function () {
            stopAutoSlide(); // Stop auto-slide when user interacts
            currentStepIndex = index + 1
            showStep(currentStepIndex);
         });
    });
}