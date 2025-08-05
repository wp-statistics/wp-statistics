jQuery(document).ready(function () {
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


