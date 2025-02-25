jQuery(document).ready(function () {
    jQuery(document).on('click', "a.wps-notification-sidebar__dismiss, a.wps-notification-sidebar__dismiss-all", function (e) {
        e.preventDefault();

        let $this = jQuery(this);
        let notificationId = '';

        if ($this.hasClass('wps-notification-sidebar__dismiss')) {
            notificationId = $this.data('notification-id');
        }

        if ($this.hasClass('wps-notification-sidebar__dismiss-all')) {
            notificationId = 'all';
        }

        if (notificationId === 'all') {
            jQuery('.wps-notification-sidebar__cards--active .wps-notification-sidebar__card').each(function () {
                let $card = jQuery(this);

                jQuery('.wps-notification-sidebar__cards--dismissed').prepend($card.clone().hide().fadeIn(300));

                $card.fadeOut(300, function () {
                    jQuery(this).remove();
                });
            });
        } else {
            let $card = $this.closest('.wps-notification-sidebar__card');

            jQuery('.wps-notification-sidebar__cards--dismissed').prepend($card.clone().hide().fadeIn(300));

            $card.fadeOut(300, function () {
                jQuery(this).remove();
            });
        }

        jQuery('.wps-notification-sidebar__cards--dismissed .wps-notification-sidebar__no-card').remove();

        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_dismiss_notification',
            'notification_id': notificationId
        }

        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'GET',
            dataType: 'json',
            data: params,
            timeout: 30000,
            success: function ({data, success}) {
                if (!success) {
                    console.log(data);
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });

    jQuery(document).on('click', "a.wps-notifications--has-items", function (e) {
        e.preventDefault();

        let $this = jQuery(this);

        $this.removeClass('wps-notifications--has-items');

        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_update_notifications_status',
        }

        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'GET',
            dataType: 'json',
            data: params,
            timeout: 30000,
            success: function ({data, success}) {
                if (!success) {
                    console.log(data);
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });
});