const skipButtons = document.querySelectorAll('.js-wps-premiumModalClose');
const modal = document.querySelector('.js-wps-premiumModal');
const welcomeContent = document.querySelector('.js-wps-premiumModalWelcomeContent');
const premiumStepsContent = document.querySelector('.js-wps-premiumModalSteps');
const premiumSteps = document.querySelectorAll('.js-wps-premiumModalStep');
const exploreButton = document.querySelector('.js-wps-premiumModalExploreBtn');
const premiumFeatures = document.querySelectorAll('.js-wps-premiumStepFeature');
const premiumBtn = document.querySelectorAll('.js-wps-openPremiumModal');
const upgradeButton = document.querySelector('.js-wps-premiumModalUpgradeBtn');
const firstStepHeader = document.querySelector('.js-wps-premium-first-step__head');
const stepsHeader = document.querySelector('.js-wps-premium-steps__head');

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
          const targetStep = premiumSteps[currentStepIndex + 1];
          if (targetStep) {
              targetStep.setAttribute('data-href', href);
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
    if (index < 0 || index >= premiumSteps.length) return;


    premiumSteps.forEach(step => step.classList.remove('wps-modal__premium-step--active'));
    premiumFeatures.forEach(feature => feature.classList.remove('active'));

    premiumSteps[index].classList.add('wps-modal__premium-step--active');
    if (index > 0) {
        firstStepHeader.style.display = 'none';
        stepsHeader.style.display = 'block';
        premiumFeatures[index - 1].classList.add('active'); // Sync the sidebar with the step
    }
    else{
        firstStepHeader.style.display = 'block';
        stepsHeader.style.display = 'none';
    }
    if (upgradeButton) {
        const activeStep = document.querySelector('.wps-modal__premium-step--active');
        if (activeStep) {
            const href = activeStep.getAttribute('data-href');
            if (href) {
                upgradeButton.setAttribute('href', href);
            }
        }
    }
}

// Function to start the auto-slide process
const startAutoSlide = () => {
        autoSlideInterval = setInterval(() => {
        currentStepIndex = (currentStepIndex + 1) % premiumSteps.length; // Loop through steps
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
