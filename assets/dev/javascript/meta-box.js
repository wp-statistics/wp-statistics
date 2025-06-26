/**
 * Sanitize MetaBox name
 *
 * @param meta_box
 * @returns {*|void|string|never}
 * @see https://www.designcise.com/web/tutorial/how-to-replace-all-occurrences-of-a-word-in-a-javascript-string
 */
wps_js.sanitize_meta_box_name = function (meta_box) {
    return (meta_box.replace(new RegExp('-', 'g'), "_"));
};

/**
 * Get Meta Box Method name
 */
wps_js.get_meta_box_method = function (meta_box) {
    return this.sanitize_meta_box_name(meta_box) + '_meta_box';
};

/**
 * Get Meta Box Tags ID
 */
wps_js.getMetaBoxKey = function (key) {
    return 'wp-statistics-' + key + '-widget';
};

/**
 * Show No Data Error if Meta Box is Empty
 */
wps_js.no_meta_box_data = function () {
    return '<div class="o-wrap o-wrap--no-data">' + wps_js._('no_data') + '</div>';
};

/**
 * Show Error Connection if Meta Box is Empty
 */
wps_js.error_meta_box_data = function (xhr) {
    if (typeof xhr !== 'undefined') {
        try {
            let data = JSON.parse(xhr);

            if (wps_js.isset(data, 'message')) {
                return '<div class="o-wrap o-wrap--no-data">' + data['message'] + '</div>';
            }
        } catch (error) {
            console.log('An unexpected error occurred: ', xhr, error);
        }
    }
    return '<div class="o-wrap o-wrap--no-data">' + wps_js._('rest_connect') + '</div>';
};

/**
 * Get MetaBox information by key
 */
wps_js.get_meta_box_info = function (key) {
    if (key in wps_js.global.meta_boxes) {
        return wps_js.global.meta_boxes[key];
    }
    return [];
};

/**
 * Get MetaBox Lang
 */
wps_js.meta_box_lang = function (meta_box, lang) {
    if (lang in wps_js.global.meta_boxes[meta_box]['lang']) {
        return wps_js.global.meta_boxes[meta_box]['lang'][lang];
    }
    return '';
};

/**
 * Get MetaBox inner text selector
 */
wps_js.meta_box_inner = function (key) {
    return "#" + wps_js.getMetaBoxKey(key) + " div.inside";
};

/**
 * Get MetaBox name by tag ID
 * ex: wp-statistics-summary-widget -> summary
 */
wps_js.meta_box_name_by_id = function (ID) {
    return ID.split('statistics-').pop().split('-widget')[0];
};

/**
 * Create Custom Button for Meta Box
 */
wps_js.meta_box_button = function (key) {
    let selector = "#" + wps_js.getMetaBoxKey(key) + " .handle-actions button:first";
    let meta_box_info = wps_js.get_meta_box_info(key);

    // Gutenberg Button Style
    let gutenberg_style = 'z-index: 9999;position: absolute;top: 1px;display:none;right: calc(44px + 3.24rem) !important;height: 44px !important;';
    let position_gutenberg = 'right';
    if (wps_js.is_active('rtl')) {
        position_gutenberg = 'left';
    }

    // Clean Button
    jQuery("#" + wps_js.getMetaBoxKey(key) + " button[class*=wps-refresh]").remove();

    // Add Refresh Button
    jQuery(`<button class="handlediv wps-refresh" aria-label="reload button"` + (wps_js.is_active('gutenberg') ? ` style="${gutenberg_style}${position_gutenberg}: 3%;" ` : 'style="line-height: 28px;"') + ` type="button" title="` + wps_js._('reload') + `"></button>`).insertBefore(selector);

    if (wps_js.is_active('gutenberg')){
        jQuery('body').addClass('wps-gutenberg');
    }

    jQuery("#" + wps_js.getMetaBoxKey(key) + " .hndle, #" + wps_js.getMetaBoxKey(key) + " .handlediv").on('click', function() {
        jQuery(this).closest('.postbox').addClass('handle');
    });
};

wps_js.meta_box_tooltip = function (key) {
    let selector = "#" + wps_js.getMetaBoxKey(key) + " h2.hndle";
    let meta_box_info = wps_js.get_meta_box_info(key);

    if (meta_box_info.hasOwnProperty('description')) {
        const title = jQuery(selector).text();
        const tooltip = meta_box_info.description;
        const newTitle = '<a href="#" class="wps-tooltip" title="' + tooltip + '"><i class="wps-tooltip-icon"></i></a>';
        if (tooltip) jQuery(selector).append(newTitle);
    }
}

/**
 * Run Meta Box
 *
 * @param key
 * @param params
 * @param button
 */
wps_js.run_meta_box = function (key, params = false, button = true) {

    // Check Exist Meta Box div
    if (wps_js.exist_tag("#" + wps_js.getMetaBoxKey(key)) && (wps_js.is_active('gutenberg') || (!wps_js.is_active('gutenberg') && jQuery("#" + wps_js.getMetaBoxKey(key)).is(":visible")))) {

        // Meta Box Main
        let main = jQuery(wps_js.meta_box_inner(key));

        // Get Meta Box Method
        let method = wps_js.get_meta_box_method(key);

        // Add tooltip
        wps_js.meta_box_tooltip(key);

        // Check Exist Method name
        if (method in wps_js) {

            // Check PlaceHolder Method
            if ("placeholder" in wps_js[method]) {
                main.html(wps_js[method]["placeholder"]());
            } else {
                main.html(wps_js.placeholder());
            }

            // Add Custom Button
            if (button === true) {
                wps_js.meta_box_button(key);
            }

            // Get Meta Box Data
            let arg = {'name': key};
            if (params !== false) {
                arg = Object.assign(params, arg);
            }

            // Check Request Params in Meta box
            if ("params" in wps_js[method]) {
                arg = Object.assign(arg, wps_js[method]['params']());
            }

            // Run
            wps_js.ajaxQ('metabox', arg, method, 'error_meta_box_data');
        }
    }
};

wps_js.prepare_date_filter_data = function (args) {
    let data = {'ago': ''};
    if (args.hasOwnProperty('footer_options')) {
        const selectedDateFilter = args.footer_options.default_date_filter;
        if (selectedDateFilter.length) {
            let dateFilterSplted = selectedDateFilter.split('|');
            if (dateFilterSplted[0] == 'filter') {
                data.ago = dateFilterSplted[1];
            } else {
                let customDateRange = dateFilterSplted[1].split(':');
                data.ago = '';
                data.from = customDateRange[1];
                data.to = customDateRange[2];
            }
        }
    }
    return data;
}

/**
 * Load all Meta Boxes
 */
wps_js.run_meta_boxes = function (list = false) {
    if (list === false) {
        list = Object.keys(wps_js.global.meta_boxes);
    }
    list.forEach(function (value) {
        let args = wps_js.global.meta_boxes[value];

        // Check Date Filter
        let data = wps_js.prepare_date_filter_data(args);

        // Run Meta Box
        wps_js.run_meta_box(value, data);
    });
};

/**
 * Render Meta Box Footer
 */
wps_js.meta_box_footer = function (key, data) {
    let params = {
        'footer_options': {
            'filter_by_date': false,
            'default_date_filter': '',
            'display_more_link': false,
            'more_link_title': ''
        }
    };

    const args = wps_js.global.meta_boxes[key];
    if (args.hasOwnProperty('footer_options')) {
        Object.assign(params.footer_options, args.footer_options);
    }

    let selectedDateFilter = '';
    if (data.hasOwnProperty('filter')) {
        selectedDateFilter = data.filter;
    } else if (typeof params.footer_options.default_date_filter != 'undefined') {
        selectedDateFilter = params.footer_options.default_date_filter;
    }

    let selectedStartDate = '';
    if (data.hasOwnProperty('filter_start_date')) {
        selectedStartDate = data.filter_start_date;
    }

    let selectedEndDate = '';
    if (data.hasOwnProperty('filter_end_date')) {
        selectedEndDate = data.filter_end_date;
    }

    let fromDate = '';
    if (data.hasOwnProperty('from')) {
        fromDate = data.from;
    }

    let toDate = '';
    if (data.hasOwnProperty('to')) {
        toDate = data.to;
    }

    if (!params.footer_options.filter_by_date && !params.footer_options.display_more_link) return;

    let html = '<div class="c-footer"><div class="c-footer__filter js-widget-filters">';
    if (params.footer_options.filter_by_date) {
        html += `
            <button class="c-footer__filter__btn js-filters-toggle">` + wps_js._('str_' + selectedDateFilter) + `</button>
            <div class="c-footer__filters">
                <div class="c-footer__filters__current-filter">
                    <span class="c-footer__current-filter__title js-filter-title">Last 7 days</span>
                    <span class="c-footer__current-filter__date-range hs-filter-range">May 12,2020  -  May 20, 2020</span>
                </div>
                <div class="c-footer__filters__list">
                    <button data-metabox-key="${key}" data-filter="today" class="c-footer__filters__list-item">` + wps_js._('str_today') + `</button>
                    <button data-metabox-key="${key}" data-filter="yesterday" class="c-footer__filters__list-item">` + wps_js._('str_yesterday') + `</button>
                    <button data-metabox-key="${key}" data-filter="this_week" class="c-footer__filters__list-item">` + wps_js._('str_this_week') + `</button>
                    <button data-metabox-key="${key}" data-filter="last_week" class="c-footer__filters__list-item">` + wps_js._('str_last_week') + `</button>
                    <button data-metabox-key="${key}" data-filter="this_month" class="c-footer__filters__list-item">` + wps_js._('str_this_month') + `</button>
                    <button data-metabox-key="${key}" data-filter="last_month" class="c-footer__filters__list-item">` + wps_js._('str_last_month') + `</button>
                    <button class="c-footer__filters__list-item c-footer__filters__list-item--more" onclick="jQuery(this).closest('.c-footer__filters__list').find('.js-more-filters').addClass('is-open')">` + wps_js._('str_more') + `</button>
                    <div class="c-footer__filters__more-filters js-more-filters">
                        <button data-metabox-key="${key}" data-filter="7days" class="c-footer__filters__list-item">` + wps_js._('str_7days') + `</button>
                        <button data-metabox-key="${key}" data-filter="30days" class="c-footer__filters__list-item">` + wps_js._('str_30days') + `</button>
                        <button data-metabox-key="${key}" data-filter="90days" class="c-footer__filters__list-item">` + wps_js._('str_90days') + `</button>
                        <button data-metabox-key="${key}" data-filter="6months" class="c-footer__filters__list-item">` + wps_js._('str_6months') + `</button>
                        <button data-metabox-key="${key}" data-filter="this_year" class="c-footer__filters__list-item">` + wps_js._('str_this_year') + `</button>
                        <button class="c-footer__filters__close-more-filters" onclick="jQuery(this).closest('.js-more-fi' + 'lters').removeClass('is-open')">` + wps_js._('str_back') + `</button>
                    </div>
                    <input type="text" class="c-footer__filters__custom-date-input js-datepicker-input"/>
                    <button data-metabox-key="${key}" data-filter="custom" class="c-footer__filters__list-item c-footer__filters__list-item--custom js-custom-datepicker">` + wps_js._('str_custom') + `</button>
                </div>
            </div>
        `;
    }
    html += `</div><div class="c-footer__more">`;
    if (params.footer_options.display_more_link) {
        html += `<a class="c-footer__more__link" href="` + args.page_url + `">${params.footer_options.more_link_title}<svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.4191 7.98577L7.73705 5.22727L8.44415 4.5L12.3333 8.50003L8.44415 12.5L7.73705 11.7727L10.4191 9.01429H4.33325V7.98577H10.4191Z" fill="#56585A"/></svg></a>`;
    }
    html += `</div></div>`;

    jQuery(wps_js.meta_box_inner(key)).append(html);

    const datePickerElement = jQuery(wps_js.meta_box_inner(key)).find('.js-datepicker-input').first();
    datePickerElement.daterangepicker({"autoApply": true});
    datePickerElement.on('apply.daterangepicker', function (ev, picker) {
        wps_js.run_meta_box(key, {
            'from': picker.startDate.format('YYYY-MM-DD'),
            'to': picker.endDate.format('YYYY-MM-DD')
        });
    });

    /**
     * Add click event to filters toggle
     */
    jQuery('.js-filters-toggle:not(.is-ready)').on('click', function () {
        jQuery('.js-widget-filters').removeClass('is-active');
        jQuery('.postbox').removeClass('has-focus');
        jQuery(this).closest('.js-widget-filters').toggleClass('is-active');
        jQuery(this).closest('.postbox').toggleClass('has-focus')

        /**
         * Open filters to the downside if there's not enough space.
         */
        if (!jQuery(this).hasClass('is-active')) {
            const targetTopPosition = jQuery(this)[0].getBoundingClientRect().top;
            if (targetTopPosition < 350) {
                jQuery(this).closest('.js-widget-filters').addClass('is-down');
            }
        }
    });
    jQuery('.js-filters-toggle:not(.is-ready)').addClass('is-ready');


    wps_js.set_date_filter_as_selected(key, selectedDateFilter, selectedStartDate, selectedEndDate, fromDate, toDate);
};

/**
 * Set As Selected Date Filter
 */
wps_js.set_date_filter_as_selected = function (key, selectedDateFilter = "30days", selectedStartDate, selectedEndDate, fromDate, toDate) {
    const metaBoxInner = jQuery(wps_js.meta_box_inner(key));
    const filterBtn = jQuery(metaBoxInner).find('.c-footer__filter__btn');
    const filterList = jQuery(metaBoxInner).find('.c-footer__filters__list');
    const currentFilterTitle = jQuery(metaBoxInner).find('.c-footer__current-filter__title');
    const currentFilterRange = jQuery(metaBoxInner).find('.c-footer__current-filter__date-range');

    if (!selectedDateFilter) {
        selectedDateFilter = "30days"
    }

    if (selectedDateFilter.length) {
        filterList.find('button[data-filter]').removeClass('is-selected');
        filterList.find('button[data-filter="' + selectedDateFilter + '"').addClass('is-selected');
        filterBtn.text(wps_js._('str_' + selectedDateFilter));
        currentFilterTitle.text(wps_js._('str_' + selectedDateFilter));
        if (selectedDateFilter == 'custom') {
            filterBtn.text(selectedStartDate + ' - ' + selectedEndDate);
            const datePickerElement = jQuery(wps_js.meta_box_inner(key)).find('.js-datepicker-input').first();
            datePickerElement.data('daterangepicker').setStartDate(moment(fromDate).format('MM/DD/YYYY'));
            datePickerElement.data('daterangepicker').setEndDate(moment(toDate).format('MM/DD/YYYY'));
        }
    }
    if (selectedStartDate.length && selectedEndDate.length) {
        currentFilterRange.text(selectedStartDate + ' - ' + selectedEndDate);
    }
}

/**
 * Meta Box Footer Handle Date Filter
 */
jQuery(document).on("click", 'button[data-filter]:not(.c-footer__filters__list-item--custom)', function () {
    wps_js.run_meta_box(jQuery(this).attr('data-metabox-key'), {'ago': jQuery(this).attr('data-filter')});
});


/**
 * Disable Close WordPress Post ox for Meta Box Button
 *
 * @see wp-admin/js/postbox.js:107
 */
jQuery(document).on('mouseenter mouseleave', '.wps-refresh, .wps-more', function (ev) {
    if (ev.type === 'mouseenter') {
        wps_js.wordpress_postbox_ajax('disable');
    } else {
        wps_js.wordpress_postbox_ajax('enable');
    }
});



/**
 * Watch Show/Hide Meta Box in WordPress Dashboard
 * We dont Use PreventDefault Because WordPress Core uses Checked checkbox.
 */
jQuery(document).on("click", 'input[type=checkbox][id^="wp-statistics-"][id$="-widget-hide"]', function () {

    // Check is Checked For Show Post Box
    if (jQuery(this).is(':checked')) {

        // Get Meta Box name By ID
        let ID = jQuery(this).attr("id");
        let meta_box_name = wps_js.meta_box_name_by_id(ID);

        // Run Meta Box
        wps_js.run_meta_box(meta_box_name);
    }
});

/**
 * Show Select Date Time For Chart MetaBox
 */
wps_js.btn_group_chart = function (chart, args = false) {

    // Datetime Select List
    let select_list = {
        7: wps_js._('str_week'),
        30: wps_js._('str_month'),
        365: wps_js._('str_year')
    };

    // Check Active time
    var active;
    if (args.type == "ago") {
        active = parseInt(args.days);
    }

    // Create Html Data
    let html = `<div class="wps-btn-group"><div class="btn-group" role="group">`;

    // Show Data
    Object.keys(select_list).forEach(function (key) {
        html += `<button type="button" class="btn ` + (key == active ? 'btn-primary' : 'btn-default') + `" data-chart-time="${chart}" data-time="${key}">${select_list[key]}</button>`;
    });

    // Add Custom
    html += `<button type="button" class="btn ` + (args.type == "between" ? 'btn-primary' : 'btn-default') + `" data-custom-date-picker="${chart}">${wps_js._('custom')}</button>`;
    html += `</div></div>`;

    // Show Jquery Date Picker
    html += `
    <div data-chart-date-picker="${chart}"` + (args.type == "ago" ? ' style="display:none;"' : '') + `>
        <input type="text" size="18" name="date-from" data-wps-date-picker="from" value="${args['from']}" placeholder="YYYY-MM-DD" autocomplete="off">
        ` + wps_js._('to') + `
        <input type="text" size="18" name="date-to" data-wps-date-picker="to" value="${args['to']}" placeholder="YYYY-MM-DD" autocomplete="off">
        <input type="submit" value="` + wps_js._('go') + `" data-between-chart-show="${chart}" class="button-primary">
        <input type="hidden" name="" id="date-from" value="${args['from']}">
        <input type="hidden" name="" id="date-to" value="${args['to']}">
    </div>
    `;

    // Show HTMl
    return html;
};

/**
 * Seat Active Class after Click Btn Group
 */
jQuery(document).on("click", '.wps-btn-group button', function () {
    jQuery('.wps-btn-group button').attr('class', 'btn btn-default');
    jQuery(this).attr('class', 'btn btn-primary');
});

/**
 * SlideToggle Click on Custom Date Range
 */
jQuery(document).on("click", 'button[data-custom-date-picker]', function () {
    jQuery('div[data-chart-date-picker= ' + jQuery(this).attr('data-custom-date-picker') + ']').slideDown();
});

/**
 * Button Group Handle Chart time Show
 */
jQuery(document).on("click", 'button[data-chart-time]', function () {
    wps_js.run_meta_box(jQuery(this).attr('data-chart-time'), {'ago': jQuery(this).attr('data-time'), 'no-data': 'no'});
});

/**
 * Send From/To Chart
 */
jQuery(document).on("click", 'input[data-between-chart-show]', function () {
    let chart = jQuery(this).attr('data-between-chart-show');
    wps_js.run_meta_box(chart, {
        'from': jQuery("div[data-chart-date-picker=" + chart + "] input[id=date-from]").val(),
        'to': jQuery("div[data-chart-date-picker=" + chart + "] input[id=date-to]").val(),
        'no-data': 'no'
    });
});

/**
 * Close filters when clicking outside the filters
 * */
jQuery(document).on("click", function (event) {
    if (!jQuery(event.target).closest(".js-widget-filters").length) {
        jQuery('.js-widget-filters').removeClass('is-active');
        jQuery('.postbox.has-focus').removeClass('has-focus');
        jQuery('.c-footer__filter__btn.is-active').removeClass('is-active');
        setTimeout(function () {
            jQuery('.js-widget-filters').removeClass('is-down');
        }, 500)
    } else {
        const targetClasses = event.target.classList;
        if (targetClasses.contains('c-footer__filter__btn') && targetClasses.contains('is-active')) {
            event.target.classList.remove('is-active');
            jQuery('.js-widget-filters').removeClass('is-active');
            jQuery('.postbox.has-focus').removeClass('has-focus');

            setTimeout(function () {
                jQuery('.js-widget-filters').removeClass('is-down');
            }, 500)

        } else if (targetClasses.contains('c-footer__filter__btn') && !targetClasses.contains('is-active')) {
            event.target.classList.add('is-active');
        }
    }
});

const wpsAoutWidget = document.getElementById('wp-statistics-about-widget');
if (wpsAoutWidget) {
    if (!wpsAoutWidget.querySelector('.js-wps-widget-customization-empty') &&
        !wpsAoutWidget.querySelector('.wps-about-widget__premium')) {
        wpsAoutWidget.classList.add('wp-statistics-about-widget__customize');
    }
}


