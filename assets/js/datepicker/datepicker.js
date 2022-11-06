jQuery(document).on('click', function (event) {
    if (!jQuery(event.target).closest('.c-footer__filter').length) {
        jQuery('.js-widget-filters').removeClass('is-active');
    }
});

jQuery( document ).ready(function() {
    const datePickerElement = jQuery('.js-datepicker-input');
    datePickerElement.daterangepicker({"autoApply": true});
    datePickerElement.on('apply.daterangepicker', function (ev, picker) {
        console.log(picker.startDate.format('YYYY-MM-DD'))
        console.log(picker.endDate.format('YYYY-MM-DD'))
    });
});