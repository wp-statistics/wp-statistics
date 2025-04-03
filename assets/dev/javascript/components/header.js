/**
 * FeedbackBird position
 * */
function moveFeedbackBird() {
    let windowWidth = window.outerWidth || document.documentElement.clientWidth;
    const feedbackBird = document.getElementById('feedback-bird-app');
    const feedbackBirdTitle = document.querySelector('.c-fbb-widget__header__title');
    const license = document.querySelector('.wps-mobileMenuContent>div:last-child');
    const notification = document.querySelector('.wps-adminHeader__side .wps-notifications');
    const support = document.querySelector('.wps-adminHeader__side');
    if (feedbackBird && (document.body.classList.contains('wps_page'))) {
        if (windowWidth <= 1030) {
            const cutDiv = feedbackBird.parentNode.removeChild(feedbackBird);
            license.parentNode.insertBefore(cutDiv, license);
        } else {
            const cutDiv = feedbackBird.parentNode.removeChild(feedbackBird);
            if (notification) {
                notification.parentNode.insertBefore(cutDiv, notification);
            } else {
                support.appendChild(cutDiv);
            }
        }
        feedbackBird.style.display = 'block';
        feedbackBird.setAttribute('title', feedbackBirdTitle.innerHTML);
    }
}

window.addEventListener('resize', moveFeedbackBird);


jQuery(document).ready(function () {
    moveFeedbackBird();
    const dropdownToggles = document.querySelectorAll('.wps-admin-header__link--has-dropdown');
    dropdownToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu) {
                dropdownMenu.classList.toggle('is-open');
                toggle.classList.toggle('is-open');
            }
        });
    });

    document.addEventListener('click', function (e) {
        dropdownToggles.forEach(function (toggle) {
            const dropdownMenu = toggle.nextElementSibling;
            if (dropdownMenu && !toggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('is-open');
            }
        });
    });

    const targetElement = document.querySelector('.wp-header-end');
    const noticeElement = document.querySelector('.notice.notice-warning.update-nag');
    // Check if both targetElement and noticeElement exist
    if (targetElement && noticeElement) {
        // Move the notice element after the target element
        targetElement.parentNode.insertBefore(noticeElement, targetElement.nextSibling);
    }
});


