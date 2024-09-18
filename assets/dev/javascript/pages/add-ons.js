if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "plugins") {
    jQuery(document).ready(function () {
        const action_buttons = document.querySelectorAll('.js-addon-show-more');
        const license_buttons = document.querySelectorAll('.js-wps-addon-license-button');
        const addon_items = document.querySelectorAll('.js-wps-addon-check-box');
        const select_all = document.querySelector('.js-wps-addon-select-all');

        if (select_all) {
            select_all.addEventListener('click', function(event) {
                event.stopPropagation();
                addon_items.forEach(function(item) {
                    item.checked = true;
                });
            });
        }

        if (action_buttons.length > 0) {
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
        }

        if (license_buttons.length > 0) {
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
        }

        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_check_license'
        };
        params = Object.assign(params, wps_js.global.request_params);

        // Create Ajax
        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'GET',
            dataType: 'json',
            data: params,
            timeout: 30000,
            success: function (data) {
                console.log(data);
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });
}
