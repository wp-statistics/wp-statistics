jQuery(document).ready(function () {
    const datePickerBtn = jQuery('.js-date-range-picker-btn');
    const datePickerElement = jQuery('.js-date-range-picker-input');
    const datePickerForm = jQuery('.js-date-range-picker-form');
    const datePickerField = jQuery('.wps-js-calendar-field');
    const wpTimezone = wps_js.isset(wps_js.global, 'options', 'wp_timezone') ? wps_js.global['options']['wp_timezone'] : null;
    let validTimezone = wpTimezone;

    // Update the week start day based on WordPress setting
    if (datePickerBtn.length) {
        moment.updateLocale('en', {
            week: {
                dow: parseInt(wps_js._('start_of_week'))
            }
        });
    }


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
    function normalizeDate(date, timezone) {
         if (timezone && (timezone.startsWith('UTC') || timezone.startsWith('+') || timezone.startsWith('-'))) {
            const offset = timezone.startsWith('UTC') ? timezone.replace('UTC', '') : timezone;
            return moment(date).utcOffset(offset).startOf('day');
        } else if (moment.tz.zone(timezone)) {
             return moment(date).tz(timezone).startOf('day');
        } else {
             return moment(date).utc().startOf('day');
        }
    }

    if (datePickerBtn.length && datePickerElement.length && datePickerForm.length) {
        datePickerBtn.on('click', function () {
            datePickerElement.trigger('click');
        });
        if (wpTimezone && (wpTimezone.startsWith('+') || wpTimezone.startsWith('-'))) {
            validTimezone = `UTC${wpTimezone}`;
        } else if (!moment.tz.zone(validTimezone)) {
            validTimezone = 'UTC'; // Fallback to UTC if the timezone is invalid
        }
        function getLocalTime() {
            if (validTimezone) {
                if (validTimezone.startsWith('UTC') || validTimezone.startsWith('+') || validTimezone.startsWith('-')) {
                    const offset = validTimezone.startsWith('UTC') ? validTimezone.replace('UTC', '') : validTimezone;
                    return moment().utcOffset(offset);
                } else if (moment.tz.zone(validTimezone)) {
                    return moment().tz(validTimezone);
                }
            }
            return moment().utc();
        }

        const localTime = getLocalTime();
        // Define ranges with translated labels as keys
        let ranges = {
            [wps_js._('str_today')]: [
                normalizeDate(localTime.clone(), validTimezone),
                normalizeDate(localTime.clone(), validTimezone)
            ],
            [wps_js._('str_yesterday')]: [
                normalizeDate(localTime.clone().subtract(1, 'days'), validTimezone),
                normalizeDate(localTime.clone().subtract(1, 'days'), validTimezone)
            ],
            [wps_js._('str_this_week')]: [
                normalizeDate(localTime.clone().startOf('week'), validTimezone),
                normalizeDate(localTime.clone().endOf('week'), validTimezone)
            ],
            [wps_js._('str_last_week')]: [
                normalizeDate(localTime.clone().subtract(1, 'week').startOf('week'), validTimezone),
                normalizeDate(localTime.clone().subtract(1, 'week').endOf('week'), validTimezone)
            ],
            [wps_js._('str_this_month')]: [
                normalizeDate(localTime.clone().startOf('month'), validTimezone),
                normalizeDate(localTime.clone().endOf('month'), validTimezone)
            ],
            [wps_js._('str_last_month')]: [
                normalizeDate(localTime.clone().subtract(1, 'month').startOf('month'), validTimezone),
                normalizeDate(localTime.clone().subtract(1, 'month').endOf('month'), validTimezone)
            ],
            [wps_js._('str_7days')]: [
                normalizeDate(localTime.clone().subtract(6, 'days'), validTimezone),
                normalizeDate(localTime.clone(), validTimezone)
            ],
            [wps_js._('str_28days')]:[
                normalizeDate(localTime.clone().subtract(27, 'days'), validTimezone),
                normalizeDate(localTime.clone(), validTimezone)
            ],
            [wps_js._('str_30days')]: [
                normalizeDate(localTime.clone().subtract(29, 'days'), validTimezone),
                normalizeDate(localTime.clone(), validTimezone)
            ],
            [wps_js._('str_90days')]: [
                normalizeDate(localTime.clone().subtract(89, 'days'), validTimezone),
                normalizeDate(localTime.clone(), validTimezone)
            ],
            [wps_js._('str_6months')]: [
                normalizeDate(localTime.clone().subtract(6, 'months'), validTimezone),
                normalizeDate(localTime.clone(), validTimezone)
            ],
            [wps_js._('str_year')]: [
                normalizeDate(localTime.clone().startOf('year'), validTimezone),
                normalizeDate(localTime.clone().endOf('year'), validTimezone)
            ]
        };

        function hasTypeParameter() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('post_id');
        }

        if (datePickerBtn.hasClass('js-date-range-picker-all-time')) {
            let post_date = moment(0);
            if (hasTypeParameter()) {
                post_date = wps_js.global.post_creation_date ?
                    normalizeDate(moment(wps_js.global.post_creation_date), validTimezone) :
                    normalizeDate(moment(0), validTimezone);
            } else {
                post_date = wps_js.global.initial_post_date ?
                    normalizeDate(moment(wps_js.global.initial_post_date), validTimezone) :
                    normalizeDate(moment(0), validTimezone);
            }
            ranges[wps_js._('all_time')] = [
                post_date,
                normalizeDate(moment(), validTimezone)
            ];
        }


        const phpDateFormat = datePickerBtn.attr('data-date-format') ? datePickerBtn.attr('data-date-format') : 'MM/DD/YYYY';
        let momentDateFormat = phpToMomentFormat(phpDateFormat);
        // Default dates for the date picker
        let defaultStartDate = moment(wps_js.global.user_date_range.from).format('YYYY-MM-DD');
        let defaultEndDate = moment(wps_js.global.user_date_range.to).format('YYYY-MM-DD');
        if (datePickerBtn.length && datePickerElement.length && datePickerForm.length && !datePickerElement.data('daterangepicker')) {
            datePickerElement.daterangepicker({
                "autoApply": true,
                "ranges": ranges,
                "locale": {
                    "customRangeLabel": wps_js._('custom_range')
                },
                startDate: defaultStartDate,
                endDate: defaultEndDate
            });
        }

        if (wps_js.isset(wps_js.global, 'request_params', 'from') && wps_js.isset(wps_js.global, 'request_params', 'to')) {
            let requestFromDate = wps_js.global.request_params.from;
            if (hasTypeParameter() && requestFromDate && wps_js.global.post_creation_date) {
                const postCreationDate = new Date(wps_js.global.post_creation_date);
                const fromDate = new Date(requestFromDate);

                const fromDateWithoutTime = new Date(fromDate.getFullYear(), fromDate.getMonth(), fromDate.getDate());
                if (fromDateWithoutTime < postCreationDate) {
                    // Check if requestFromDate is not within any of the predefined ranges
                    let isInRange = false;
                    for (const rangeKey in ranges) {
                        const range = ranges[rangeKey];
                        const rangeStart = new Date(range[0]);
                        const rangeEnd = new Date(range[1]);

                        const rangeStartWithoutTime = new Date(rangeStart.getFullYear(), rangeStart.getMonth(), rangeStart.getDate());
                        const rangeEndWithoutTime = new Date(rangeEnd.getFullYear(), rangeEnd.getMonth(), rangeEnd.getDate());

                        if (fromDateWithoutTime >= rangeStartWithoutTime && fromDateWithoutTime <= rangeEndWithoutTime) {
                            isInRange = true;
                            break;
                        }
                    }
                    // If requestFromDate is not in any range, update it to post_creation_date
                    if (!isInRange) {
                        requestFromDate = wps_js.global.post_creation_date;
                    }
                }
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
                if (activeText !== 'All time') {
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
                if (defaultRange !== 'All time') {
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
            const startDate = picker.startDate.startOf('day').utcOffset(validTimezone, true).format('YYYY-MM-DD');
            const endDate = picker.endDate.startOf('day').utcOffset(validTimezone, true).format('YYYY-MM-DD');

            inputFrom.val(startDate);
            inputTo.val(endDate);

            const selectedRange = datePickerElement.data('daterangepicker').chosenLabel;
            datePickerBtn.find('span').html(selectedRange);
            if (selectedRange !== 'All time') {
                jQuery.ajax({
                    url: wps_js.global.ajax_url,
                    method: 'POST',
                    data: {
                        wps_nonce: wps_js.global.rest_api_nonce,
                        action: 'wp_statistics_store_date_range',
                        date: {
                            from: startDate,
                            to: endDate
                        }
                    },
                    beforeSend: function () {
                        datePickerBtn.addClass('wps-disabled');
                    },
                    complete: function (data) {
                        datePickerForm.submit();
                    }
                });
            } else {
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