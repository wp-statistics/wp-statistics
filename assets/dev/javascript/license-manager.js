jQuery(document).ready(function ($) {
    if (typeof WP_Statistics_License_Manager_Object === 'undefined' || !WP_Statistics_License_Manager_Object.ajaxUrl) {
        console.error('WP_Statistics_License_Manager_Object is not available or missing ajaxUrl.');
        return;
    }
    let ajaxUrl = WP_Statistics_License_Manager_Object.ajaxUrl;

    let params = {
        'action': 'wp_statistics_check_license'
    };

    $.ajax({
        url: ajaxUrl,
        type: 'GET',
        dataType: 'json',
        data: params,
        timeout: 30000,
        success: function ({ data, success }) {

            // If request is not successful, return early
            if (success == false) return console.log(data);

            console.log(data);
        },
        error: function (xhr, status, error) {
            console.log(error);
        }
    });
});
