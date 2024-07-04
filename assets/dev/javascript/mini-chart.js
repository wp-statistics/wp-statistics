
jQuery(document).ready(function () {
    jQuery('.wp-statistics-global-data:not(.disabled), .wp-statistics-current-page-data:not(.disabled)').on('click', function (e) {
        if (jQuery(this).hasClass('disabled')) {
            return;
        }
        e.preventDefault();
        jQuery('.wp-statistics-global-data, .wp-statistics-current-page-data').find('.ab-sub-wrapper').hide();
        jQuery(this).find('.ab-sub-wrapper').show();
        jQuery('.wp-statistics-global-data, .wp-statistics-current-page-data').removeClass('active');
        jQuery(this).toggleClass('active');
    });
    jQuery('.wp-statistics-global-data').trigger('click');

});