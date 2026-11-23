/**
 * Check Exist Dom
 */
wps_js.exist_tag = function (tag) {
    return (jQuery(tag).length);
};


/**
 * Loading button
 */
wps_js.loading_button = function (btn) {
    btn.classList.add('wps-loading-button');
};


/**
 * Jquery UI Picker
 */
wps_js.date_picker = function () {
    const datePickerField = jQuery('input[data-wps-date-picker]');
    if (datePickerField.length) {
        datePickerField.daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1998,
            drops: 'up',
            opens: document.getElementById('TB_window') ? 'center' : 'right',
            maxYear: parseInt(new Date().getFullYear() + 1),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
        datePickerField.on('show.daterangepicker', function (ev, picker) {
            const correspondingPicker = picker.container;
            jQuery(correspondingPicker).addClass(ev.target.className);
        });
    }

};

wps_js.formatNumber = function (num, fixed = 0) {
    if (num === null) {
        return null;
    }
    num = parseFloat(num.toString().trim().replace(/[, ]/g, ''));

    if (isNaN(num)) {
        return null;
    }

    if (num === 0) {
        return '0';
    }
    fixed = (!fixed || fixed < 0) ? 0 : fixed;
    var b = (parseInt(num)).toPrecision(2).split("e"),
        k = b.length === 1 ? 0 : Math.floor(Math.min(b[1].slice(1), 14) / 3),
        c = k < 1 ? num.toFixed(0 + fixed) : (num / Math.pow(10, k * 3)).toFixed(1 + fixed),
        d = c < 0 ? c : Math.abs(c),
        e = d + ['', 'K', 'M', 'B', 'T'][k];
    return e;
}

wps_js.vectorMap = function (data) {
    let pin = Array();
    let colors = {};
    if (data?.codes?.length > 0) {
        const geoCountryData = {};
        data.codes.forEach((code, index) => {
            const visitors_raw = Number(data.raw_data[index]) || 0;
            const visitors = data.data[index];
            geoCountryData[code.toLowerCase()] = {
                label: data.labels[index],
                flag: data.flags[index],
                visitors_raw,
                visitors
            };
        });

        const maxVisitors = Math.max(1, ...Object.values(geoCountryData).map(country => country.visitors_raw));

        Object.keys(geoCountryData).forEach(code => {
            const country = geoCountryData[code];

            const intensity = country.visitors_raw / maxVisitors;
            // #EBF5FF to #3288D7
            const r = Math.round(235 - (185 * intensity));  // From 235 to 50
            const g = Math.round(245 - (109 * intensity));  // From 245 to 136
            const b = Math.round(255 - (40 * intensity));   // From 255 to 215

            colors[code] = `rgb(${r}, ${g}, ${b})`;

            pin[code] = `<div class='map-html-marker'>
                    <div class="map-country-header">
                        <img src='${country.flag}' 
                            alt="${country.label}" 
                            title='${country.label}' 
                            class='log-tools wps-flag'/> 
                            <span>${country.label}  </span>
                    </div>
                    <div class="map-country-content">
                        <div>${wps_js._('visitors')}</div>
                        <div>${country.visitors}</div>
                    </div>
                </div>`;
        });
    }
    jQuery('#wp-statistics-visitors-map').vectorMap({
        map: 'world_en',
        backgroundColor: '#fff',
        borderColor: '#fff',
        borderOpacity: 0.6,
        color: '#e6e6e6',
        selectedColor: '#596773',
        hoverColor: '#596773',


        colors: colors,
        onLabelShow: function (element, label, code) {
            const lowerCode = code.toLowerCase();
            if (pin[lowerCode]) {
                label.html(pin[lowerCode]);
                return;
            }

            const imageUrl = `${wps_js.global.assets_url}/images/flags/${lowerCode}.svg`;
            const countryName = label.text();

            fetch(imageUrl)
                .then(response => {
                    const flagImage = response.ok
                        ? `<img src='${imageUrl}' alt="${countryName}" title="${countryName}" class='log-tools wps-flag'/>`
                        : '';

                    label.html(`
                <div class='map-html-marker'>
                    <div class="map-country-header">
                        ${flagImage}
                        <span>${countryName}</span>
                    </div>
                    <div class="map-country-content">
                        <div>${wps_js._('visitors')}</div>
                        <div>0</div>
                    </div>
                </div>
            `);
                })
                .catch(error => {
                    console.error('Error fetching the image:', error);

                    label.html(`
                <div class='map-html-marker'>
                    <div class="map-country-header">
                        <span>${countryName}</span>
                    </div>
                    <div class="map-country-content">
                        <div>${wps_js._('visitors')}</div>
                        <div>0</div>
                    </div>
                </div>
            `);
                });
        },
    });
}
/**
 * Set Select2
 */
wps_js.select2 = function () {
    jQuery("select[data-type-show=select2]").select2();
}
const wpsSelect2 = jQuery('.wps-select2');
const wpsBody = jQuery('body');
const wpsDropdown = jQuery('.wps-dropdown');

if (wpsSelect2.length) {
    const wpsFilterPage = jQuery('.wps-filter-page');
    const wpsFilterVisitor = jQuery('.wps-filter-visitor');
    const dirValue = wpsBody.hasClass('rtl') ? 'rtl' : 'ltr';
    const dropdownParent = wpsFilterPage.length ? wpsFilterPage : wpsFilterVisitor;

    const initializeSelect2 = (parentElement, ajaxAction) => {
        wpsSelect2.select2({
            dropdownParent: parentElement,
            dir: dirValue,
            dropdownAutoWidth: true,
            dropdownCssClass: 'wps-select2-filter-dropdown',
            minimumInputLength: 1,
            ajax: {
                delay: 500,
                url: wps_js.global.ajax_url,
                dataType: 'json',
                data: function (params) {
                    const query = {
                        wps_nonce: wps_js.global.rest_api_nonce,
                        search: params.term, // The term to search for
                        action: ajaxAction,
                        paged: params.page || 1
                    };

                    if (wps_js.isset(wps_js.global, 'request_params')) {
                        const requestParams = wps_js.global.request_params;
                        if (requestParams.author_id) query.author_id = requestParams.author_id;
                        if (requestParams.page) query.page = requestParams.page;
                        if (requestParams.pt) query.post_type = requestParams.pt;
                        if (requestParams.pid) query.post_id = requestParams.pid;
                    }
                    return query;
                },
                processResults: function (data) {
                    if (data && Array.isArray(data.results)) {
                        return {
                            results: data.results.map(item => ({
                                id: item.id,
                                text: item.text
                            })),
                            pagination: {
                                more: false
                            }
                        };
                    } else {
                        console.error('Expected an array of results but got:', data);
                        return {results: []};
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX request error:', status, error);
                }
            }
        });
    };

    // Initial select2 setup without AJAX
    wpsSelect2.select2({
        dropdownParent: dropdownParent,
        dir: dirValue,
        dropdownAutoWidth: true,
        dropdownCssClass: 'wps-select2-filter-dropdown'
    });

    // Event listeners
    wpsSelect2.on('select2:open', () => wpsDropdown.addClass('active'));
    wpsSelect2.on('select2:close', () => wpsDropdown.removeClass('active'));
    wpsSelect2.on('change', function () {
        const selectedOption = jQuery(this).find('option:selected');
        const url = selectedOption.val();
        if (url) {
            window.location.href = url;
        }
    });

    // Conditional initialization based on filter page or visitor
    if (wpsFilterPage.length) {
        initializeSelect2(wpsFilterPage, 'wp_statistics_get_page_filter_items');
        wpsFilterPage.on('click', () => wpsSelect2.select2('open'));
    }


    if (wpsFilterVisitor.length) {
        initializeSelect2(wpsFilterVisitor, 'wp_statistics_search_visitors');
        wpsFilterVisitor.on('click', () => wpsSelect2.select2('open'));
    }
}
/**
 * Set Tooltip
 */
wps_js.tooltip = function () {
    jQuery('.wps-tooltip').each(function () {
        jQuery(this).tooltipster(wps_js.tooltip_options(this, {contentCloning: true}));
    });

    jQuery('body').on('mouseenter touchstart', '.wps-tooltip:not(.tooltipstered)', function () {
        $(this).tooltipster(wps_js.tooltip_options(this)).tooltipster('open');
    });
};

/**
 * Tooltip options. Info tooltips are at most 280px wide and stay inside the viewport on phones
 * (max width = screen - 16px, 8px from each edge); visitor-info pill tooltips keep their current
 * options (redesign pending).
 */
wps_js.TOOLTIP_MAX_WIDTH = 280;

wps_js.tooltip_options = function (el, extra) {
    var options = jQuery.extend({theme: 'tooltipster-shadow', contentAsHTML: false}, extra || {});

    if (el && el.closest && el.closest('.wps-visitor-info')) return options;

    var viewport = document.documentElement.clientWidth || window.innerWidth;
    options.functionPosition = wps_js.tooltip_keep_in_viewport;
    // One info tooltip at a time: opening one (tap on phones, hover elsewhere) closes the others.
    options.functionBefore = function (instance) {
        wps_js.tooltip_close_others(instance);
        if (extra && typeof extra.functionBefore === 'function') return extra.functionBefore.apply(this, arguments);
    };
    // Info tooltips wrap at 280px (one short line or two), never wider than the screen minus 16px.
    options.maxWidth = Math.min(wps_js.TOOLTIP_MAX_WIDTH, viewport - 16);

    return options;
};

/**
 * Close every open tooltipster tooltip except `keep`.
 */
wps_js.tooltip_close_others = function (keep) {
    if (!jQuery.tooltipster || typeof jQuery.tooltipster.instances !== 'function') return;
    jQuery.each(jQuery.tooltipster.instances(), function (i, instance) {
        if (instance !== keep && instance.status && instance.status().open) instance.close();
    });
};

// A scrolled tab strip (phones) would leave an open tab tooltip pointing off-screen: close it.
document.addEventListener('scroll', function (event) {
    var target = event.target;
    if (target && target.classList && target.classList.contains('wps-tabs')) wps_js.tooltip_close_others(null);
}, true);

/**
 * Keep a tooltip 8px inside the viewport on both sides (phones, LTR and RTL): tab and card
 * info icons near the screen edge used to put the box flush against or past the edge.
 * The arrow still points at the icon (tooltipster places it from the final position).
 * Visitor-info pill tooltips keep their current placement (they get their own design later).
 */
wps_js.tooltip_keep_in_viewport = function (instance, helper, position) {
    if (!position || !position.coord || !position.size) return position;
    if (helper && helper.origin && helper.origin.closest && helper.origin.closest('.wps-visitor-info')) return position;
    if (position.side !== 'top' && position.side !== 'bottom') return position;

    var margin = 8;
    var viewport = document.documentElement.clientWidth || window.innerWidth;
    var maxLeft = viewport - position.size.width - margin;

    if (position.coord.left > maxLeft) position.coord.left = maxLeft;
    if (position.coord.left < margin) position.coord.left = margin;

    return position;
};

/**
 * Execute Tooltip
 */
wps_js.tooltip();

/**
 * Redirect To Custom Url
 *
 * @param url
 */
wps_js.redirect = function (url) {
    window.location.replace(url);
};


/**
 * Share of a whole, as a percent with one decimal (0 when the total is 0).
 *
 * @param {number} count
 * @param {number} total
 * @returns {number}
 */
wps_js.share_percent = function (count, total) {
    count = Number(count) || 0;
    total = Number(total) || 0;
    if (total <= 0) return 0;
    return Math.round((count / total) * 1000) / 10;
};

/**
 * Format a percent for display: "16.5%", "40%" (no trailing .0).
 *
 * @param {number} value
 * @returns {string}
 */
wps_js.format_percent = function (value) {
    value = Number(value) || 0;
    const rounded = Math.round(value * 10) / 10;
    let text = rounded % 1 === 0 ? rounded.toFixed(0) : rounded.toFixed(1);
    return text + '%';
};

/**
 * Percent bar markup, the JS twin of views/components/objects/percent-bar.php.
 *
 * @param {number} value   Percent 0-100.
 * @param {Object} [opts]
 * @param {string} [opts.variant='share'] 'share' | 'rate' | 'ring'
 * @param {string} [opts.label]           Accessible label (screen readers only).
 * @returns {string} HTML
 */
wps_js.percent_bar = function (value, opts = {}) {
    const variant = ['share', 'rate', 'ring'].includes(opts.variant) ? opts.variant : 'share';
    value = Math.max(0, Math.min(100, Number(value) || 0));
    const text = wps_js.format_percent(value);
    const fill = value > 0 ? Math.max(value, 2) : 0;
    const label = opts.label ? `<span class="screen-reader-text">${wps_js.escapeHtml(opts.label)}</span>` : '';

    if (variant === 'ring') {
        const r = 7, c = 2 * Math.PI * r, d = (c * value / 100).toFixed(2);
        return `<span class="wps-percent wps-percent--ring"><svg class="wps-percent__ring" viewBox="0 0 18 18" width="18" height="18" aria-hidden="true" focusable="false"><circle class="wps-percent__ring-track" cx="9" cy="9" r="${r}"></circle><circle class="wps-percent__ring-fill" cx="9" cy="9" r="${r}" stroke-dasharray="${d} ${c.toFixed(2)}" transform="rotate(-90 9 9)"></circle></svg><span class="wps-percent__value">${text}</span>${label}</span>`;
    }

    return `<span class="wps-percent wps-percent--${variant}"><span class="wps-percent__value">${text}</span>${label}<span class="wps-percent__track" aria-hidden="true"><span class="wps-percent__fill" style="width:${fill}%"></span></span></span>`;
};

/**
 * Work out the percent for each row of a share list.
 *
 * Order of preference: explicit percentages, then opts.total (the real whole, e.g. all
 * visitors), then the sum of the rows. The sum is only right when the rows already cover
 * the whole (an "Other" row is included); a top-5 list without "Other" must pass a total.
 *
 * @param {number[]} data
 * @param {Array<number|string>} [percentages]
 * @param {number} [total]
 * @returns {number[]}
 */
wps_js.horizontal_bar_percentages = function (data, percentages = [], total = null) {
    const rows = Array.isArray(data) ? data.map(Number) : [];
    const sum = rows.reduce((a, b) => a + (Number.isFinite(b) ? b : 0), 0);
    const base = Number(total) > 0 ? Number(total) : sum;

    return rows.map((count, i) => {
        const given = Array.isArray(percentages) ? percentages[i] : undefined;
        if (given !== undefined && given !== null && given !== '' && Number.isFinite(Number(given))) {
            return Number(given);
        }
        return wps_js.share_percent(count, base);
    });
};

/**
 * Create Horizontal Bar Chart (share list: name, count and percent, thin track under each row).
 *
 * Signature kept for add-ons (window.renderHorizontalBar). Optional 6th argument:
 *  opts.total  number  the real whole when the rows are only the top N
 *
 * Old element classes (.wps-horizontal-bar__item, __label, __data, __background ...) are kept.
 */
wps_js.horizontal_bar = function (tag_id, labels, data, imageUrls, percentages = [], opts = {}) {

    // Get Element By ID
    let element = document.getElementById(tag_id);

    if (element) {
        let parent = element.parentNode;
        let nextSibling = element.nextSibling;
        parent.removeChild(element);
        if (!Array.isArray(data) && typeof data === 'object' && data !== null) {
            data = Object.values(data);
        } else if (!Array.isArray(data)) {
            data = [];
        }
        data = data.map(Number);
        labels = Array.isArray(labels) ? labels : Object.values(labels || {});

        const shares = wps_js.horizontal_bar_percentages(data, percentages || [], opts && opts.total);

        let blockDiv = document.createElement('div');
        blockDiv.classList.add('wps-horizontal-bar', 'wps-share-list');
        blockDiv.setAttribute('role', 'list');
        for (let i = 0; i < data.length; i++) {
            const percentage = shares[i];
            const percentageText = wps_js.format_percent(percentage);

            let itemDiv = document.createElement('div');
            itemDiv.classList.add('wps-horizontal-bar__item');
            itemDiv.setAttribute('role', 'listitem');
            let labelImageDiv = document.createElement('div');
            labelImageDiv.classList.add('wps-horizontal-bar__label-image-container');
            if (imageUrls && imageUrls[i] && imageUrls[i] !== 'undefined') {
                let img = document.createElement('img');
                img.src = imageUrls[i];
                img.alt = '';
                img.classList.add('wps-horizontal-bar__image');
                labelImageDiv.appendChild(img);
            }
            let labelDiv = document.createElement('div');
            labelDiv.innerHTML = wps_js.escapeHtml(labels[i]);
            labelDiv.setAttribute('title', labels[i]);
            labelDiv.classList.add('wps-horizontal-bar__label');
            labelImageDiv.appendChild(labelDiv);
            itemDiv.appendChild(labelImageDiv);
            let dataPercentDiv = document.createElement('div');
            dataPercentDiv.classList.add('wps-horizontal-bar__data-percent-container');
            let dataDiv = document.createElement('div');
            dataDiv.innerHTML = `<span title="${data[i]}">${wps_js.formatNumber(data[i])}</span><span>${percentageText}</span>`;
            dataDiv.classList.add('wps-horizontal-bar__data');
            dataPercentDiv.appendChild(dataDiv);
            itemDiv.appendChild(dataPercentDiv);
            let trackDiv = document.createElement('div');
            trackDiv.classList.add('wps-horizontal-bar__track');
            trackDiv.setAttribute('aria-hidden', 'true');
            let backgroundDiv = document.createElement('div');
            backgroundDiv.classList.add('wps-horizontal-bar__background');
            backgroundDiv.style.width = `${percentage > 0 ? Math.max(Math.min(percentage, 100), 1.5) : 0}%`;
            trackDiv.appendChild(backgroundDiv);
            itemDiv.appendChild(trackDiv);
            blockDiv.appendChild(itemDiv);
        }
        if (nextSibling) {
            parent.insertBefore(blockDiv, nextSibling);
        } else {
            parent.appendChild(blockDiv);
        }
    }
};

window.renderHorizontalBar = (id, label, data, icons, percentages = [], opts = {}) => {
    wps_js.horizontal_bar(id, label, data, icons, percentages, opts);
}

/**
 * Create Chart ID by Meta Box name
 *
 * @param meta_box
 */
wps_js.chart_id = function (meta_box) {
    return 'wp-statistics-' + meta_box + '-meta-box-chart';
};

/**
 * Enable/Disable WordPress Admin PostBox Ajax Request
 *
 * @param type
 */
wps_js.wordpress_postbox_ajax = function (type = 'enable') {
    let wordpress_postbox = jQuery('.postbox .hndle, .postbox .handlediv');
    if (type === 'enable') {
        wordpress_postbox.on('click', window.postboxes.handle_click);
    } else {
        wordpress_postbox.off('click', window.postboxes.handle_click);
    }
};

/**
 * Isset Property in Object
 *
 * @param obj
 */
wps_js.isset = function (obj) {
    let args = Array.prototype.slice.call(arguments, 1);

    for (let i = 0; i < args.length; i++) {
        if (!obj || !obj.hasOwnProperty(args[i])) {
            return false;
        }
        obj = obj[args[i]];
    }
    return true;
};

/**
 * Number Format
 *
 * @param number
 * @param decimals
 * @param dec_point
 * @param thousands_point
 * @returns {*}
 */
wps_js.number_format = function (number, decimals, dec_point, thousands_point) {
    if (number == null || !isFinite(number)) {
        throw new TypeError("number is not valid");
    }
    if (!decimals) {
        let len = number.toString().split('.').length;
        decimals = len > 1 ? len : 0;
    }
    if (!dec_point) {
        dec_point = '.';
    }
    if (!thousands_point) {
        thousands_point = ',';
    }
    number = parseFloat(number).toFixed(decimals);
    number = number.replace(".", dec_point);

    let splitNum = number.split(dec_point);
    splitNum[0] = splitNum[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_point);
    number = splitNum.join(dec_point);
    return number;
};

/**
 * Set Equal Bigger Div Height For WordPress PostBox
 *
 * @param Dom_1
 * @param Dom_2
 */
wps_js.set_equal_height = function (Dom_1, Dom_2) {
    let tbl_h = jQuery(Dom_1).height();
    let ch_h = jQuery(Dom_2).height();
    let ex = Dom_2;
    let val = tbl_h;
    if (tbl_h < ch_h) {
        ex = Dom_1;
        val = ch_h;
    }
    jQuery(ex).css('height', val + 'px');
};

/**
 * Create Half WordPress Post Box
 *
 * @param div_class
 * @param div_id
 * @returns {string}
 */
wps_js.Create_Half_PostBox = function (div_class, div_id) {
    return `<div class="postbox-container wps-postbox-half ${div_class}"><div class="metabox-holder"><div class="meta-box-sortables"> <div class="postbox" id="${div_id}"> <div class="inside"></div></div></div></div></div>`;
};

/**
 * Check IS IP
 *
 * @param str
 * @returns {boolean}
 */
wps_js.isIP = function (str) {
    const octet = '(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]?|0)';
    const regex = new RegExp(`^${octet}\\.${octet}\\.${octet}\\.${octet}$`);
    return regex.test(str);
};

/**
 * Get Link Params
 */
wps_js.getLinkParams = function (param, link = false) {
    if (!link) {
        link = window.location.href;
    }
    let v = link.match(new RegExp('(?:[\?\&]' + param + '=)([^&]+)'));
    return v ? v[1] : null;
};

/**
 * Sum array Of Item
 *
 * @param array
 * @returns {*}
 */
wps_js.sum = function (array) {
    return array.reduce(function (a, b) {
        return a + b;
    }, 0);
};

/**
 * Show empty data
 */
/**
 * Empty state markup, the JS twin of views/components/objects/empty-state.php.
 * Shows "No data found for this date range." (or "Data coming soon!" when the whole range
 * starts today or later) and the active dates. Legacy o-wrap--no-data class kept for old CSS.
 */
wps_js.no_results = function () {
    const params = (wps_js.global && wps_js.global.request_params) || {};
    const range = (wps_js.global && wps_js.global.user_date_range) || {};
    const from = params.from || range.from || '';
    const to = params.to || range.to || '';
    const message = (from && wps_js.isTodayOrFutureDate(from)) ? wps_js._('coming_soon') : (wps_js._('no_data_range') || wps_js._('no_result'));
    const label = wps_js.date_range_label ? wps_js.date_range_label(from, to) : '';
    const icon = '<svg class="wps-empty-state__icon" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>';
    return '<div class="wps-empty-state o-wrap--no-data" role="status">' + icon +
        '<p class="wps-empty-state__message">' + wps_js.escapeHtml(message) + '</p>' +
        (label ? '<p class="wps-empty-state__range">' + wps_js.escapeHtml(label) + '</p>' : '') +
        '</div>';
};
// Public for add-ons (wps_js lives inside admin.min.js): Data Plus cards use it for their empty state.
window.wpsNoResults = function () {
    return wps_js.no_results();
};

/**
 * "Oct 23 - Nov 21, 2026" for two Y-m-d dates, in the admin language. Empty string if invalid.
 */
wps_js.date_range_label = function (from, to) {
    const re = /^\d{4}-\d{2}-\d{2}$/;
    if (!re.test(from || '') || !re.test(to || '')) return '';
    const locale = (document.documentElement.lang || 'en-US');
    const d = (s) => { const [y, m, dd] = s.split('-').map(Number); return new Date(Date.UTC(y, m - 1, dd)); };
    try {
        const md = new Intl.DateTimeFormat(locale, {month: 'short', day: 'numeric', timeZone: 'UTC'});
        const mdy = new Intl.DateTimeFormat(locale, {month: 'short', day: 'numeric', year: 'numeric', timeZone: 'UTC'});
        if (from === to) return mdy.format(d(to));
        const sameYear = from.slice(0, 4) === to.slice(0, 4);
        return (sameYear ? md : mdy).format(d(from)) + ' - ' + mdy.format(d(to));
    } catch (e) {
        return from + ' - ' + to;
    }
};

wps_js.isTodayOrFutureDate = function (dateString) {
    if (!dateString) return false;
    const today = new Date();
    const target = new Date(dateString);
    return target >= new Date(today.getFullYear(), today.getMonth(), today.getDate());
}

wps_js.placeholder_txt = function (noDataMessage) {
    const randomIndex = Math.floor(Math.random() * 4) + 1;
    const src = `${wps_js.global.assets_url}/images/no-data/vector-${randomIndex}.svg`;
    return `
        <div class="wps--no-data__container" aria-live="polite">
            <img class="wps--no-data__image" src="${src}" role="presentation" alt="Illustration for ${noDataMessage}">
            <p class="${src ? 'wps--no-data__text' : 'o-wrap o-wrap--no-data wps-center'}">
                ${noDataMessage}
            </p>
        </div>
    `;
};

wps_js.allValuesZero = function (data) {
    if (!data || data.length === 0) return true;
    return data.every(value => value === 0 || value === null || value === undefined || value === '0');
};

wps_js.allDatasetsZero = function (currentDatasets, previousDatasets = null) {
    const currentAllZero = !currentDatasets || currentDatasets.length === 0 ||
        currentDatasets.every(dataset => wps_js.allValuesZero(dataset.data));

    if (!previousDatasets || previousDatasets.length === 0) {
        return currentAllZero;
    }
    const previousAllZero = previousDatasets.every(dataset => wps_js.allValuesZero(dataset.data));
    return currentAllZero && previousAllZero;
};

// Head filters drop down
jQuery(document).ready(function () {
    var dropdowns = document.querySelectorAll(".wps-head-filters__item");

    dropdowns.forEach(function (dropdown) {
        dropdown.classList.remove('loading');
        dropdown.addEventListener("click", function (event) {
            var dropdownContent = dropdown.querySelector(".dropdown-content");
            if (dropdownContent) {
                if (!event.target.classList.contains('disabled')) {
                    dropdownContent.classList.toggle("show");
                }
            }
        });
    });

    var searchInputs = jQuery(".wps-search-dropdown");


    searchInputs.on("click", function (event) {
        event.stopPropagation();
    });

    searchInputs.on("input", function () {
        let filter = jQuery(this).val().toLowerCase();
        let items = jQuery(this).parent().find(".dropdown-item");
        items.each(function () {
            let text = jQuery(this).text() || jQuery(this).innerText;
            if (text.toLowerCase().indexOf(filter) > -1) {
                jQuery(this).show();
            } else {
                jQuery(this).hide();
            }
        });
    });

    window.addEventListener("click", function (event) {
        dropdowns.forEach(function (dropdown) {
            let dropdownContent = dropdown.querySelector(".dropdown-content")
            if (dropdownContent && !dropdown.contains(event.target)) {
                dropdownContent.classList.remove("show");
            }
        });
    });
});

if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "help-center") {
    const body = document.body;
    const targetClass = 'statistics_page_wps_help-center_page';

    if (!body.classList.contains(targetClass)) {
        body.classList.add(targetClass);
    }
}
jQuery(document).ready(function () {
    const targetElement = document.querySelector('.wp-header-end');
    const noticeElement = document.querySelector('.notice.notice-warning.update-nag');
    // Check if both targetElement and noticeElement exist
    if (targetElement && noticeElement) {
        // Move the notice element after the target element
        targetElement.parentNode.insertBefore(noticeElement, targetElement.nextSibling);
    }

    document.querySelectorAll('.wp-has-submenu.menu-top.toplevel_page_wps_overview_page li a')
        .forEach(link => {
            if (link.querySelector('.wps-text-warning, .wps-addons-menu')) {
                link.classList.add('addon-menu');
            }
        });
});

window.renderFormatNum = function (data) {
    return wps_js.formatNumber(data)
}
window.renderWPSSelect2 = function (class_name) {
    jQuery("select[data-type-show=select2]").select2({
        dropdownCssClass: class_name,
    });
}