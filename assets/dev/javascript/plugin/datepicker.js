jQuery(document).ready(function () {
    const datePickerBtn = jQuery('.js-date-range-picker-btn');
    const datePickerElement = jQuery('.js-date-range-picker-input');
    const datePickerForm = jQuery('.js-date-range-picker-form');

    if (datePickerBtn.length && datePickerElement.length && datePickerForm.length) {
        datePickerBtn.on('click', function () {
            datePickerElement.trigger('click');
        });

        datePickerElement.daterangepicker({
            "autoApply": true,
            "ranges": {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(7, 'days'), moment()],
                'Last 14 Days': [moment().subtract(14, 'days'), moment()],
                'Last 30 Days': [moment().subtract(30, 'days'), moment()],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last 60 Days': [moment().subtract(60, 'days'), moment()],
                'Last 90 Days': [moment().subtract(90, 'days'), moment()],
                'Last 120 Days': [moment().subtract(120, 'days'), moment()],
                'Last 6 Months': [moment().subtract(180, 'days'), moment()],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
            },
        });

        if (wps_js.isset(wps_js.global, 'request_params', 'from') && wps_js.isset(wps_js.global, 'request_params', 'to')) {
            const requestFromDate = wps_js.global.request_params.from;
            const requestToDate = wps_js.global.request_params.to;
            datePickerElement.data('daterangepicker').setStartDate(moment(requestFromDate).format('MM/DD/YYYY'));
            datePickerElement.data('daterangepicker').setEndDate(moment(requestToDate).format('MM/DD/YYYY'));
            datePickerElement.data('daterangepicker').updateCalendars();
            const activeRangeText = datePickerElement.data('daterangepicker').container.find('.ranges li.active').text();
            datePickerBtn.find('span').html(activeRangeText);
        } else {
            let defaultRange = datePickerBtn.find('span').text();
            datePickerElement.data('daterangepicker').container.find('.ranges li.active').removeClass('active');
            datePickerElement.data('daterangepicker').container.find('.ranges li[data-range-key="' + defaultRange + '"]').addClass('active');
            datePickerElement.on('show.daterangepicker', function (ev, picker) {
                datePickerElement.data('daterangepicker').container.find('.ranges li.active').removeClass('active');
                datePickerElement.data('daterangepicker').container.find('.ranges li[data-range-key="' + defaultRange + '"]').addClass('active');
            });
        }

        datePickerElement.on('show.daterangepicker', function (ev, picker) {
            const correspondingPicker = picker.container;
            jQuery(correspondingPicker).addClass(ev.target.className);
        });
        datePickerElement.on('apply.daterangepicker', function (ev, picker) {
            const inputFrom = datePickerForm.find('.js-date-range-picker-input-from').first();
            const inputTo = datePickerForm.find('.js-date-range-picker-input-to').first();
            inputFrom.val(picker.startDate.format('YYYY-MM-DD'));
            inputTo.val(picker.endDate.format('YYYY-MM-DD'));
            datePickerBtn.find('span').html(datePickerElement.data('daterangepicker').chosenLabel);
            datePickerForm.submit();
        });
    }

    // Single Calendar
    const datePickerField = jQuery('.wps-js-calendar-field');
    if (datePickerField.length) {
        datePickerField.daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1998,
            maxYear: parseInt(new Date().getFullYear() + 1),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
        datePickerField.on('show.daterangepicker', function (ev, picker) {
            const correspondingPicker = picker.container;
            jQuery(correspondingPicker).addClass(ev.target.className);
        });
        datePickerField.on('apply.daterangepicker', function(ev, picker) {
             jQuery('.wps-today-datepicker').submit();
        });
    }
});