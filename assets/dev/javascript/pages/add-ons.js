if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "plugins") {
    jQuery(document).ready(function () {
        const action_buttons = document.querySelectorAll('.js-addon-show-more');
        const license_buttons = document.querySelectorAll('.js-wps-addon-license-button');
        const addon_items = document.querySelectorAll('.js-wps-addon-check-box');
        const select_all = document.querySelector('.js-wps-addon-select-all');
        const active_license_btn = document.querySelector('.js-addon-active-license');
        const addon_download_btn = document.querySelector('.js-addon-download-button');
        const license_input = document.querySelector('.wps-addon__step__active-license input');


        if (addon_items) {
            if (select_all) {
                select_all.addEventListener('click', function (event) {
                    event.stopPropagation();
                    addon_items.forEach(function (item) {
                        item.checked = true;
                    });
                    addon_download_btn.classList.remove('disabled');
                });
            }

            // Function to check the status of addon_items checkboxes
            function updateDownloadButtonState() {
                let anyChecked = false;
                let allUnchecked = true;

                addon_items.forEach(function (item) {
                    if (item.checked) {
                        anyChecked = true;
                        allUnchecked = false;
                    }
                });

                if (anyChecked) {
                    addon_download_btn.classList.remove('disabled');
                } else {
                    addon_download_btn.classList.add('disabled');
                }
            }

            // Handle individual addon items checkboxes
            addon_items.forEach(function (item) {
                item.addEventListener('change', updateDownloadButtonState);
            });
        }


        if (addon_download_btn) {
            addon_download_btn.addEventListener('click', function (event) {
                if (!addon_download_btn.classList.contains('disable')) {
                    event.stopPropagation();
                }

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

        // Define the AJAX request function
        const sendAjaxRequest = (params, button) => {
            button.classList.add('loading');
            jQuery.ajax({
                url: wps_js.global.admin_url + 'admin-ajax.php',
                type: 'GET',
                dataType: 'json',
                data: params,
                timeout: 30000,
                success: function (data) {
                    console.log(data);
                    button.classList.remove('loading');
                    if (data.success) {
                        button.classList.add('disabled');
                        window.location.href = 'admin.php?page=wps_plugins_page&tab=downloads';
                    }else{
                        license_input.classList.add('wps-danger');

                        const alertDiv = document.createElement('div');
                        alertDiv.classList.add('wps-alert', 'wps-alert--danger');
                        alertDiv.innerHTML = `
                        <span class="icon"></span>
                        <div>
                            <p>${data?.data?.message}</p>
                        </div>
                    `;
                        const activeLicenseDiv = document.querySelector('.wps-addon__step__active-license');
                        if (activeLicenseDiv) {
                            activeLicenseDiv.parentNode.insertBefore(alertDiv, activeLicenseDiv.nextSibling);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    button.classList.remove('loading');
                    console.log(error);
                }
            });
        }


        if (license_input && active_license_btn) {
            function toggleButtonState() {
                license_input.classList.remove('wps-danger', 'wps-warning');
                // Check if the alert div already exists and remove it
                const existingAlertDiv = document.querySelector('.wps-alert');
                if (existingAlertDiv) {
                    existingAlertDiv.remove();
                }

                if (license_input.value.trim() === '') {
                    active_license_btn.classList.add('disabled');
                    active_license_btn.disabled = true;
                } else {
                    active_license_btn.classList.remove('disabled');
                    active_license_btn.disabled = false;
                }

            }

            // Initial check when the page loads
            toggleButtonState();

            // Listen for input event to enable button when typing
            license_input.addEventListener('input', function () {
                toggleButtonState();
            });

        }


        if (active_license_btn) {
            active_license_btn.addEventListener('click', function (event) {
                event.stopPropagation();
                 // Get and trim the license key input value
                const license_key = license_input.value.trim();
                if (license_key) {
                    const active_params = {
                        'license_key': license_key,
                        ...params
                    }
                    sendAjaxRequest(active_params, active_license_btn);
                }
            });
        }

        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'GET',
            dataType: 'json',
            data: params,
            timeout: 30000,
            success: function (data) {
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });

    });
}
