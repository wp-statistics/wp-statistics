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

        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_dismissNotification',
            'notification_id': notificationId
        }

        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'GET',
            dataType: 'json',
            data: params,
            timeout: 30000,
            success: function ({data, success}) {

                if (success) {
                    if (notificationId === 'all') {
                        jQuery('.wps-notification-sidebar__card').fadeOut(300, function () {
                            jQuery(this).remove();
                        });
                    } else {
                        $this.closest('.wps-notification-sidebar__card').fadeOut(300, function () {
                            jQuery(this).remove();
                        });
                    }
                } else {
                    console.log(data);
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });
});