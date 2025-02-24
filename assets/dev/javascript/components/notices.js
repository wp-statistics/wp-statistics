jQuery(document).ready(function () {
    jQuery(document).on('click', "a.wps-notice-action__ajax-handler", function (e) {
        e.preventDefault();

        let $this = jQuery(this);
        let $notice = $this.closest('.notice');
        let noticeDataElement = $this.closest('.notice').find('.js-wps-notice-data');
        let option = noticeDataElement.data('option');
        let value = noticeDataElement.data('value');
        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_notice_ajax_handler',
            'option': option,
            'value': value
        }

        $notice.css('cursor', 'progress');
        $this.css('cursor', 'progress');

        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'GET',
            dataType: 'json',
            data: params,
            timeout: 30000,
            success: function ({data, success}) {
                if (!success) {
                    console.log(data);
                    $notice.css('cursor', 'default');
                    $this.css('cursor', 'default');
                } else {
                    location.reload();
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
                $notice.css('cursor', 'default');
                $this.css('cursor', 'default');
            }
        });
    });
});