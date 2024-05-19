/**
 * Check Exist Dom
 */
wps_js.exist_tag = function (tag) {
    return (jQuery(tag).length);
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

/**
 * Set Select2
 */
wps_js.select2 = function () {
    jQuery("select[data-type-show=select2]").select2();
}

const wpsSelect2 = jQuery('.wps-select2');
const wpsFilterPage = jQuery('.wps-filter-page');
const wpsBody = jQuery('body');
const wpsDropdown = jQuery('.wps-dropdown');

if (wpsSelect2.length && wpsFilterPage.length) {
    var dirValue = wpsBody.hasClass('rtl') ? 'rtl' : 'ltr';

    wpsSelect2.select2({
        dropdownParent: $('.wps-filter-page'),
        dir: dirValue,
        dropdownAutoWidth: true,
        dropdownCssClass: 'wps-select2-filter-dropdown'
    });

    wpsFilterPage.on('click', function () {
        wpsSelect2.select2('open');
    });

    wpsSelect2.on('select2:open', function () {
        wpsDropdown.addClass('active');
    });

    wpsSelect2.on('select2:close', function () {
        wpsDropdown.removeClass('active');
    });

    wpsSelect2.on('change', function () {
        var selectedOption = jQuery(this).find('option:selected');
        var url = selectedOption.val();

        if (url) {
            window.location.href = url;
        }
    });
}


/**
 * Set Tooltip
 */
wps_js.tooltip = function () {
    jQuery('.wps-tooltip').tooltipster({
        theme: 'tooltipster-flat'
    });
    jQuery('body').on('mouseenter touchstart', '.wps-tooltip:not(.tooltipstered)', function () {
        $(this).tooltipster({
            theme: 'tooltipster-flat'
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
 * Create Line Chart JS
 */
wps_js.line_chart = function (tag_id, title, label, data, newOptions) {

    // Get Element By ID
    let ctx = document.getElementById(tag_id).getContext('2d');

    // Check is RTL Mode
    if (wps_js.is_active('rtl')) {
        Chart.defaults.global = {
            defaultFontFamily: "Tahoma"
        }
    }

    const defaultOptions = {
        type: 'line',
        data: {
            labels: label,
            datasets: data
        },
        options: {
            responsive: true,
            legend: {
                position: 'bottom',
            },
            animation: {
                duration: 1500,
            },
            title: {
                display: true,
                text: title
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                y: {
                    ticks: {
                        stepSize: 1,
                    }
                },
            },
            plugins: {
                zoom: {
                    pan: {
                        enabled: true,
                        mode: 'xy',
                    },
                    zoom: {
                        wheel: {
                            enabled: true,
                            speed: 0.05,
                            modifierKey: 'ctrl'
                        },
                        pinch: {
                            enabled: true
                        },
                        mode: 'xy',
                    }
                }
            }
        }
    };

    const options = Object.assign({}, defaultOptions, newOptions);

    // Create Chart
    new Chart(ctx, options);
};

/**
 * Create pie Chart JS
 */
wps_js.pie_chart = function (tag_id, label, data, label_callback = false, tooltip_callback = false) {

    // Get Element By ID
    let ctx = document.getElementById(tag_id).getContext('2d');

    // Check is RTL Mode
    if (wps_js.is_active('rtl')) {
        Chart.defaults.global = {
            defaultFontFamily: "Tahoma"
        }
    }
    // Set Default Label Callback
    if (label_callback === false) {
         label_callback = function (tooltipItem) {
            return tooltipItem.formattedValue
        };
    }

    // Set Default tooltip title Callback
    if( tooltip_callback === false){
        tooltip_callback = function (tooltipItem ) {
            return tooltipItem.label;
        }
    }

    // Create Chart
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: label,
            datasets: data
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: function (chart) {
                        if (chart.chart.width > 400) {
                            return 'left';
                        }
                        return 'top';
                    }
                },
                tooltip: {
                    enable: true,
                    callbacks: {
                        label: label_callback,
                        title: tooltip_callback
                    }
                }
            },
            animation: {
                duration: 1500,
            },
        },
        plugins: [{
            afterDraw: function (chart) {
                if (chart.data.datasets[0].data.every(x => x == 0) === true) {
                    let ctx = chart.ctx;
                    let width = chart.width;
                    let height = chart.height;
                    chart.clear();
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = "14px normal 'Tahoma'";
                    ctx.fillText(wps_js._('no_data'), width / 2, height / 2);
                    ctx.restore();
                }
            }
        }]
    });
};

/**
 * Create Chart ID by Meta Box name
 *
 * @param meta_box
 */
wps_js.chart_id = function (meta_box) {
    return 'wp-statistics-' + meta_box + '-meta-box-chart';
};

/**
 * Generate Flat Random Color
 */
wps_js.random_color = function (i = false) {
    let colors = [
        [243, 156, 18, "#f39c12"],
        [52, 152, 219, "#3498db"],
        [192, 57, 43, "#c0392b"],
        [155, 89, 182, "#9b59b6"],
        [39, 174, 96, "#27ae60"],
        [230, 126, 34, "#e67e22"],
        [142, 68, 173, "#8e44ad"],
        [46, 204, 113, "#2ecc71"],
        [41, 128, 185, "#2980b9"],
        [22, 160, 133, "#16a085"],
        [211, 84, 0, "#d35400"],
        [44, 62, 80, "#2c3e50"],
        [241, 196, 15, "#f1c40f"],
        [231, 76, 60, "#e74c3c"],
        [26, 188, 156, "#1abc9c"],
        [46, 204, 113, "#2ecc71"],
        [52, 152, 219, "#3498db"],
        [155, 89, 182, "#9b59b6"],
        [52, 73, 94, "#34495e"],
        [22, 160, 133, "#16a085"],
        [39, 174, 96, "#27ae60"],
        [44, 62, 80, "#2c3e50"],
        [241, 196, 15, "#f1c40f"],
        [230, 126, 34, "#e67e22"],
        [231, 76, 60, "#e74c3c"],
        [236, 240, 241, "#9b9e9f"],
        [149, 165, 166, "#a65d20"]
    ];
    return colors[(i === false ? Math.floor(Math.random() * colors.length) : i)];
};

/**
 * Show Domain Icon
 */
wps_js.site_icon = function (domain) {
    return `<img src="https://www.google.com/s2/favicons?domain=${domain}" width="18" height="18" alt="${domain}" style="vertical-align: middle;" />`;
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
 * FeedbackBird position
 * */
function moveFeedbackBird() {
    let windowWidth = window.outerWidth || document.documentElement.clientWidth;
    const feedbackBird = document.getElementById('feedback-bird-app');
    const feedbackBirdTitle = document.querySelector('.c-fbb-widget__header__title');
    const license = document.querySelector('.wps-mobileMenuContent .wps-bundle');
    const support = document.querySelector('.wps-adminHeader__side');
    if (feedbackBird && (document.body.classList.contains('wps_page'))) {
        if (windowWidth <= 1030) {
            const cutDiv = feedbackBird.parentNode.removeChild(feedbackBird);
            license.parentNode.insertBefore(cutDiv, license);
        } else {
            const cutDiv = feedbackBird.parentNode.removeChild(feedbackBird);
            support.appendChild(cutDiv);
        }
        feedbackBird.style.display = 'block';
        feedbackBird.setAttribute('title', feedbackBirdTitle.innerHTML);
    }
}

// Head filters drop down
jQuery(document).ready(function () {
    var dropdowns = document.querySelectorAll(".wps-head-filters__item");

    dropdowns.forEach(function (dropdown) {
        dropdown.classList.remove('loading');
        dropdown.addEventListener("click", function (event) {
            var dropdownContent = dropdown.querySelector(".dropdown-content");
            if (dropdownContent) {
                dropdownContent.classList.toggle("show");
            }
        });
    });

    window.addEventListener("click", function (event) {
        dropdowns.forEach(function (dropdown) {
            var dropdownContent = dropdown.querySelector(".dropdown-content");
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

    jQuery(document).on('click', '.wps-privacy-list__item .wps-privacy-list__title', (e) => {
        const title = jQuery(e.currentTarget);
        const content = title.siblings('.wps-privacy-list__content');

        // If the action button is clicked, don't expand the item
        if (jQuery(e.target).is('.wps-privacy-list__button')) {
            return;
        }

        title.toggleClass('open');

        if (content.hasClass('show')) {
            content.removeClass('show');
        } else {
            content.addClass('show');
        }
    });
});


/**
 * FeedbackBird position
 * */
function moveFeedbackBird() {
    let windowWidth = window.outerWidth || document.documentElement.clientWidth;
    const feedbackBird = document.getElementById('feedback-bird-app');
    const feedbackBirdTitle = document.querySelector('.c-fbb-widget__header__title');
    const license = document.querySelector('.wps-mobileMenuContent .wps-bundle');
    const support = document.querySelector('.wps-adminHeader__side');
    if (feedbackBird && (document.body.classList.contains('wps_page'))) {
        if (windowWidth <= 1030) {
            const cutDiv = feedbackBird.parentNode.removeChild(feedbackBird);
            license.parentNode.insertBefore(cutDiv, license);
        } else {
            const cutDiv = feedbackBird.parentNode.removeChild(feedbackBird);
            support.appendChild(cutDiv);
        }
        feedbackBird.style.display = 'block';
        feedbackBird.setAttribute('title', feedbackBirdTitle.innerHTML);
    }
}

window.onload = moveFeedbackBird;
window.addEventListener('resize', moveFeedbackBird);

jQuery(document).ready(function () {
    const targetElement = document.querySelector('.wp-header-end');
    const noticeElement = document.querySelector('.notice.notice-warning.update-nag');
    // Check if both targetElement and noticeElement exist
    if (targetElement && noticeElement) {
        // Move the notice element after the target element
        targetElement.parentNode.insertBefore(noticeElement, targetElement.nextSibling);
    }
});