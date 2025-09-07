if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "optimization") {
    const wpStatisticsOptimization = {
        actions: [
            {
                button: '#populate-submit-button',
                result: '#populate-submit-result',
                action: 'wp_statistics_update_country_data',
                messageKey: 'confirm_refresh_country'
            },
            {
                button: '#populate-source-channel-submit',
                result: '#populate-source-channel-result',
                action: 'wp_statistics_update_source_channel',
                messageKey: 'confirm_update_channel'
            },
            {
                button: '#hash-ips-submit',
                result: '#hash-ips-result',
                action: 'wp_statistics_hash_ips',
                messageKey: 'confirm_hash_ips'
            },
            {
                button: '#repair-schema-submit-button',
                result: '#repair-schema-result',
                action: 'wp_statistics_repair_schema',
                messageKey: 'confirm_repair_schema'
            },
            {
                button: '#re-check-schema-submit-button',
                result: '#re-check-schema-result',
                action: 'wp_statistics_re_check_schema'
            }
        ],

        showModal: function (message = wps_js._('are_you_sure'), onConfirm) {
            const $modal = $('#setting-confirmation');
            if (!$modal.length) return console.error('Modal not found.');

            $modal.find('.wps-modal__description').text(message);

            const $ok = $modal.find('button[data-action="resolve"]');
            $ok.off('click').on('click', () => {
                $ok.addClass('wps-loading-button');
                onConfirm();
            });

            $modal.addClass('wps-modal--open');
        },

        ajaxAction: function (action, $result, $button) {
            const $modal = $('#setting-confirmation');
            const $ok = $modal.find('button[data-action="resolve"]');

            const parseMessage = (response, defaultMsg = wps_js._('operation_completed')) => {
                try {
                    const parsed = typeof response === 'string' ? JSON.parse(response) : response;

                    if (parsed?.data?.message) {
                        return parsed.data.message;
                    }

                    if (parsed?.message) {
                        return parsed.message;
                    }

                    if (parsed?.data) {
                        return JSON.stringify(parsed.data);
                    }

                    return defaultMsg;
                } catch {
                    return typeof response === 'string' ? response : JSON.stringify(response);
                }
            };

            const parseErrorMessage = (jqXHR, textStatus, errorThrown) => {
                try {
                    const parsed = JSON.parse(jqXHR.responseText);
                    return parsed.data?.message
                        ? parsed.data.message
                        : parsed.message || JSON.stringify(parsed);
                } catch {
                    return jqXHR.responseText || `${textStatus}: ${errorThrown}`;
                }
            };

            $button.addClass('wps-loading-button');

            jQuery.ajax({
                url: wps_js.global.ajax_url,
                type: 'post',
                data: {
                    action: action,
                    wps_nonce: wps_js.global.rest_api_nonce
                }
            })
                .done(function (response) {
                    const msg = parseMessage(response);
                    if (response.success) {
                        $result.html(`<div class="wps-alert wps-alert__success"><p>${msg}</p></div>`);
                    } else {
                        $result.html(`<div class="wps-alert wps-alert__danger"><p>${msg}</p></div>`);
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    const msg = parseErrorMessage(jqXHR, textStatus, errorThrown);
                    $result.html(`<div class="wps-alert wps-alert__danger"><p>${msg}</p></div>`);
                })
                .always(function () {
                    $button.removeClass('wps-loading-button');
                    $ok.removeClass('wps-loading-button');
                    $modal.removeClass('wps-modal--open');
                });
        },

        initMaintenanceAction: function ({button, result, action, messageKey}) {
            const $btn = $(button);
            const $res = $(result);

            $btn.off('click').on('click', e => {
                e.preventDefault();
                if (!messageKey) {
                    this.ajaxAction(action, $res, $btn);
                } else {
                    const msg = wps_js._(messageKey);
                    this.showModal(msg, () => this.ajaxAction(action, $res, $btn));
                }
            });
        },

        init: function () {
            this.actions.forEach(a => this.initMaintenanceAction(a));
        }
    };

    jQuery(document).ready(() => {
        wpStatisticsOptimization.init();
    });
}