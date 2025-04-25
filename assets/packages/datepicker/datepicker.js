jQuery(document).on('click', function (event) {
    if (!jQuery(event.target).closest('.c-footer__filter').length) {
        jQuery('.js-widget-filters').removeClass('is-active');
    }
});