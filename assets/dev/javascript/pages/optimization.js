jQuery(document).ready(function () {
    const wpsMaintenanceActions = [
        {
            buttonId: '#populate-submit-button',
            resultId: '#populate-submit-result',
            action: 'wp_statistics_update_country_data',
            confirmMessage: wps_js._('confirm_refresh_country')
        },
        {
            buttonId: '#populate-source-channel-submit',
            resultId: '#populate-source-channel-result',
            action: 'wp_statistics_update_source_channel',
            confirmMessage: wps_js._('confirm_update_channel')
        },
        {
            buttonId: '#hash-ips-submit',
            resultId: '#hash-ips-result',
            action: 'wp_statistics_hash_ips',
            confirmMessage: wps_js._('confirm_hash_ips')
        },
        {
            buttonId: '#repair-schema-submit-button',
            resultId: '#repair-schema-result',
            action: 'wp_statistics_repair_schema',
            confirmMessage: wps_js._('confirm_repair_schema')
        }
    ];

    function wpsHandleMaintenanceAction(config) {
        const {
            buttonId,
            resultId,
            action,
            confirmMessage
        } = config;

        const $button = jQuery(buttonId);
        $button.prop('onclick', null).off('click');

        $button.on('click', function (e) {
            e.preventDefault();

            const $result = jQuery(resultId);

            // Get the modal element
            const modal = document.getElementById('setting-confirmation');
            if (!modal) {
                console.error('Modal with ID "setting-confirmation" not found.');
                return;
            }

            // Set the confirmation message
            const modalDescription = modal.querySelector('.wps-modal__description');
            if (modalDescription) {
                modalDescription.textContent = confirmMessage || 'Are you sure?';
            }

            // Open the modal
            modal.classList.add('wps-modal--open');

            // Remove previous click listeners from the modal OK button
            const okButton = modal.querySelector('button[data-action="resolve"]');
            if (!okButton) return;
            const newOkButton = okButton.cloneNode(true);
            okButton.parentNode.replaceChild(newOkButton, okButton);

            // When OK is clicked, execute the AJAX request
            newOkButton.addEventListener('click', function () {
                $button.addClass('wps-loading-button');
                newOkButton.classList.add('wps-loading-button');

                const data = {
                    action: action,
                    wps_nonce: wps_js.global.rest_api_nonce
                };

                jQuery.ajax({
                    url: wps_js.global.ajax_url,
                    type: 'post',
                    data: data
                })
                    .done(function (response) {
                        let msg = '';

                        try {
                            const parsed = typeof response === 'string' ? JSON.parse(response) : response;

                            if (parsed.success && parsed.data && parsed.data.message) {
                                msg = parsed.data.message;
                            } else if (parsed.data) {
                                msg = JSON.stringify(parsed.data);
                            } else if (parsed.message) {
                                msg = parsed.message;
                            } else {
                                msg = 'Operation completed.';
                            }
                        } catch (e) {
                            msg = response;
                        }

                        $result.html('<div class="wps-alert wps-alert__success"><p>' + msg + '</p></div>');
                    })
                    .fail(function (jqXHR, textStatus, errorThrown) {
                        let msg = '';

                        try {
                            const parsed = JSON.parse(jqXHR.responseText);
                            if (parsed.data && parsed.data.message) {
                                msg = parsed.data.message;
                            } else if (parsed.message) {
                                msg = parsed.message;
                            } else {
                                msg = JSON.stringify(parsed);
                            }
                        } catch (e) {
                            msg = jqXHR.responseText || textStatus + ': ' + errorThrown;
                        }

                        $result.html('<div class="wps-alert wps-alert__danger"><p>' + msg + '</p></div>');
                    })
                    .always(function () {
                        $button.removeClass('wps-loading-button');
                        newOkButton.classList.remove('wps-loading-button');
                        modal.classList.remove('wps-modal--open');
                    });
            });
        });
    }

    // Initialize all maintenance actions
    wpsMaintenanceActions.forEach(cfg => wpsHandleMaintenanceAction(cfg));
});
