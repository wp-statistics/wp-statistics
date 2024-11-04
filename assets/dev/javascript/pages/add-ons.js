if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "plugins") {
    jQuery(document).ready(function () {
        const action_buttons = document.querySelectorAll('.js-addon-show-more');
        const license_buttons = document.querySelectorAll('.js-wps-addon-license-button');
        const addon_items = document.querySelectorAll('.js-wps-addon-check-box');
        const retry_addon_install_btn = document.querySelectorAll('.js-addon-retry-btn');
        const select_all = document.querySelector('.js-wps-addon-select-all');
        const active_license_btn = document.querySelector('.js-addon-active-license');
        const addon_download_btn = document.querySelector('.js-addon-download-button');
        const license_input = document.querySelector('.wps-addon__step__active-license input');
        const active_all = document.querySelector('.js-addon_active-all');
        let active_addon_plugin_btn = document.querySelectorAll('.js-addon-active-plugin-btn');
        let selectedSlugs = [];
        let activeSlugs = [];
        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_check_license'
        };
        params = Object.assign(params, wps_js.global.request_params);

        if (params.page === 'plugins' && params?.tab === 'downloads' && addon_download_btn) {
            if (addon_items.length === 0) {
                addon_download_btn.textContent = wps_js._('continue_to_next_step');
                addon_download_btn.classList.remove('disabled');
                addon_download_btn.href = `admin.php?page=wps_plugins_page&tab=get-started&license_key=${params.license_key}`;
            }
        }


        const toggleActiveAll = () => {
            if (active_addon_plugin_btn && active_addon_plugin_btn.length > 0) {
                if (active_all) {
                    active_all.classList.remove('wps-hide')
                }
            }
        }
        toggleActiveAll();

        // Check if the alert div already exists and remove it
        const toggleAlertBox = (btn) => {
            const existingAlertDiv = btn.parentElement.parentElement.querySelector('.wps-alert');
            if (existingAlertDiv) {
                existingAlertDiv.remove();
            }
        }

        const updateSelectedSlugs = () => {
            selectedSlugs = [];
            addon_items.forEach(function (item) {
                if (item.checked) {
                    selectedSlugs.push(item.getAttribute('data-slug'));
                }
            });
        }

        // Generic function to process slugs sequentially
        const processSlugs = (slugs, action, index, onSuccess, button) => {
            if (index < slugs.length) {
                const slug = slugs[index];
                let action_params = {
                    ...params,
                    'action': action,
                    'plugin_slug': slug
                };

                if (button) {
                    button.classList.add('loading');
                    button.classList.add('disabled');
                }

                sendAjaxRequest(action_params, button, () => processSlugs(slugs, action, index + 1, onSuccess, button));
            } else {
                onSuccess();
            }
        };

        // Function to handle step 2: downloading slugs
        const processSlugsSequentially = (index) => {
            select_all.classList.add('wps-hide');

            addon_items.forEach(function (item, i) {
                if (item.checked && i > index) {
                    const containStatus = item.parentElement.parentElement.querySelector('.wps-postbox-addon__status');
                    if (containStatus) {
                        containStatus.remove();
                    }
                    const statusSpan = document.createElement('span');
                    statusSpan.classList.add('wps-postbox-addon__status', 'wps-postbox-addon__status--waiting');
                    statusSpan.textContent = wps_js._('waiting');
                    item.parentElement.parentElement.insertBefore(statusSpan, item.parentElement.parentElement.firstChild);
                    item.parentElement.classList.add('wps-hide');
                }
            });


            processSlugs(selectedSlugs, 'wp_statistics_download_plugin', index, () => {
                addon_download_btn.classList.add('redirecting');
                addon_download_btn.textContent = wps_js._('redirecting');
                const new_params = new URLSearchParams();
                selectedSlugs.forEach(slug => new_params.append('addons[]', slug));
                window.location.href = `admin.php?page=wps_plugins_page&tab=get-started&license_key=${params.license_key}&` + new_params.toString();
            }, addon_download_btn);
        };

        // Function to handle step 3: activating slugs
        const processActiveSlugsSequentially = (index) => {

            active_addon_plugin_btn.forEach(function (item, i) {
                if (i > index) {
                    const containStatus = item.parentElement.parentElement.querySelector('.wps-postbox-addon__status');
                    if (containStatus) {
                        containStatus.remove();
                    }
                    const statusSpan = document.createElement('span');
                    statusSpan.classList.add('wps-postbox-addon__status', 'wps-postbox-addon__status--waiting');
                    statusSpan.textContent = wps_js._('waiting');
                    item.parentElement.parentElement.insertBefore(statusSpan, item.parentElement.parentElement.firstChild);
                    item.parentElement.classList.add('wps-hide')
                }
            });

            processSlugs(activeSlugs, 'wp_statistics_activate_plugin', index, () => {
                active_addon_plugin_btn.forEach(function (item, i) {
                    item.parentElement.classList.remove('wps-hide')
                });
                active_addon_plugin_btn = document.querySelectorAll('.js-addon-active-plugin-btn');
                toggleActiveAll();
            });
        };


        if (addon_items.length > 0) {
            if (select_all) {
                select_all.addEventListener('click', function (event) {
                    event.stopPropagation();
                    addon_items.forEach(function (item) {
                        item.checked = true;
                        item.parentElement.parentElement.parentElement.classList.add('wps-has-checkbox')
                    });
                    addon_download_btn.classList.remove('disabled');
                    updateSelectedSlugs();
                });
                select_all.click();
            }

            // Function to check the status of addon_items checkboxes
            const updateDownloadButtonState = () => {
                let anyChecked = false;
                addon_items.forEach(function (item) {
                    if (item.checked) {
                        anyChecked = true;
                    }
                });

                if (anyChecked) {
                    addon_download_btn.classList.remove('disabled');
                } else {
                    addon_download_btn.classList.add('disabled');
                }
                updateSelectedSlugs();
            }

            // Handle individual addon items checkboxes
            addon_items.forEach(function (item) {
                item.addEventListener('change', updateDownloadButtonState);
            });
        } else {
            if (select_all) select_all.classList.add('wps-hide');
        }

        // Updated handleAddonAction to use event delegation
        const handleAddonAction = (buttons, action, paramsKey) => {
            if (buttons.length > 0) {
                buttons.forEach(function (button) {
                    button.addEventListener('click', function (event) {
                        event.stopPropagation();
                        const slug = button.getAttribute('data-slug');
                        let action_params = {
                            ...params,
                            'action': action,
                            [paramsKey]: slug
                        };
                        sendAjaxRequest(action_params, button);
                    });
                });
            }
        };

        handleAddonAction(retry_addon_install_btn, 'wp_statistics_download_plugin', 'plugin_slug');
        handleAddonAction(active_addon_plugin_btn, 'wp_statistics_activate_plugin', 'plugin_slug');

        document.addEventListener('click', function (event) {
            if (event.target.classList.contains('js-addon-retry-btn')) {
                event.stopPropagation();
                const slug = event.target.getAttribute('data-slug');
                let action_params = {
                    ...params,
                    'action': 'wp_statistics_download_plugin',
                    'plugin_slug': slug
                };
                sendAjaxRequest(action_params, event.target);
            } else if (event.target.classList.contains('js-addon-active-plugin-btn')) {
                event.stopPropagation();
                const slug = event.target.getAttribute('data-slug');
                let action_params = {
                    ...params,
                    'action': 'wp_statistics_activate_plugin',
                    'plugin_slug': slug
                };
                sendAjaxRequest(action_params, event.target);
            } else if (event.target.classList.contains('js-addon_active-all')) {
                event.stopPropagation();
                active_all.classList.add('wps-hide');

                active_addon_plugin_btn = document.querySelectorAll('.js-addon-active-plugin-btn')
                activeSlugs = [];
                active_addon_plugin_btn.forEach(function (item) {
                    activeSlugs.push(item.getAttribute('data-slug'));
                });
                if (active_addon_plugin_btn && active_addon_plugin_btn.length > 0) {
                    processActiveSlugsSequentially(0);
                }
            }
        });

        if (addon_download_btn) {
            addon_download_btn.addEventListener('click', function (event) {
                if (!addon_download_btn.classList.contains('disable')) {
                    event.stopPropagation();

                    addon_items.forEach(function (item) {
                        if (!item.checked) {
                            item.disabled = true;
                        }
                    });
                    processSlugsSequentially(0);
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
                            let active_input = closestItem.querySelector('.wps-addon__item__update_license input');
                            let active_button = closestItem.querySelector('.wps-addon__item__update_license button');
                            if (active_input && active_button) {
                                function toggleButtonState() {
                                    active_input.classList.remove('wps-danger', 'wps-warning');
                                    toggleAlertBox(active_button);
                                    if (active_input.value.trim() === '') {
                                        active_button.classList.add('disabled');
                                        active_button.disabled = true;
                                    } else {
                                        active_button.classList.remove('disabled');
                                        active_button.disabled = false;
                                    }
                                }

                                // Initial check when the page loads
                                toggleButtonState();

                                // Listen for input event to enable button when typing
                                active_input.addEventListener('input', function () {
                                    toggleButtonState();
                                });

                            }
                            if (active_button) {
                                active_button.addEventListener('click', function (event) {
                                    event.stopPropagation();
                                    // Get and trim the license key input value
                                    const license_key = active_input.value.trim();
                                    const addon_slug = active_input.dataset.addonSlug;

                                    if (license_key && addon_slug) {
                                        const active_params = {
                                            'license_key': license_key,
                                            'addon_slug': addon_slug,
                                            ...params
                                        }
                                        sendAjaxRequest(active_params, active_button);
                                    }
                                });
                            }


                        }
                    }
                });
            });
        }

        const errorHandel = (params, button, data) => {
            if (params.action === "wp_statistics_check_license") {
                toggleAlertBox(button);
                const alertDiv = document.createElement('div');
                if (data?.data?.message?.toLowerCase().includes('domain')) {
                    button.parentElement.querySelector('input').classList.add('wps-warning');
                    alertDiv.classList.add('wps-alert', 'wps-alert--warning');
                } else {
                    button.parentElement.querySelector('input').classList.add('wps-danger');
                    alertDiv.classList.add('wps-alert', 'wps-alert--danger');
                }
                alertDiv.innerHTML = `
                                <span class="icon"></span>
                                <div>
                                    <p>${data?.data?.message}</p>
                                </div>
                                `;
                let activeLicenseDiv;
                if (params.tab) {
                    activeLicenseDiv = document.querySelector('.wps-addon__step__active-license');
                } else {
                    activeLicenseDiv = button.parentElement;
                }

                if (activeLicenseDiv) {
                    activeLicenseDiv.parentNode.insertBefore(alertDiv, activeLicenseDiv.nextSibling);
                }
            }
            if (params.action === "wp_statistics_download_plugin") {
                const current_plugin_checkbox = document.querySelector(`[data-slug="${params.plugin_slug}"]`);
                if (current_plugin_checkbox) {
                    const downloadingStatus = current_plugin_checkbox.parentElement.parentElement.querySelector('.wps-postbox-addon__status');
                    if (downloadingStatus) {
                        downloadingStatus.remove();
                    }
                    const statusSpan = document.createElement('span');
                    statusSpan.classList.add('wps-postbox-addon__status', 'wps-postbox-addon__status--danger');
                    statusSpan.textContent = wps_js._('failed');
                    current_plugin_checkbox.parentElement.parentElement.insertBefore(statusSpan, current_plugin_checkbox.parentElement.parentElement.firstChild);

                    if (params.tab === 'get-started') {
                        const retryBtn = document.createElement('a');
                        retryBtn.classList.add('wps-postbox-addon__button', 'button-retry-addon-download', 'js-addon-retry-btn');
                        retryBtn.textContent = wps_js._('retry');
                        retryBtn.setAttribute('data-slug', params.plugin_slug);
                        current_plugin_checkbox.parentElement.parentElement.insertBefore(retryBtn, statusSpan.nextSibling);
                    }
                }
            }
            if (params.action === "wp_statistics_activate_plugin") {
                const current_plugin = document.querySelector(`[data-slug="${params.plugin_slug}"]`);
                if (current_plugin) {
                    const loadingStatus = current_plugin.parentElement.parentElement.querySelector('.wps-postbox-addon__status');
                    if (loadingStatus) {
                        loadingStatus.remove();
                    }
                    const statusSpan = document.createElement('span');
                    statusSpan.classList.add('wps-postbox-addon__status', 'wps-postbox-addon__status--danger');
                    statusSpan.textContent = wps_js._('failed');
                    current_plugin.parentElement.parentElement.insertBefore(statusSpan, current_plugin.parentElement.parentElement.firstChild);
                    current_plugin.style.display = 'flex';
                }
            }
        }
        // Define the AJAX request function
        const sendAjaxRequest = (params, button, callback) => {
            if (button) button.classList.add('loading');
            if (params.action === "wp_statistics_download_plugin") {

                const current_plugin = document.querySelector(`[data-slug="${params.plugin_slug}"]`);
                if (current_plugin) {
                    const statusLable = current_plugin.parentElement.parentElement.querySelector('.wps-postbox-addon__status');
                    if (statusLable) {
                        statusLable.remove();
                    }
                    const statusSpan = document.createElement('span');
                    statusSpan.classList.add('wps-postbox-addon__status', 'wps-postbox-addon__status--purple');
                    statusSpan.textContent = wps_js._('downloading');
                    if (current_plugin && current_plugin.parentElement.parentElement) {
                        current_plugin.parentElement.parentElement.insertBefore(statusSpan, current_plugin.parentElement.parentElement.firstChild);
                        current_plugin.style.display = 'none';
                    }

                }
            }

            if (params.action === "wp_statistics_activate_plugin") {

                const current_plugin = document.querySelector(`[data-slug="${params.plugin_slug}"]`);
                if (current_plugin) {
                    const statusLable = current_plugin.parentElement.parentElement.querySelector('.wps-postbox-addon__status');
                    if (statusLable) {
                        statusLable.remove();
                    }
                    const statusSpan = document.createElement('span');
                    statusSpan.classList.add('wps-postbox-addon__status', 'wps-postbox-addon__status--purple');
                    statusSpan.textContent = wps_js._('activating');
                    if (current_plugin && current_plugin.parentElement.parentElement) {
                        current_plugin.parentElement.parentElement.insertBefore(statusSpan, current_plugin.parentElement.parentElement.firstChild);
                        current_plugin.style.display = 'none';
                    }
                }
            }
            jQuery.ajax({
                url: wps_js.global.admin_url + 'admin-ajax.php',
                type: 'GET',
                dataType: 'json',
                data: params,
                timeout: 30000,
                success: function (data) {
                    if (button) button.classList.remove('loading');
                    if (data.success) {
                        if (button) button.classList.add('disabled');
                        if (params.action === "wp_statistics_check_license") {
                            if (wps_js.global.request_params?.tab) {
                                button.classList.add('redirecting');
                                button.textContent = wps_js._('redirecting');
                                window.location.href = `admin.php?page=wps_plugins_page&tab=downloads&license_key=${params.license_key}`;
                            } else {
                                toggleAlertBox(button);
                                button.parentElement.querySelector('input').classList.remove('wps-danger');
                                const statusDanger = button.parentElement.parentElement.parentElement.querySelector('.wps-postbox-addon__status');
                                if (statusDanger.classList.contains('wps-postbox-addon__status--danger')) {
                                    statusDanger.classList.add('wps-postbox-addon__status--success');
                                    statusDanger.classList.remove('wps-postbox-addon__status--danger');
                                    statusDanger.textContent = wps_js._('activated');
                                }
                                const alertDiv = document.createElement('div');
                                alertDiv.classList.add('wps-alert', 'wps-alert--success');
                                alertDiv.innerHTML = `
                                <span class="icon"></span>
                                <div>
                                    <p>${data?.data?.message}</p>
                                </div>
                                `;
                                let activeLicenseDiv = button.parentElement;
                                if (activeLicenseDiv) {
                                    activeLicenseDiv.parentNode.insertBefore(alertDiv, activeLicenseDiv.nextSibling);
                                }
                            }

                        }
                        if (params.action === "wp_statistics_download_plugin") {
                            const current_plugin_checkbox = document.querySelector(`[data-slug="${params.plugin_slug}"]`);
                            if (current_plugin_checkbox) {
                                const loadingStatus = current_plugin_checkbox.parentElement.parentElement.querySelector('.wps-postbox-addon__status--purple');
                                const updatedLable = current_plugin_checkbox.parentElement.parentElement.querySelector('.wps-postbox-addon__label--updated');
                                if (loadingStatus) {
                                    loadingStatus.remove();
                                }
                                if (updatedLable) {
                                    updatedLable.remove();
                                }
                                const statusSpan = document.createElement('span');
                                statusSpan.classList.add('wps-postbox-addon__status', 'wps-postbox-addon__status--installed');
                                statusSpan.textContent = wps_js._('already_installed');
                                if (current_plugin_checkbox && current_plugin_checkbox.parentElement.parentElement) {
                                    current_plugin_checkbox.parentElement.parentElement.insertBefore(statusSpan, current_plugin_checkbox.parentElement.parentElement.firstChild);
                                }

                                if (params.tab === 'downloads') {
                                    statusSpan.parentElement.parentElement.classList.add('wps-addon__download__item--disabled');
                                }
                                if (params.tab === 'get-started') {
                                    const activeBtn = document.createElement('a');
                                    activeBtn.classList.add('wps-postbox-addon__button', 'button-activate-addon', 'js-addon-active-plugin-btn');
                                    activeBtn.textContent = wps_js._('active');
                                    activeBtn.setAttribute('data-slug', params.plugin_slug);
                                    current_plugin_checkbox.parentElement.insertBefore(activeBtn, statusSpan.nextSibling);
                                    const showMoreBtn = document.querySelector('.js-addon-show-more');
                                    active_addon_plugin_btn = document.querySelectorAll('.js-addon-active-plugin-btn');
                                    toggleActiveAll();
                                    if (showMoreBtn) {
                                        showMoreBtn.classList.remove('wps-hide');
                                    }
                                }
                                current_plugin_checkbox.remove();
                            }
                        }
                        if (params.action === "wp_statistics_activate_plugin") {
                            const current_plugin_checkbox = document.querySelector(`[data-slug="${params.plugin_slug}"]`);
                            if (current_plugin_checkbox) {
                                const loadingStatus = current_plugin_checkbox.parentElement.parentElement.querySelector('.wps-postbox-addon__status--purple');
                                if (loadingStatus) {
                                    loadingStatus.remove();
                                }
                                const statusSpan = document.createElement('span');
                                statusSpan.classList.add('wps-postbox-addon__status', 'wps-postbox-addon__status--success');
                                statusSpan.textContent = wps_js._('activated');
                                current_plugin_checkbox.parentElement.parentElement.insertBefore(statusSpan, current_plugin_checkbox.parentElement.parentElement.firstChild);
                                current_plugin_checkbox.remove();
                            }
                        }

                    } else {
                        errorHandel(params, button, data);
                    }
                    if (callback) callback();
                },
                error: function (xhr, status, error) {
                    if (button) button.classList.remove('loading');
                    if (callback) callback();
                    errorHandel(params, button, error);
                }
            });
        }
        if (license_input && active_license_btn) {
            function toggleButtonState() {
                license_input.classList.remove('wps-danger', 'wps-warning');
                toggleAlertBox(active_license_btn);

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

    });
}
