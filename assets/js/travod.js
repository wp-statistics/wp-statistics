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

    jQuery('.travod-quote-form .button').click(function () {
        /*var name = jQuery('#name').val();
        var email = jQuery('#email').val();

        if (!name || !email) {
            alert('Please enter name or email.');
            return false;
        }

        alert(ajaxurl);

        return false;*/
    });
});