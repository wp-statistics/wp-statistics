if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "plugins") {
    jQuery(document).ready(function () {
        const action_buttons = document.querySelectorAll('.js-addon-show-more');
        const license_buttons = document.querySelectorAll('.js-wps-addon-license-button');

        action_buttons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.stopPropagation();

                const isActive = this.parentElement.classList.contains('active');

                document.querySelectorAll('.js-addon-show-more').forEach(function (otherButton) {
                    otherButton.parentElement.classList.remove('active');
                });

                if (!isActive) {
                    this.parentElement.classList.add('active');
                }
            });
        });

        document.body.addEventListener('click', function () {
            document.querySelectorAll('.js-addon-show-more').forEach(function (button) {
                button.parentElement.classList.remove('active');
            });
        });

        license_buttons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.stopPropagation();

                const isActive = this.classList.contains('active');

                document.querySelectorAll('.js-wps-addon-license-button').forEach(function (otherButton) {
                    otherButton.classList.remove('active');
                    otherButton.closest('.wps-postbox-addon__item').classList.remove('active');
                });

                if (!isActive) {
                    this.classList.add('active');
                    const closestItem = this.closest('.wps-postbox-addon__item');
                    if (closestItem) {
                        closestItem.classList.add('active');
                    }
                }
            });
        });


    });
}
