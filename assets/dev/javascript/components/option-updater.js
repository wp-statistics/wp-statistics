jQuery(document).ready(function () {
    jQuery(document).on('click', "a.wps-option__updater", function (e) {
        e.preventDefault();

        let $this = jQuery(this);
        let option = $this.data('option');
        let value = $this.data('value');
        let params = {
            'wps_nonce': wps_js.global.rest_api_nonce,
            'action': 'wp_statistics_option_updater',
            'option': option,
            'value': value
        }

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
                    $this.css('cursor', 'default');
                } else {
                    location.reload();
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
                $this.css('cursor', 'default');
            }
        });
    });
});