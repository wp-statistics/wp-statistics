jQuery(document).ready(function () {
    jQuery('.wp-statistics-travod .notice-dismiss').click(function () {
        jQuery('.wp-statistics-travod').slideUp(100);

        var data = {
            'action': 'wp_statistics_close_notice',
            'notice': 'suggestion',
        };

        jQuery.ajax({
            url: ajaxurl,
            type: 'get',
            data: data,
            datatype: 'json',
        });
    });
});