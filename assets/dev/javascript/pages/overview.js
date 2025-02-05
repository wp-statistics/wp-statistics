if (wps_js.global.page.file === "index.php" || wps_js.is_active('overview_page') || wps_js.global.page.file === "post-new.php" || (wps_js.global.page.file === "post.php" && wps_js.isset(wps_js.global, 'page', 'ID'))) {

    // Split meta boxes into left and right
    const meta_list_side = wps_js.global.meta_boxes.side;
    const meta_list_normal = wps_js.global.meta_boxes.normal;
    const meta_list_column3 = wps_js.global.meta_boxes?.column3;
    const meta_list_column4 = wps_js.global.meta_boxes?.column4;
    const isInsideDashboard = document.getElementById('dashboard-widgets') !== null;

    class DateManager {
        static getDateRange(filter) {
            const today = moment().format('YYYY-MM-DD');
            const currentWeekEnd = moment().endOf('week').format('YYYY-MM-DD');

            const ranges = {
                'today': {start: today, end: today},
                'yesterday': {
                    start: moment().subtract(1, 'days').format('YYYY-MM-DD'),
                    end: moment().subtract(1, 'days').format('YYYY-MM-DD')
                },
                'this_week': {
                    start: moment().startOf('week').format('YYYY-MM-DD'),
                    end: currentWeekEnd
                },
                'last_week': {
                    start: moment().subtract(1, 'week').startOf('week').format('YYYY-MM-DD'),
                    end: moment().subtract(1, 'week').endOf('week').format('YYYY-MM-DD')
                },
                'this_month': {
                    start: moment().startOf('month').format('YYYY-MM-DD'),
                    end: moment().endOf('month').format('YYYY-MM-DD')
                },
                'last_month': {
                    start: moment().subtract(1, 'month').startOf('month').format('YYYY-MM-DD'),
                    end: moment().subtract(1, 'month').endOf('month').format('YYYY-MM-DD')
                },
                '7days': {
                    start: moment().subtract(6, 'days').format('YYYY-MM-DD'),
                    end: today
                },
                '30days': {
                    start: moment().subtract(29, 'days').format('YYYY-MM-DD'),
                    end: today
                },
                '90days': {
                    start: moment().subtract(89, 'days').format('YYYY-MM-DD'),
                    end: today
                },
                '6months': {
                    start: moment().subtract(6, 'months').format('YYYY-MM-DD'),
                    end: today
                },
                'this_year': {
                    start: moment().startOf('year').format('YYYY-MM-DD'),
                    end: moment().endOf('year').format('YYYY-MM-DD')
                },
                'last_year': {
                    start: moment().subtract(1, 'year').startOf('year').format('YYYY-MM-DD'),
                    end: moment().subtract(1, 'year').endOf('year').format('YYYY-MM-DD')
                }
            };

            return ranges[filter] || {start: null, end: null};
        }

        static formatDateRange(startDate, endDate) {
            if (!startDate || !endDate) {
                return '';
            }

            const start = moment(startDate);
            const end = moment(endDate);

            if (start.isSame(end, 'day')) {
                return start.format('MMM D, YYYY');
            }
            return `${start.format('MMM D, YYYY')} - ${end.format('MMM D, YYYY')}`;
        }

        static getDefaultDateRange() {
            const today = moment().format('YYYY-MM-DD');
            return {
                start: moment().subtract(29, 'days').format('YYYY-MM-DD'),
                end: today
            };
        }
    }

    class DatePickerHandler {
        constructor() {
            this.initializeEventListeners();
        }

        initializeEventListeners() {
            this.initializeFilterToggles();
            this.initializeMoreFilters();
            this.initializeDatePicker();
            this.initializeCustomDatePicker();
            this.initializeDateSelection();
            this.initializeFilterClicks();
        }

        initializeFilterToggles() {
            jQuery(document).off('click', '.js-filters-toggle').on('click', '.js-filters-toggle', e => {
                const $target = jQuery(e.currentTarget);
                jQuery('.js-widget-filters').removeClass('is-active');
                jQuery('.postbox').removeClass('has-focus');
                $target.closest('.js-widget-filters').toggleClass('is-active');
                $target.closest('.postbox').toggleClass('has-focus');

                const targetTopPosition = $target[0].getBoundingClientRect().top;
                if (targetTopPosition < 350) {
                    $target.closest('.js-widget-filters').addClass('is-down');
                }
            });
        }

        initializeMoreFilters() {
            jQuery(document).off('click', '.js-show-more-filters').on('click', '.js-show-more-filters', e => {
                e.preventDefault();
                jQuery(e.currentTarget).closest('.c-footer__filters__list').find('.js-more-filters').addClass('is-open');
            });

            jQuery(document).off('click', '.js-close-more-filters').on('click', '.js-close-more-filters', e => {
                e.preventDefault();
                jQuery(e.currentTarget).closest('.js-more-filters').removeClass('is-open');
            });
        }

        initializeDatePicker() {
            jQuery('.js-datepicker-input').each(function () {
                if (!jQuery(this).data('daterangepicker')) {
                    jQuery(this).daterangepicker({
                        autoUpdateInput: false,
                        autoApply: true,
                        locale: {
                            cancelLabel: 'Clear',
                            format: 'YYYY-MM-DD'
                        }
                    });
                }
            });
        }

        initializeCustomDatePicker() {
            jQuery(document).off('click', 'button[data-filter="custom"]').on('click', 'button[data-filter="custom"]', e => {
                const $target = jQuery(e.currentTarget);
                const metaboxKey = $target.attr("data-metabox-key");
                const dateInput = jQuery('#' + metaboxKey + ' .inside .js-datepicker-input').first();

                this.setupDateRangePicker(dateInput, metaboxKey);
                dateInput.data('daterangepicker').show();
            });
        }

        setupDateRangePicker(dateInput, metaboxKey) {
            if (!dateInput.data('daterangepicker')) {
                dateInput.daterangepicker({
                    autoUpdateInput: false,
                    autoApply: true,
                    locale: {
                        cancelLabel: 'Clear',
                        format: 'YYYY-MM-DD'
                    }
                });

                dateInput.on('apply.daterangepicker', (ev, picker) => {
                    const dates = {
                        startDate: picker.startDate.format('YYYY-MM-DD'),
                        endDate: picker.endDate.format('YYYY-MM-DD')
                    };
                    this.handleDateSelection(metaboxKey, dates, picker);
                });
            }
        }

        initializeDateSelection() {
            jQuery('.js-datepicker-input').off('apply.daterangepicker').on('apply.daterangepicker', (ev, picker) => {
                const $metabox = jQuery(ev.currentTarget).closest('.postbox');
                const metaboxId = $metabox.attr('id');
                const dates = {
                    startDate: picker.startDate.format('YYYY-MM-DD'),
                    endDate: picker.endDate.format('YYYY-MM-DD')
                };
                this.handleDateSelection(metaboxId, dates, picker);
            });

            jQuery('.js-datepicker-input').off('hide.daterangepicker').on('hide.daterangepicker', (ev, picker) => {
                if (picker.startDate) {
                    const $metabox = jQuery(ev.currentTarget).closest('.postbox');
                    const metaboxId = $metabox.attr('id');
                    const dates = {
                        startDate: picker.startDate.format('YYYY-MM-DD'),
                        endDate: picker.endDate ? picker.endDate.format('YYYY-MM-DD') : picker.startDate.format('YYYY-MM-DD')
                    };
                    this.handleDateSelection(metaboxId, dates, picker, true);
                }
            });
        }

        handleDateSelection(metaboxId, dates, picker, isHide = false) {
            const $metabox = jQuery('#' + metaboxId);
            const dateRangeText = dates.startDate === dates.endDate ?
                picker.startDate.format('MMM D, YYYY') :
                picker.startDate.format('MMM D, YYYY') + ' - ' + picker.endDate.format('MMM D, YYYY');

            this.updateUIElements($metabox, dateRangeText, isHide);
            this.loadMetaBoxData(metaboxId, dates);
        }

        updateUIElements($metabox, dateRangeText, isHide) {
            const titleText = isHide ? 'Custom Range' : wps_js._('str_custom');
            $metabox.find('.js-filter-title').text(titleText);
            $metabox.find('.hs-filter-range').text(dateRangeText);
            $metabox.find('.js-filters-toggle').text(titleText);
            $metabox.find('.c-footer__filters').removeClass('is-active');
            jQuery('.postbox.has-focus').removeClass('has-focus');
        }

        initializeFilterClicks() {
            jQuery(document).off('click', '.c-footer__filters__list-item:not(.js-show-more-filters):not(.js-close-more-filters):not([data-filter="custom"])')
                .on('click', '.c-footer__filters__list-item:not(.js-show-more-filters):not(.js-close-more-filters):not([data-filter="custom"])', e => {
                    const $target = jQuery(e.currentTarget);
                    const filter = $target.data('filter');
                    const $metabox = $target.closest('.postbox');
                    const metaboxId = $metabox.attr('id');
                    const dates = DateManager.getDateRange(filter);

                    this.updateFilterUI($metabox, $target, dates);
                    this.loadMetaBoxData(metaboxId, dates, filter);
                });
        }

        updateFilterUI($metabox, $target, dates) {
            $metabox.find('.js-filter-title').text($target.text());
            $metabox.find('.hs-filter-range').text(DateManager.formatDateRange(dates.start, dates.end));
            $metabox.find('.js-filters-toggle').text($target.text());
            $metabox.find('.c-footer__filters').removeClass('is-active');
            $target.closest('.postbox.has-focus').removeClass('has-focus');
        }

        loadMetaBoxData(metaboxId, dates, filter = 'custom') {
            wps_js.showLoadingSkeleton(metaboxId);
            loadMetaBoxData(metaboxId, dates.startDate || dates.start, dates.endDate || dates.end, filter)
                .then(response => {
                    wps_js.handleMetaBoxRender(response, metaboxId);
                })
                .catch(error => console.error(`Error loading metabox ${metaboxId}:`, error));
        }
    }

    // Initialize DatePickerHandler
    wps_js.datePickerHandler = new DatePickerHandler();
    wps_js.initDatePickerHandlers = function () {
        wps_js.datePickerHandler.initializeEventListeners();
    };

    function loadMetaBoxData(metaBoxKey, startDate = null, endDate = null, date_filter = null) {
        return new Promise((resolve, reject) => {
            const keyName = metaBoxKey.replace(/-/g, '_').replace('widget', 'metabox');
            let data = {
                'action': `${keyName}_get_data`,
                'wps_nonce': wps_js.global.rest_api_nonce,
                'current_page': wps_js.global.page
            };

            if (date_filter) {
                data.date_filter = date_filter;
            }

            if (startDate && endDate) {
                data.from = startDate;
                data.to = endDate;
            }

            const successHandler = `${metaBoxKey}_success`;
            const errorHandler = `${metaBoxKey}_error`;

            wps_js[successHandler] = function (data) {
                resolve(data);
                return data;
            };

            wps_js[errorHandler] = function (error) {
                reject(error);
                return error;
            };

            wps_js.ajaxQ(
                wps_js.global.admin_url + 'admin-ajax.php',
                data,
                successHandler,
                errorHandler,
                'GET',
                false
            );
        });
    }

    wps_js.handleMetaBoxRender = function (response, metaBoxKey) {
        const keyName = metaBoxKey.replace(/-/g, '_');
        if (typeof wps_js[`render_${keyName}`] === 'function') {
            wps_js[`render_${keyName}`](response, metaBoxKey);
            wps_js.handelReloadButton(metaBoxKey);
            wps_js.handelMetaBoxFooter(metaBoxKey, response);
        }
    };

    function handleScreenOptionsChange() {
        let activeOptions = [];
        // Check if the screen options element exists
        if ($('#adv-settings').length > 0) {
            $('#adv-settings input[type="checkbox"]').each(function () {
                if ($(this).is(':checked')) {
                    // Get the ID and remove the '-hide' suffix
                    let optionId = $(this).attr('id').replace('-hide', '');
                    activeOptions.push(optionId);
                }
            });
        } else {
            activeOptions = [...meta_list_side, ...meta_list_normal];
            if (isInsideDashboard) {
                if(meta_list_column3 ) activeOptions = [...activeOptions, ...meta_list_column3];
                if(meta_list_column4 ) activeOptions = [...activeOptions, ...meta_list_column4];
            }
        }
        return activeOptions;
    }

    function refreshMetaBox(metaBoxKey) {
        loadMetaBoxData(metaBoxKey).then(response => {
            wps_js.handleMetaBoxRender(response, metaBoxKey);
        });
    }

    // Initialize meta boxes on page load
    let activeOptions = handleScreenOptionsChange();

    let normalIndex = 0, sideIndex = 0, column3Index = 0 , column4Index = 0;
    let normalLength = meta_list_normal.length;
    let sideLength = meta_list_side.length;
    let column3Length = isInsideDashboard ? meta_list_column3 ? meta_list_column3.length :0 : 0;
    let column4Length = isInsideDashboard ? meta_list_column4 ? meta_list_column4.length : 0 : 0;
    let isMobile = isInsideDashboard ? window.innerWidth < 800 : window.innerWidth < 759;


    // Loop while either list has elements to process
    function processMetaBoxes(metaList, index, length) {
        while (index < length) {
            if (activeOptions.includes(metaList[index])) {
                refreshMetaBox(metaList[index]);
            }
            index++;
        }
        return index;
    }

    while (normalIndex < normalLength || sideIndex < sideLength || (isInsideDashboard && column3Index < column3Length)) {
        if (isMobile) {
            if (isInsideDashboard) {
                normalIndex = processMetaBoxes(meta_list_normal, normalIndex, normalLength);
                sideIndex = processMetaBoxes(meta_list_side, sideIndex, sideLength);
                if(meta_list_column3) column3Index = processMetaBoxes(meta_list_column3, column3Index, column3Length);
                if(meta_list_column4) column4Index = processMetaBoxes(meta_list_column4, column4Index, column4Length);

            }else{
                sideIndex = processMetaBoxes(meta_list_side, sideIndex, sideLength);
                normalIndex = processMetaBoxes(meta_list_normal, normalIndex, normalLength);
            }

        } else {
            function processNextMetaBox(metaList, index, length) {
                while (index < length && !activeOptions.includes(metaList[index])) {
                    index++;
                }
                if (index < length) {
                    refreshMetaBox(metaList[index]);
                    index++;
                }
                return index;
            }

            if (isInsideDashboard) {
                normalIndex = processNextMetaBox(meta_list_normal, normalIndex, normalLength);
                sideIndex = processNextMetaBox(meta_list_side, sideIndex, sideLength);
                column3Index = processNextMetaBox(meta_list_column3, column3Index, column3Length);
                column4Index = processNextMetaBox(meta_list_column4, column4Index, column4Length);
            } else {
                sideIndex = processNextMetaBox(meta_list_side, sideIndex, sideLength);
                normalIndex = processNextMetaBox(meta_list_normal, normalIndex, normalLength);
            }
        }
    }

    jQuery(document).on('change', '#adv-settings input[type="checkbox"]', function () {
        let metaBoxKey = $(this).attr('id').replace('-hide', '');

        if ($(this).is(':checked')) {
            refreshMetaBox(metaBoxKey);
        }
    });

    // Bind refresh button event for manual refresh
    function bindRefreshEvents(metaList) {
        metaList.forEach((metaBoxKey) => {
            jQuery(document).on('click', `#${metaBoxKey} .wps-refresh`, function () {
                wps_js.showLoadingSkeleton(metaBoxKey);
                refreshMetaBox(metaBoxKey);
            });
        });
    }

    // Bind refresh button events for both lists
    bindRefreshEvents(meta_list_side);
    bindRefreshEvents(meta_list_normal);
    if (isInsideDashboard){
        if(meta_list_column3) bindRefreshEvents(meta_list_column3);
        if(meta_list_column4) bindRefreshEvents(meta_list_column4);
    }

    // Export utility functions
    wps_js.metaBoxInner = key => jQuery('#' + key + ' .inside');

    wps_js.showLoadingSkeleton = function (metaBoxKey) {
        let metaBoxInner = jQuery('#' + metaBoxKey + ' .inside');
        metaBoxInner.html('<div class="wps-skeleton-container"><div class="wps-skeleton-container__skeleton wps-skeleton-container__skeleton--full wps-skeleton-container__skeleton--h-150"></div></div>');
    };

    wps_js.handelReloadButton = key => {
        const selector = "#" + key + " .handle-actions button:first";
        if (!jQuery('#' + key + ' .wps-refresh').length) {
            jQuery(`<button class="handlediv wps-refresh" type="button" title="${wps_js._('reload')}"></button>`).insertBefore(selector);
        }
    };

    wps_js.handelMetaBoxFooter = function (key, response) {
        let html = '<div class="c-footer"><div class="c-footer__filter js-widget-filters">';
        if (response.options && response.options.datepicker) {
            let startDateResponse;
            let endDateResponse;
            let dateFilterTitle = wps_js._(`str_30days`);
            let dateFilterType = wps_js._(`str_30days`);
            if (response?.filters && response.filters.date && response.filters.date.filter) {
                const dateFormat = wps_js.isset(wps_js.global, 'options', 'wp_date_format') ? wps_js.global['options']['wp_date_format'] : 'MM/DD/YYYY';
                let momentDateFormat = phpToMomentFormat(dateFormat);
                const startDateFormat = momentDateFormat.replace(/,?\s?(YYYY|YY)[-/\s]?,?|[-/\s]?(YYYY|YY)[-/\s]?,?/g, "");
                const fromDate = moment(response.filters.date.from);
                const toDate = moment(response.filters.date.to);
                if (fromDate.year() === toDate.year()) {
                    startDateResponse = fromDate.format(startDateFormat);
                } else {
                    startDateResponse = fromDate.format(momentDateFormat);
                }
                endDateResponse = toDate.format(momentDateFormat);
                dateFilterType = response.filters.date.type === 'custom' ? startDateResponse + ' _ ' + endDateResponse : wps_js._(`str_${response.filters.date.filter}`);
                dateFilterTitle = response.filters.date.type === 'custom' ? wps_js._('str_custom') : wps_js._(`str_${response.filters.date.filter}`)
            }

            html += `
                <button class="c-footer__filter__btn js-filters-toggle">` + dateFilterType + `</button>
                <div class="c-footer__filters">
                    <div class="c-footer__filters__current-filter">
                        <span class="c-footer__current-filter__title js-filter-title">` + dateFilterTitle + `</span>
                         <span class="c-footer__current-filter__date-range hs-filter-range">` + startDateResponse + ' - ' + endDateResponse + `</span>
                    </div>
                    <div class="c-footer__filters__list">
                        <button data-metabox-key="${key}" data-filter="today" class="c-footer__filters__list-item">` + wps_js._('str_today') + `</button>
                        <button data-metabox-key="${key}" data-filter="yesterday" class="c-footer__filters__list-item">` + wps_js._('str_yesterday') + `</button>
                        <button data-metabox-key="${key}" data-filter="this_week" class="c-footer__filters__list-item">` + wps_js._('str_this_week') + `</button>
                        <button data-metabox-key="${key}" data-filter="last_week" class="c-footer__filters__list-item">` + wps_js._('str_last_week') + `</button>
                        <button data-metabox-key="${key}" data-filter="this_month" class="c-footer__filters__list-item">` + wps_js._('str_this_month') + `</button>
                        <button data-metabox-key="${key}" data-filter="last_month" class="c-footer__filters__list-item">` + wps_js._('str_last_month') + `</button>
                        <button class="c-footer__filters__list-item c-footer__filters__list-item--more js-show-more-filters">` + wps_js._('str_more') + `</button>
                        <div class="c-footer__filters__more-filters js-more-filters">
                            <button data-metabox-key="${key}" data-filter="7days" class="c-footer__filters__list-item">` + wps_js._('str_7days') + `</button>
                            <button data-metabox-key="${key}" data-filter="30days" class="c-footer__filters__list-item">` + wps_js._('str_30days') + `</button>
                            <button data-metabox-key="${key}" data-filter="90days" class="c-footer__filters__list-item">` + wps_js._('str_90days') + `</button>
                            <button data-metabox-key="${key}" data-filter="6months" class="c-footer__filters__list-item">` + wps_js._('str_6months') + `</button>
                            <button data-metabox-key="${key}" data-filter="this_year" class="c-footer__filters__list-item">` + wps_js._('str_this_year') + `</button>
                            <button data-metabox-key="${key}" data-filter="last_year" class="c-footer__filters__list-item">` + wps_js._('str_last_year') + `</button>
                            <button class="c-footer__filters__close-more-filters js-close-more-filters">` + wps_js._('str_back') + `</button>
                        </div>
                        <input type="text" class="c-footer__filters__custom-date-input js-datepicker-input"/>
                        <button data-metabox-key="${key}" data-filter="custom" class="c-footer__filters__list-item c-footer__filters__list-item--custom js-custom-datepicker">` + wps_js._('str_custom') + `</button>
                    </div>
                </div> `;
        }
        html += `</div>`;
        if (response.options && response.options.button) {
            html += response.options.button;
        }
        html += `</div></div>`;
        let selector = jQuery("#" + key + " h2.hndle");

        if (key === 'wp-statistics-useronline-widget') {
            const current_online = jQuery(".wps-currently-online");
            if (current_online.length && current_online.text() >= 0) {
                const container = selector.find('.wps-wps-currently-online__container');

                if (container.length) {
                    container.find('.wps-wps-currently-online__text').text(current_online.text());
                } else {
                    const online = `<span class="wps-wps-currently-online__container">
                                <span class="wps-wps-currently-online__dot"></span>
                                <span class="wps-wps-currently-online__text">${current_online.text()}</span>
                            </span>`;
                    selector.append(online);
                }
            } else {
                selector.find('.wps-wps-currently-online__container').remove()
            }
        }


        if (key === 'wp-statistics-quickstats-widget') {
            const selector = jQuery("#" + key + " div.handle-actions");
            if (selector.length && !selector.find('.wps-overview-btn').length) {
                const link = `<a href="${wps_js.global.admin_url}admin.php?page=wps_overview_page" class="wps-overview-btn">${wps_js._('go_to_overview')}</a>`;
                selector.prepend(link);
                const overviewBtn = selector.find('.wps-overview-btn');
                if (overviewBtn.length) {
                    overviewBtn.on('click', function (e) {
                        e.preventDefault();
                        window.location.href = this.href;
                    });
                }
            }
        }


        if (response.meta && response.meta.description) {
            if (selector.length && !selector.find('.wps-tooltip').length) {
                const tooltip = response.meta.description;
                const newTitle = '<a href="#" class="wps-tooltip" title="' + tooltip + '"><i class="wps-tooltip-icon"></i></a>';
                if (tooltip) selector.append(newTitle);
            }
        }

        wps_js.metaBoxInner(key).append(html);
    }

    document.addEventListener('click', function (event) {
        if (event.target && event.target.id === 'js-close-notice') {
            let params = {
                'action': 'wp_statistics_dismiss_notices',
                'wps_nonce': wps_js.global.rest_api_nonce,
                'notice_id': 'enable_email_metabox_notice'
            };

            jQuery.ajax({
                url: wps_js.global.admin_url + 'admin-ajax.php',
                type: 'POST',
                dataType: 'json',
                data: params,
                timeout: 30000,
                success: function ({data, success}) {
                    if (success === false) return console.log(data);
                    event.target.parentElement.parentElement.style.display = 'none';
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        }
    });

}