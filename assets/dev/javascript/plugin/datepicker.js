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
        if (!date) return null;

        let normalizedDate;
        if (timezone && (timezone.startsWith('UTC') || timezone.startsWith('+') || timezone.startsWith('-'))) {
            const offset = timezone.startsWith('UTC') ? timezone.replace('UTC', '') : timezone;
            normalizedDate = moment(date).utcOffset(offset);
        } else if (moment.tz.zone(timezone)) {
            normalizedDate = moment(date).tz(timezone);
        } else {
            normalizedDate = moment(date).utc();
        }

        return normalizedDate.clone().startOf('day');
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
            return moment();
        }

        const localTime = getLocalTime();

        // Check for GSC custom date picker configuration (from wp_statistics_admin_assets filter)
        const gsc = (wps_js.global && wps_js.global.gsc) ? wps_js.global.gsc : null;
        const gscConfig = gsc ? gsc.date_picker_config : null;
        const gscMeta = gsc ? gsc.date : null;
        const gscMetaKey = gsc ? gsc.meta_key : null;
        
        // Get gsc_meta for date_meta
        const gscMetaData = (wps_js.global && wps_js.global.gsc_meta) ? wps_js.global.gsc_meta : null;
        const gscDateMeta = gscMetaData ? gscMetaData.date_meta : null;


        // Map period keys to their translated labels and date calculations
        const rangeDefinitions = {
            'today': {
                label: wps_js._('str_today'),
                dates: [
                    normalizeDate(localTime.clone(), validTimezone),
                    normalizeDate(localTime.clone(), validTimezone)
                ]
            },
            'yesterday': {
                label: wps_js._('str_yesterday'),
                dates: [
                    normalizeDate(localTime.clone().subtract(1, 'days'), validTimezone),
                    normalizeDate(localTime.clone().subtract(1, 'days'), validTimezone)
                ]
            },
            'this_week': {
                label: wps_js._('str_this_week'),
                dates: [
                    normalizeDate(localTime.clone().startOf('week'), validTimezone),
                    normalizeDate(localTime.clone().endOf('week'), validTimezone)
                ]
            },
            'last_week': {
                label: wps_js._('str_last_week'),
                dates: [
                    normalizeDate(localTime.clone().subtract(1, 'week').startOf('week'), validTimezone),
                    normalizeDate(localTime.clone().subtract(1, 'week').endOf('week'), validTimezone)
                ]
            },
            'this_month': {
                label: wps_js._('str_this_month'),
                dates: [
                    normalizeDate(localTime.clone().startOf('month'), validTimezone),
                    normalizeDate(localTime.clone().endOf('month'), validTimezone)
                ]
            },
            'last_month': {
                label: wps_js._('str_last_month'),
                dates: [
                    normalizeDate(localTime.clone().subtract(1, 'month').startOf('month'), validTimezone),
                    normalizeDate(localTime.clone().subtract(1, 'month').endOf('month'), validTimezone)
                ]
            },
            '7days': {
                label: wps_js._('str_7days'),
                dates: [
                    normalizeDate(localTime.clone().subtract(6, 'days'), validTimezone),
                    normalizeDate(localTime.clone(), validTimezone)
                ]
            },
            '14days': {
                label: wps_js._('str_14days'),
                dates: [
                    normalizeDate(localTime.clone().subtract(13, 'days'), validTimezone),
                    normalizeDate(localTime.clone(), validTimezone)
                ]
            },
            '28days': {
                label: wps_js._('str_28days'),
                dates: [
                    normalizeDate(localTime.clone().subtract(27, 'days'), validTimezone),
                    normalizeDate(localTime.clone(), validTimezone)
                ]
            },
            '30days': {
                label: wps_js._('str_30days'),
                dates: [
                    normalizeDate(localTime.clone().subtract(29, 'days'), validTimezone),
                    normalizeDate(localTime.clone(), validTimezone)
                ]
            },
            '90days': {
                label: wps_js._('str_90days'),
                dates: [
                    normalizeDate(localTime.clone().subtract(89, 'days'), validTimezone),
                    normalizeDate(localTime.clone(), validTimezone)
                ]
            },
            '6months': {
                label: wps_js._('str_6months'),
                dates: [
                    normalizeDate(localTime.clone().subtract(6, 'months'), validTimezone),
                    normalizeDate(localTime.clone(), validTimezone)
                ]
            },
            'this_year': {
                label: wps_js._('str_year'),
                dates: [
                    normalizeDate(localTime.clone().startOf('year'), validTimezone),
                    normalizeDate(localTime.clone().endOf('year'), validTimezone)
                ]
            }
        };

        // Build ranges based on GSC config or use default ranges
        let ranges = {};
        if (gscConfig && gscConfig.ranges && Array.isArray(gscConfig.ranges)) {
            // Use only the specified ranges for GSC tabs
            gscConfig.ranges.forEach(function(rangeKey) {
                if (rangeDefinitions[rangeKey]) {
                    ranges[rangeDefinitions[rangeKey].label] = rangeDefinitions[rangeKey].dates;
                }
            });
        } else {
            // Default ranges
            ranges = {
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
        }

        function hasTypeParameter() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('post_id');
        }

        if (datePickerBtn.hasClass('js-date-range-picker-all-time')) {
            let post_date = moment(0);
            if (hasTypeParameter()) {
                if (wps_js.global.post_creation_date) {
                     if (validTimezone && moment.tz.zone(validTimezone)) {
                        post_date = moment.tz(wps_js.global.post_creation_date, validTimezone).startOf('day');
                    } else if (validTimezone && (validTimezone.startsWith('UTC') || validTimezone.startsWith('+') || validTimezone.startsWith('-'))) {
                        const offset = validTimezone.startsWith('UTC') ? validTimezone.replace('UTC', '') : validTimezone;
                        post_date = moment(wps_js.global.post_creation_date).utcOffset(offset, true).startOf('day');
                    } else {
                        post_date = moment(wps_js.global.post_creation_date).startOf('day');
                    }
                } else {
                    post_date = normalizeDate(moment(0), validTimezone);
                }
            } else {
                if (wps_js.global.initial_post_date) {
                     if (validTimezone && moment.tz.zone(validTimezone)) {
                        post_date = moment.tz(wps_js.global.initial_post_date, validTimezone).startOf('day');
                    } else if (validTimezone && (validTimezone.startsWith('UTC') || validTimezone.startsWith('+') || validTimezone.startsWith('-'))) {
                        const offset = validTimezone.startsWith('UTC') ? validTimezone.replace('UTC', '') : validTimezone;
                        post_date = moment(wps_js.global.initial_post_date).utcOffset(offset, true).startOf('day');
                    } else {
                        post_date = moment(wps_js.global.initial_post_date).startOf('day');
                    }
                } else {
                    post_date = normalizeDate(moment(0), validTimezone);
                }
            }
            ranges[wps_js._('all_time')] = [
                post_date,
                normalizeDate(moment(), validTimezone)
            ];
        }


        const phpDateFormat = datePickerBtn.attr('data-date-format') ? datePickerBtn.attr('data-date-format') : 'MM/DD/YYYY';
        const createDate = datePickerBtn.attr('data-date-create') ? datePickerBtn.attr('data-date-create') : null;
        let momentDateFormat = phpToMomentFormat(phpDateFormat);
        const DATE_FORMAT = 'YYYY-MM-DD';
        // Default dates for the date picker
        let defaultStartDate = moment(wps_js.global.user_date_range.from).format(DATE_FORMAT);
        let defaultEndDate = moment(wps_js.global.user_date_range.to).format(DATE_FORMAT);

        // For GSC tabs, check if current date range is valid, otherwise use 28 days default
        if (gscConfig && gscConfig.ranges) {
            const currentFrom = moment(defaultStartDate);
            const currentTo = moment(defaultEndDate);
            const daysDiff = currentTo.diff(currentFrom, 'days') + 1;

            // Check if current range matches one of the allowed GSC ranges (7, 14, 28, 90 days)
            const allowedDays = [7, 14, 28, 90];
            const isValidRange = allowedDays.includes(daysDiff) && currentTo.isSame(localTime.clone().startOf('day'), 'day');

            if (!isValidRange) {
                // Reset to 28 days (default)
                defaultStartDate = normalizeDate(localTime.clone().subtract(27, 'days'), validTimezone).format(DATE_FORMAT);
                defaultEndDate = normalizeDate(localTime.clone(), validTimezone).format(DATE_FORMAT);
             }
        }

        let minDate = null;
        if (createDate) {
            const parsedDate = moment(createDate, [momentDateFormat, 'YYYY-MM-DD', 'YYYY/MM/DD', 'YYYY-MM-DD HH:mm:ss'], true);
            if (parsedDate.isValid()) {
                minDate = parsedDate.clone().startOf('day');
                if (moment(defaultStartDate).isBefore(minDate)) {
                    defaultStartDate = minDate.format(DATE_FORMAT);
                }
            }
        }

        if (datePickerBtn.length && datePickerElement.length && datePickerForm.length && !datePickerElement.data('daterangepicker')) {
            const datePickerOptions = {
                "autoApply": true,
                "ranges": ranges,
                "locale": {
                    "customRangeLabel": wps_js._('custom_range')
                },
                startDate: defaultStartDate,
                endDate: defaultEndDate,
                isInvalidDate: function (date) {
                    if (!minDate) return false;
                    const normalizedDate = normalizeDate(date, validTimezone);
                    const normalizedMinDate = normalizeDate(minDate, validTimezone);
                    return normalizedDate.isBefore(normalizedMinDate);
                }
            };

            // For GSC tabs, disable custom range and only show predefined ranges
            if (gscConfig) {
                datePickerOptions.showCustomRangeLabel = false;
                datePickerOptions.alwaysShowCalendars = false;
            }

            if (minDate) {
                datePickerOptions.minDate = minDate;
            }

            datePickerElement.daterangepicker(datePickerOptions);

            // Add GSC-specific class to daterangepicker for custom styling
            if (gscConfig) {
                const picker = datePickerElement.data('daterangepicker');
                if (picker && picker.container) {
                    picker.container.addClass('wps-gsc-datepicker');
                    picker.container.find('.ranges ul').addClass('wps-gsc-ranges');
                }
            }

            // Hide ranges before createDate
            if (minDate) {
                const picker = datePickerElement.data('daterangepicker');
                const rangeList = picker.container.find('.ranges li');
                const normalizedMinDate = moment(minDate).utcOffset(validTimezone, true).startOf('day');

                rangeList.each(function() {
                    const rangeText = $(this).text();
                    const range = ranges[rangeText];
                    if (range) {
                        const rangeStart = moment(range[0]).utcOffset(validTimezone, true).startOf('day');
                        if (rangeStart.isBefore(normalizedMinDate)) {
                            $(this).hide();
                        }
                    }
                });
            }
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
            const startMoment = moment(requestFromDate).utcOffset(validTimezone, true);
            const endMoment = moment(requestToDate).utcOffset(validTimezone, true);
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
            const defaultStartMoment = moment(defaultStartDate).utcOffset(validTimezone, true);
            const defaultEndMoment = moment(defaultEndDate).utcOffset(validTimezone, true);
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

        // For GSC tabs, override display with actual GSC date range and add tooltip (must run last)
        if (gscMeta && gscMeta.from && gscMeta.to) {
            const gscFromDate = moment(gscMeta.from);
            const gscToDate = moment(gscMeta.to);

            let gscRangeText;
            if (gscFromDate.year() === gscToDate.year()) {
                const startFormat = momentDateFormat.replace(/,?\s?(YYYY|YY)[-/\s]?,?|[-/\s]?(YYYY|YY)[-/\s]?,?/g, "");
                gscRangeText = `${gscFromDate.format(startFormat)} - ${gscToDate.format(momentDateFormat)}`;
            } else {
                gscRangeText = `${gscFromDate.format(momentDateFormat)} - ${gscToDate.format(momentDateFormat)}`;
            }

            // Calculate which range label to show based on days difference
            const daysDiff = gscToDate.diff(gscFromDate, 'days') + 1;
            let rangeLabel = '';
            if (daysDiff === 7) rangeLabel = wps_js._('str_7days');
            else if (daysDiff === 14) rangeLabel = wps_js._('str_14days');
            else if (daysDiff === 28) rangeLabel = wps_js._('str_28days');
            else if (daysDiff === 90) rangeLabel = wps_js._('str_90days');

            const displayText = rangeLabel
                ? `<span class="wps-date-range">${rangeLabel}</span>${gscRangeText}`
                : gscRangeText;

            datePickerBtn.find('span').html(displayText);
            datePickerBtn.addClass('custom-range');

            // Add tooltip with GSC delay info
            const tooltipText = wps_js._('gsc_data_delay_info').replace('%s', gscToDate.format(momentDateFormat));
            datePickerBtn.attr('title', tooltipText);
            datePickerBtn.addClass('wps-tooltip');
        }

        datePickerElement.on('show.daterangepicker', function (ev, picker) {
            const correspondingPicker = picker.container;
            jQuery(correspondingPicker).addClass(ev.target.className);
        });
        datePickerElement.on('apply.daterangepicker', function (ev, picker) {
            const inputFrom = datePickerForm.find('.js-date-range-picker-input-from').first();
            const inputTo = datePickerForm.find('.js-date-range-picker-input-to').first();

            // Get the dates in the target timezone
            const startDate = picker.startDate.clone().startOf('day').format('YYYY-MM-DD');
            const endDate = picker.endDate.clone().startOf('day').format('YYYY-MM-DD');

            inputFrom.val(startDate);
            inputTo.val(endDate);

            const selectedRange = datePickerElement.data('daterangepicker').chosenLabel;
            datePickerBtn.find('span').html(selectedRange);
            if (selectedRange !== 'All time') {
                const ajaxData = {
                    wps_nonce: wps_js.global.rest_api_nonce,
                    action: 'wp_statistics_store_date_range',
                    date: {
                        from: startDate,
                        to: endDate
                    }
                };

                // Add meta_key for GSC pages - use date_meta as meta_key
                if (gscDateMeta) {
                    ajaxData.meta_key = gscDateMeta;
                }

                jQuery.ajax({
                    url: wps_js.global.ajax_url,
                    method: 'POST',
                    data: ajaxData,
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

function fixDatePickerA11y() {
    const datePickers = document.querySelectorAll('.drp-calendar');

    datePickers.forEach((datePicker) => {
        const headers = datePicker.querySelectorAll('.calendar-table table thead tr:first-child th');

        if (headers.length) {
            if (headers[0] && headers[0].textContent.trim() === '') {
                headers[0].innerHTML = '<span class="screen-reader-text">Previous Month</span>';
            }

            if (headers[2] && headers[2].textContent.trim() === '') {
                headers[2].innerHTML = '<span class="screen-reader-text">Next Month</span>';
            }
        }

        datePicker.querySelectorAll('.calendar-table thead tr:nth-child(2) th').forEach((th) => {
            th.setAttribute('scope', 'col');
        });
    });
}

setTimeout(fixDatePickerA11y, 300);