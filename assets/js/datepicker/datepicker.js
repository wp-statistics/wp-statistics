function triggerCustomDateEventListener() {
    jQuery('.js-datepicker-input').unbind('change');
    jQuery('.js-datepicker-input').on('change', function () {
        console.log(jQuery(this).val())
    })
}

jQuery( document ).ready(function() {
    triggerCustomDateEventListener()
});