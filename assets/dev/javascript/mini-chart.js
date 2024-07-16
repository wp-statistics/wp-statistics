document.addEventListener('DOMContentLoaded', function () {
    var globalDataItems = document.querySelectorAll('.wp-statistics-global-data:not(.disabled), .wp-statistics-current-page-data:not(.disabled)');

    globalDataItems.forEach(function(item) {
        item.addEventListener('click', function (e) {
            if (item.classList.contains('disabled')) {
                return;
            }
            if (e.target.tagName.toLowerCase() != 'a' && e.target.tagName.toLowerCase() != 'button' && !e.target.classList.contains('wps-admin-bar__chart__unlock-button')) {
                // Disable all click events unless it's an actual link/button or the "Explore Details" link in footer
                e.preventDefault();
            }

            // Hide all ab-sub-wrapper elements
            document.querySelectorAll('.wp-statistics-global-data .ab-sub-wrapper, .wp-statistics-current-page-data .ab-sub-wrapper').forEach(function(wrapper) {
                wrapper.style.display = 'none';
            });

            // Show ab-sub-wrapper of the clicked element
            item.querySelector('.ab-sub-wrapper').style.display = 'block';

            // Remove active class from all elements
            document.querySelectorAll('.wp-statistics-global-data, .wp-statistics-current-page-data').forEach(function(el) {
                el.classList.remove('active');
            });

            // Toggle active class on the clicked element
            item.classList.toggle('active');
        });
    });

    // Trigger click on the first global data item
    var globalData = document.querySelector('.wp-statistics-global-data');
    if (globalData != null) {
        globalData.click();
    }
});