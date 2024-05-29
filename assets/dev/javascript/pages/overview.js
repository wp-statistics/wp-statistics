if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "overview") {

    // Add Click Close Donate Notice
    jQuery('#wps-donate-notice').on('click', '.notice-dismiss', function () {
        jQuery.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            type: 'get',
            data: {
                'action': 'wp_statistics_close_notice',
                'notice': 'donate',
                'wps_nonce': '' + wps_js.global.rest_api_nonce + ''
            },
            datatype: 'json',
        });
    });
}