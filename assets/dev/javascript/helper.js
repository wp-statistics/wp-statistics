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
    jQuery('.wps-tooltip').tooltipster({
        theme: 'tooltipster-shadow',
        contentCloning: true
    });

    jQuery('body').on('mouseenter touchstart', '.wps-tooltip:not(.tooltipstered)', function () {
        $(this).tooltipster({
            theme: 'tooltipster-shadow'
        }).tooltipster('open');
    });
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
 * Create Horizontal Bar Chart
 */
wps_js.horizontal_bar = function (tag_id, labels, data, imageUrls) {

    // Get Element By ID
    let element = document.getElementById(tag_id);

    if (element) {
        let parent = element.parentNode;
        let nextSibling = element.nextSibling;
        parent.removeChild(element);
        let total = data.reduce((sum, data) => sum + data, 0);
        let blockDiv = document.createElement('div');
        blockDiv.classList.add('wps-horizontal-bar');
        for (let i = 0; i < data.length; i++) {
            // Calculate percentage as a float with two decimal places
            let percentage = total ? ((data[i] / total) * 100) : 0;
            // Format the percentage
            let percentageText = percentage % 1 === 0 ? percentage.toFixed(0) : percentage.toFixed(1);

            // If percentage ends with .0, remove it
            if (percentageText.endsWith('.0')) {
                percentageText = percentageText.slice(0, -2);
            }
            let itemDiv = document.createElement('div');
            itemDiv.classList.add('wps-horizontal-bar__item');
            let labelImageDiv = document.createElement('div');
            labelImageDiv.classList.add('wps-horizontal-bar__label-image-container');
            if (imageUrls && imageUrls[i] && imageUrls[i] !== 'undefined') {
                let img = document.createElement('img');
                img.src = imageUrls[i];
                img.alt = labels[i];
                img.classList.add('wps-horizontal-bar__image');
                labelImageDiv.appendChild(img);
            }
            let labelDiv = document.createElement('div');
            labelDiv.innerHTML = labels[i];
            labelDiv.setAttribute('title', labels[i]);
            labelDiv.classList.add('wps-horizontal-bar__label');
            labelImageDiv.appendChild(labelDiv);
            itemDiv.appendChild(labelImageDiv);
            let dataPercentDiv = document.createElement('div');
            dataPercentDiv.classList.add('wps-horizontal-bar__data-percent-container');
            let dataDiv = document.createElement('div');
            dataDiv.innerHTML = `<span>${wps_js.formatNumber(data[i])}</span><span>${percentageText}%</span>`;
            dataDiv.classList.add('wps-horizontal-bar__data');
            dataPercentDiv.appendChild(dataDiv);
            itemDiv.appendChild(dataPercentDiv);
            let backgroundDiv = document.createElement('div');
            backgroundDiv.classList.add('wps-horizontal-bar__background');
            backgroundDiv.style.width = `${percentage}%`; // Set width according to percentage
            itemDiv.appendChild(backgroundDiv);
            blockDiv.appendChild(itemDiv);
        }
        if (nextSibling) {
            parent.insertBefore(blockDiv, nextSibling);
        } else {
            parent.appendChild(blockDiv);
        }
    }
};

window.renderHorizontalBar = (id, label, data, icons) => {
    wps_js.horizontal_bar(id, label, data, icons);
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
wps_js.no_results = function () {
    return '<div class="o-wrap o-wrap--no-data wps-center">' + wps_js._('no_result') + '</div>';
};


// Head filters drop down
jQuery(document).ready(function () {
    var dropdowns = document.querySelectorAll(".wps-head-filters__item");

    dropdowns.forEach(function (dropdown) {
        dropdown.classList.remove('loading');
        dropdown.addEventListener("click", function (event) {
            var dropdownContent = dropdown.querySelector(".dropdown-content");
            if (dropdownContent) {
                if(!event.target.classList.contains('disabled')){
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

jQuery(document).ready(function () {
    const targetElement = document.querySelector('.wp-header-end');
    const noticeElement = document.querySelector('.notice.notice-warning.update-nag');
    // Check if both targetElement and noticeElement exist
    if (targetElement && noticeElement) {
        // Move the notice element after the target element
        targetElement.parentNode.insertBefore(noticeElement, targetElement.nextSibling);
    }

});

window.renderFormatNum = function (data) {
    return wps_js.formatNumber(data)
}