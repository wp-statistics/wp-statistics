jQuery(document).ready(function () {
    const datePickerBtn = jQuery('.js-date-range-picker-btn');
    const datePickerElement = jQuery('.js-date-range-picker-input');
    const datePickerForm = jQuery('.js-date-range-picker-form');
    const datePickerField = jQuery('.wps-js-calendar-field');
    const wpTimezone = wps_js.isset(wps_js.global, 'options', 'wp_timezone') ? wps_js.global['options']['wp_timezone'] : null;

    let validTimezone = wpTimezone;
    if (wpTimezone && (wpTimezone.startsWith('+') || wpTimezone.startsWith('-'))) {
        validTimezone = `UTC${wpTimezone}`; // Convert "-10:30" to "UTC-10:30"
    }
    function getLocalTime() {
        if (validTimezone) {
            if (validTimezone.startsWith('UTC') || validTimezone.startsWith('+') || validTimezone.startsWith('-')) {
                const offset = validTimezone.replace('UTC', ''); // Remove "UTC" prefix if present
                const [hours, minutes] = offset.split(':').map(Number);

                // Handle negative offsets correctly
                const totalOffsetMinutes = (hours * 60) + (hours < 0 ? -Math.abs(minutes) : minutes);
                return moment().utcOffset(totalOffsetMinutes);
            } else {
                // Handle named timezones (e.g., "Pacific/Honolulu")
                if (moment.tz.zone(validTimezone)) {
                    return moment().tz(validTimezone);
                } else {
                    // Fallback to UTC if the named timezone is invalid
                    return moment().utc();
                }
            }
        } else {
            // Fallback to UTC if no timezone is set
            return moment().utc();
        }
    }

    // Update the week start day based on WordPress setting
    if (datePickerBtn.length) {
        moment.updateLocale('en', {
            week: {
                dow: parseInt(wps_js._('start_of_week'))
            }
        });
    }

    const localTime = getLocalTime();

    function phpToMomentFormat(phpFormat) {
        const formatMap = {
            'd': 'DD',
            'j': 'D',
            'S': 'Do',
            'n': 'M',
            'm': 'MM',
            'F': 'MMMM',
            'M': 'MMM',
            'y': 'YY',
            'Y': 'YYYY'
        };

        return phpFormat.replace(/([a-zA-Z])/g, (match) => formatMap[match] || match);
    }

    if (datePickerBtn.length && datePickerElement.length && datePickerForm.length) {
         datePickerBtn.on('click', function () {
            datePickerElement.trigger('click');
        });

        let ranges = {
            'Today': [localTime.clone().startOf('day'), localTime.clone().startOf('day')],
            'Yesterday': [localTime.clone().subtract(1, 'days').startOf('day'), localTime.clone().subtract(1, 'days').startOf('day')],
            'This Week': [localTime.clone().startOf('week'), localTime.clone().endOf('week')],
            'Last Week': [localTime.clone().subtract(1, 'week').startOf('week'), localTime.clone().subtract(1, 'week').endOf('week')],
            'This Month': [localTime.clone().startOf('month'), localTime.clone().endOf('month')],
            'Last Month': [localTime.clone().subtract(1, 'month').startOf('month'), localTime.clone().subtract(1, 'month').endOf('month')],
            'Last 7 Days': [localTime.clone().subtract(6, 'days'), localTime.clone()],
            'Last 30 Days': [localTime.clone().subtract(29, 'days'), localTime.clone()],
            'Last 90 Days': [localTime.clone().subtract(89, 'days'), localTime.clone()],
            'Last 6 Months': [localTime.clone().subtract(6, 'months'), localTime.clone()],
            'This Year': [localTime.clone().startOf('year'), localTime.clone().endOf('year')]
        };

        function hasTypeParameter() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('post_id');
        }

        if (datePickerBtn.hasClass('js-date-range-picker-all-time')) {
            let post_date = moment(0) ;
            if (hasTypeParameter()) {
                post_date=wps_js.global.post_creation_date ? moment(wps_js.global.post_creation_date) : moment(0);
            }else{
                post_date=wps_js.global.initial_post_date ? moment(wps_js.global.initial_post_date) : moment(0);
            }
            ranges['All Time'] = [post_date, moment()];
        }

        const phpDateFormat = datePickerBtn.attr('data-date-format') ? datePickerBtn.attr('data-date-format') : 'MM/DD/YYYY';
        let momentDateFormat = phpToMomentFormat(phpDateFormat);
        // Default dates for the date picker
        let defaultStartDate = moment(wps_js.global.user_date_range.from).format('YYYY-MM-DD');
        let defaultEndDate = moment(wps_js.global.user_date_range.to).format('YYYY-MM-DD');
        datePickerElement.daterangepicker({
            "autoApply": true,
            "ranges": ranges,
            startDate: defaultStartDate,
            endDate: defaultEndDate
        });

        if (wps_js.isset(wps_js.global, 'request_params', 'from') && wps_js.isset(wps_js.global, 'request_params', 'to')) {
            let requestFromDate = wps_js.global.request_params.from;
            if (hasTypeParameter() && wps_js.global.post_creation_date ) {
                requestFromDate =  wps_js.global.post_creation_date;
            }
            const requestToDate = wps_js.global.request_params.to;
            datePickerElement.data('daterangepicker').setStartDate(moment(requestFromDate).format('MM/DD/YYYY'));
            datePickerElement.data('daterangepicker').setEndDate(moment(requestToDate).format('MM/DD/YYYY'));
            datePickerElement.data('daterangepicker').updateCalendars();
            const activeText = datePickerElement.data('daterangepicker').chosenLabel;

             const startMoment = moment(requestFromDate);
            const endMoment = moment(requestToDate);
            let activeRangeText;
            if (startMoment.year() === endMoment.year()) {
                const startDateFormat = momentDateFormat.replace(/,?\s?(YYYY|YY)[-/\s]?,?|[-/\s]?(YYYY|YY)[-/\s]?,?/g, "");
                activeRangeText = `${startMoment.format(startDateFormat)} - ${endMoment.format(momentDateFormat)}`;
            } else {
                activeRangeText = `${startMoment.format(momentDateFormat)} - ${endMoment.format(momentDateFormat)}`;
            }
            if (activeText !== 'Custom Range') {
                if (activeText !== 'All Time') {
                    activeRangeText = `<span class="wps-date-range">${activeText}</span>${activeRangeText}`;
                    document.querySelector('.js-date-range-picker-btn').classList.add('custom-range')
                } else {
                    activeRangeText = activeText
                }
            }
            datePickerBtn.find('span').html(activeRangeText);
        } else {
            const defaultStartMoment = moment(defaultStartDate);
            const defaultEndMoment = moment(defaultEndDate);
            datePickerElement.data('daterangepicker').setStartDate(moment(defaultStartDate).format('MM/DD/YYYY'));
            datePickerElement.data('daterangepicker').setEndDate(moment(defaultEndDate).format('MM/DD/YYYY'));
            datePickerElement.data('daterangepicker').updateCalendars();

            let defaultActiveRangeText;
            if (defaultStartMoment.year() === defaultEndMoment.year()) {
                const startDateFormat = momentDateFormat.replace(/,?\s?(YYYY|YY)[-/\s]?,?|[-/\s]?(YYYY|YY)[-/\s]?,?/g, "");
                defaultActiveRangeText = `${defaultStartMoment.format(startDateFormat)} - ${defaultEndMoment.format(momentDateFormat)}`;
            } else {
                defaultActiveRangeText = `${defaultStartMoment.format(momentDateFormat)} - ${defaultEndMoment.format(momentDateFormat)}`;
            }
            const defaultRange = datePickerElement.data('daterangepicker').container.find('.ranges li.active').text();
            datePickerElement.data('daterangepicker').container.find('.ranges li.active').removeClass('active');
            datePickerElement.data('daterangepicker').container.find('.ranges li[data-range-key="' + defaultRange + '"]').addClass('active');
            if (defaultRange !== 'Custom Range') {
                if (defaultRange !== 'All Time') {
                    defaultActiveRangeText = `<span class="wps-date-range">${defaultRange}</span>${defaultActiveRangeText}`;
                    document.querySelector('.js-date-range-picker-btn').classList.add('custom-range')
                } else {
                    defaultActiveRangeText = defaultActiveRangeText
                }
            }
            datePickerBtn.find('span').html(defaultActiveRangeText);
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
            const startDate = picker.startDate.utcOffset(validTimezone).format('YYYY-MM-DD');
            const endDate = picker.endDate.utcOffset(validTimezone).format('YYYY-MM-DD');

            inputFrom.val(startDate);
            inputTo.val(endDate);
            const selectedRange = datePickerElement.data('daterangepicker').chosenLabel;
            datePickerBtn.find('span').html(selectedRange);
            if( selectedRange  !== 'All Time') {
                jQuery.ajax({
                    url: wps_js.global.ajax_url,
                    method: 'POST',
                    data: {
                        wps_nonce: wps_js.global.rest_api_nonce,
                        action: 'wp_statistics_store_date_range',
                        date: {
                            from: inputFrom.val(),
                            to: inputTo.val()
                        }
                    },
                    beforeSend: function () {
                        datePickerBtn.addClass('wps-disabled');
                    },
                    complete: function (data) {
                        datePickerForm.submit();
                    }
                });
            }else{
                datePickerForm.submit();
            }

        });
    }

    // Single Calendar
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
        datePickerField.on('apply.daterangepicker', function (ev, picker) {
            jQuery('.wps-today-datepicker').submit();
        });
    }
});