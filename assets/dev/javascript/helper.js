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
const wpsBody = jQuery('body');
const wpsDropdown = jQuery('.wps-dropdown');

if (wpsSelect2.length) {
    const wpsFilterPage = jQuery('.wps-filter-page');
    const wpsFilterVisitor = jQuery('.wps-filter-visitor');
    var dirValue = wpsBody.hasClass('rtl') ? 'rtl' : 'ltr';

    wpsSelect2.select2({
        dropdownParent: wpsFilterPage.length ? $('.wps-filter-page') : $('.wps-filter-visitor'),
        dir: dirValue,
        dropdownAutoWidth: true,
        dropdownCssClass: 'wps-select2-filter-dropdown'
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

    if (wpsFilterPage.length) {
        wpsSelect2.select2({
            dropdownParent: $('.wps-filter-page'),
            dir: dirValue,
            dropdownAutoWidth: true,
            dropdownCssClass: 'wps-select2-filter-dropdown',
            ajax: {
                delay: 500,
                url: wps_js.global.ajax_url,
                dataType: 'json',
                data: function (params) {
                    const query = {
                        wps_nonce: wps_js.global.rest_api_nonce,
                        search: params.term,
                        action: 'wp_statistics_get_page_filter_items',
                        paged: params.page || 1
                    }

                    if (wps_js.isset(wps_js.global, 'request_params', 'author_id')) {
                        query.author_id = wps_js.global.request_params.author_id;
                    }

                    if (wps_js.isset(wps_js.global, 'request_params', 'page')) {
                        query.page = wps_js.global.request_params.page;
                    }

                    if (wps_js.isset(wps_js.global, 'request_params', 'pt')) {
                        query.post_type = wps_js.global.request_params.pt;
                    }

                    if (wps_js.isset(wps_js.global, 'request_params', 'pid')) {
                        query.post_id = wps_js.global.request_params.pid;
                    }
                    return query;
                }
            }
        });

        wpsFilterPage.on('click', function () {
            wpsSelect2.select2('open');
        });
    }

    if (wpsFilterVisitor.length) {
        wpsSelect2.select2({
            dropdownParent: $('.wps-filter-visitor'),
            dir: dirValue,
            dropdownAutoWidth: true,
            dropdownCssClass: 'wps-select2-filter-dropdown',
            ajax: {
                delay: 500,
                url: wps_js.global.ajax_url,
                dataType: 'json',
                data: function (params) {
                    const query = {
                        wps_nonce: wps_js.global.rest_api_nonce,
                        search: params.term,
                        action: 'wp_statistics_search_visitors',
                        paged: params.page || 1
                    }

                    return query;
                }
            }
        });

        wpsFilterVisitor.on('click', function () {
            wpsSelect2.select2('open');
        });
    }
}


/**
 * Set Tooltip
 */
wps_js.tooltip = function () {
    jQuery('.wps-tooltip').tooltipster({
        theme: 'tooltipster-flat',
        contentCloning: true
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
    if (tooltip_callback === false) {
        tooltip_callback = function (tooltipItem) {
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
 * Create Horizontal Bar Chart
 */
wps_js.horizontal_bar = function (tag_id, labels, data, imageUrls) {

    // Get Element By ID
    let element = document.getElementById(tag_id);

    if (element) {
        let parent = element.parentNode;
        let nextSibling = element.nextSibling;
        let value;
        if (data[0]) {
            value = data[0].data;
        } else {
            value = data.data;
        }
        parent.removeChild(element);
        let total = value.reduce((sum, value) => sum + value, 0);
        let blockDiv = document.createElement('div');
        blockDiv.classList.add('wps-horizontal-bar');
        for (let i = 0; i < value.length; i++) {
            // Calculate percentage as a float with two decimal places
            let percentage = total ? ((value[i] / total) * 100) : 0;
            // Format the percentage
            let percentageText = percentage % 1 === 0 ? percentage.toFixed(0) : percentage.toFixed(2);
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
            dataDiv.innerHTML = `<span>${value[i].toLocaleString()}</span><span>${percentageText}%</span>`;
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
 * Show empty data
 */
wps_js.no_results = function () {
    return '<div class="o-wrap o-wrap--no-data wps-center">' + wps_js._('no_result') + '</div>';
};

wps_js.hex_to_rgba = function (hex, opacity) {
    hex = hex.replace('#', '');
    let hex_to_rgba_r = parseInt(hex.substring(0, 2), 16);
    let hex_to_rgba_g = parseInt(hex.substring(2, 4), 16);
    let hex_to_rgba_b = parseInt(hex.substring(4, 6), 16);
    return `rgba(${hex_to_rgba_r}, ${hex_to_rgba_g}, ${hex_to_rgba_b}, ${opacity})`;
}

wps_js.new_line_chart = function (data, tag_id, newOptions) {
    // Define the colors
    const colors = ['#3288D7', '#7362BF', '#27A765', '#8AC3D0'];
    // Get Element By ID
    let ctx_line = document.getElementById(tag_id).getContext('2d');

    const datasets = [];
    // Dynamically create datasets
    Object.keys(data.data).forEach((key, index) => {
        if (key !== 'labels') {
            // Main dataset
            datasets.push({
                type: 'line',
                label: key,
                data: data.data[key],
                borderColor: colors[index - 1],
                backgroundColor: colors[index - 1],
                fill: false,
                yAxisID: 'y',
                borderWidth: 2,
                pointRadius: 0,
                pointBorderColor: 'transparent',
                pointBackgroundColor: colors[index - 1],
                pointBorderWidth: 2,
                hoverPointRadius: 6,
                hoverPointBorderColor: '#fff',
                hoverPointBackgroundColor: colors[index - 1],
                hoverPointBorderWidth: 4
            });

            // Previous data dataset
            if (data.previousData[key]) {
                datasets.push({
                    type: 'line',
                    label: `${key} (Previous)`,
                    data: data.previousData[key],
                    borderColor: wps_js.hex_to_rgba(colors[index - 1], 0.4),
                    hoverBorderColor: colors[index - 1],
                    backgroundColor: colors[index - 1],
                    fill: false,
                    yAxisID: 'y',
                    borderWidth: 1,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    pointBorderColor: 'transparent',
                    pointBackgroundColor: colors[index - 1],
                    pointBorderWidth: 2,
                    hoverPointRadius: 6,
                    hoverPointBorderColor: '#fff',
                    hoverPointBackgroundColor: colors[index - 1],
                    hoverPointBorderWidth: 4
                });
            }
        }
    });

    const getOrCreateTooltip = (chart) => {
        let tooltipEl = chart.canvas.parentNode.querySelector('div');

        if (!tooltipEl) {
            tooltipEl = document.createElement('div');
            tooltipEl.classList.add('wps-chart-tooltip');
            tooltipEl.style.opacity = 1;
            tooltipEl.style.pointerEvents = 'none';
            tooltipEl.style.position = 'absolute';
            tooltipEl.style.transition = 'all .1s ease';
            const table = document.createElement('table');
            table.style.margin = '0px';
            tooltipEl.appendChild(table);
            chart.canvas.parentNode.appendChild(tooltipEl);
        }
        return tooltipEl;
    };

    const externalTooltipHandler = (context) => {
        const {chart, tooltip} = context;
        const tooltipEl = getOrCreateTooltip(chart);

        if (tooltip.opacity === 0) {
            tooltipEl.style.opacity = 0;
            return;
        }

        if (tooltip.body) {
            const titleLines = tooltip.title || [];
            const dataIndex = tooltip.dataPoints[0].dataIndex;
            const datasets = chart.data.datasets;

            let innerHtml = `<div>`;
            // Title
            titleLines.forEach(title => {
                innerHtml += `<div class="chart-title">${title}</div>`;
            });

            // Iterate over each dataset to create the tooltip content
            datasets.forEach((dataset, index) => {
                const value = dataset.data[dataIndex];
                const isPrevious = dataset.label.includes('(Previous)');
                const previousDataset = datasets.find(ds => ds.label === `${dataset.label} (Previous)`);
                const previousValue = data.previousData[dataset.label.replace(' (Previous)', '')]?.[dataIndex];

                if (isPrevious) {
                    dataset.borderColor = colors[index - 1];
                }

                if (!isPrevious) {
                    innerHtml += `
                <div class="current-data">
                    <div>
                        <span class="current-data__color" style="background-color: ${dataset.borderColor};"></span>
                        ${dataset.label}
                    </div>
                    <span class="current-data__value">${value.toLocaleString()}</span>
                </div>`;
                }

                if (previousValue !== undefined && previousValue !== '' && !isPrevious) {
                    const previousLabel = data.previousData.labels[dataIndex];
                    innerHtml += `
                <div class="previous-data">
                    <div>
                        <span class="previous-data__colors">
                            <span class="previous-data__color" style="background-color: ${dataset.borderColor};"></span>
                            <span class="previous-data__color" style="background-color: ${dataset.borderColor};"></span>
                        </span>
                        ${previousLabel}
                    </div>
                    <span class="previous-data__value"> ${previousValue.toLocaleString()}</span>
                </div>`;
                }
            });

            innerHtml += `</div>`;

            tooltipEl.innerHTML = innerHtml;
            const {offsetLeft: chartLeft, offsetTop: chartTop, clientWidth: chartWidth, clientHeight: chartHeight} = chart.canvas;
            const {caretX, caretY} = tooltip;

            // Calculate tooltip position
            const tooltipWidth = tooltipEl.offsetWidth;
            const tooltipHeight = tooltipEl.offsetHeight;

            const margin = 16;
            // Default tooltip position to the right of the point
            let tooltipX = chartLeft + caretX + margin;
            let tooltipY = chartTop + caretY - tooltipHeight / 2;

            // Check if tooltip exceeds right boundary
            if (tooltipX + tooltipWidth + margin > chartLeft + chartWidth) {
                // Not enough space on the right, position to the left
                tooltipX = chartLeft + caretX - tooltipWidth - margin;
            }

            // Ensure tooltip does not overflow horizontally
            if (tooltipX < chartLeft + margin) {
                tooltipX = chartLeft + margin;
            }

            // Ensure tooltip does not overflow vertically
            if (tooltipY < chartTop + margin) {
                tooltipY = chartTop + margin;
            }
            if (tooltipY + tooltipHeight + margin > chartTop + chartHeight) {
                tooltipY = chartTop + chartHeight - tooltipHeight - margin;
            }
            tooltipEl.style.opacity = 1;
            tooltipEl.style.left = tooltipX + 'px';
            tooltipEl.style.top = tooltipY + 'px';
        }
    };

    // Custom plugin definition
    const drawVerticalLinePlugin = {
        id: 'drawVerticalLine',
        beforeDatasetDraw(chart) {
            const {ctx, scales: {x, y}, tooltip, chartArea: {top, bottom}} = chart;
            if (tooltip._active[0]) {
                const xValue = tooltip._active[0].element.x;
                ctx.beginPath();
                ctx.strokeStyle = '#A9AAAE';
                ctx.lineWidth = 1;
                ctx.setLineDash([6, 6]);
                ctx.moveTo(xValue, top);
                ctx.lineTo(xValue, bottom);
                ctx.stroke();
                ctx.setLineDash([]);
            }
        }
    };


    // Default options
    const defaultOptions = {
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            legend: false,
            tooltip: {
                enabled: false,
                external: externalTooltipHandler,
                callbacks: {
                    title: (tooltipItems) => tooltipItems[0].label,
                    label: (tooltipItem) => tooltipItem.formattedValue
                }
            },
            drawVerticalLine: drawVerticalLinePlugin
        },
        scales: {
            x: {
                offset: data.data.labels.length <= 1,
                min: 0,
                grid: {
                    display: false,
                    drawBorder: false,
                    tickLength: 0,
                    drawTicks: false
                },
                border: {
                    color: 'transparent',
                    width: 0
                },
                ticks: {
                    align: 'inner',
                    maxTicksLimit: 9,
                    fontColor: '#898A8E',
                    fontStyle: 'italic',
                    fontWeight: 'lighter ',
                    fontSize: 13,
                    padding: 8,
                    fontFamily: '"Roboto",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif',
                    lineHeight: 15
                }
            },
            y: {
                min: 0,
                ticks: {
                    maxTicksLimit: 7,
                    fontColor: '#898A8E',
                    fontSize: 13,
                    fontStyle: 'italic',
                    fontFamily: '"Roboto",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif',
                    fontWeight: 'lighter ',
                    padding: 8,
                    lineHeight: 15
                },
                border: {
                    color: 'transparent',
                    width: 0
                },
                type: 'linear',
                position: 'right',
                grid: {
                    display: true,
                    tickMarkLength: 0,
                    drawBorder: false,
                },
                gridLines: {
                    drawTicks: false
                },
                title: {
                    display: false,
                }
            }
        },
    };
    // Merge default options with user options
    const options = Object.assign({}, defaultOptions, newOptions);
    const lineChart = new Chart(ctx_line, {
        type: 'line',
        data: {
            labels: data.data.labels,
            datasets: datasets
        },
        options: options,
    });

    const updateLegend = function () {
        const legendContainer = document.querySelector('.wps-postbox-chart--items');
        if (legendContainer) {
            legendContainer.innerHTML = '';
            datasets.forEach((dataset, index) => {
                const isPrevious = dataset.label.includes('(Previous)');
                if (!isPrevious) {
                    const currentData = dataset.data.reduce((a, b) => a + b, 0);
                    const previousData = data.previousData[dataset.label]?.reduce((a, b) => a + b, 0) || 'N/A';
                    const legendItem = document.createElement('div');
                    legendItem.className = 'wps-postbox-chart--item';
                    legendItem.innerHTML = `
                        <span>
                             ${dataset.label}
                        </span>
                        <div>
                            <div class="current-data"><span class="wps-postbox-chart--item--color" style="background: ${dataset.borderColor}"></span>${currentData.toLocaleString()}</div>
                            <div class="previous-data" >
                                <span>
                                    <span class="wps-postbox-chart--item--color" style="background: ${dataset.borderColor}"></span>
                                    <span class="wps-postbox-chart--item--color" style="background: ${dataset.borderColor}"></span>
                                </span>
                                ${previousData.toLocaleString()}
                            </div>
                        </div>
                    `;
                    // Add click event to toggle visibility of the current dataset only
                    const currentDataDiv = legendItem.querySelector('.current-data');
                    currentDataDiv.addEventListener('click', function () {
                        const metaMain = lineChart.getDatasetMeta(index);
                        metaMain.hidden = !metaMain.hidden;
                        currentDataDiv.classList.toggle('wps-line-through');
                        lineChart.update();
                    });

                    // Add click event to toggle visibility of the previous dataset
                    const previousDataDiv = legendItem.querySelector('.previous-data');
                    previousDataDiv.addEventListener('click', function () {
                        const metaPrevious = lineChart.getDatasetMeta(index + 1);
                        if (metaPrevious && metaPrevious.label.includes('(Previous)')) {
                            previousDataDiv.classList.toggle('wps-line-through');
                            metaPrevious.hidden = !metaPrevious.hidden;
                        }
                        lineChart.update();
                    });

                    legendContainer.appendChild(legendItem);
                }
            });
        }
    };
    updateLegend();
};

wps_js.performance_chart = function (data, tag_id , is_single_content = false) {
    const legendHandel = (chart) => {
        document.querySelectorAll('.js-wps-performance-chart__item').forEach((legendItem, index) => {
            legendItem.addEventListener('click', () => {
                const dataset = chart.data.datasets[index];
                dataset.hidden = !dataset.hidden;
                chart.update();
                legendItem.classList.toggle('hidden', dataset.hidden);
            });
        });
    }
    const colors = ['#3288D7', '#7362BF', '#8AC3D0'];
    let ctx_performance = document.getElementById(tag_id).getContext('2d');
    let datasets =  [
        {
            type: 'line',
            label: wps_js._('visits'),
            data: data.views,
            borderColor: wps_js.hex_to_rgba(colors[1], 0.8),
            pointStyle: 'circle',
            yAxisID: 'y',
            borderWidth: 2,
            pointRadius: 0,
            pointBorderColor: 'transparent',
            pointBackgroundColor: colors[1],
            pointBorderWidth: 2,
            hoverPointRadius: 6,
            hoverPointBorderColor: '#fff',
            hoverPointBackgroundColor: colors[1],
            hoverPointBorderWidth: 4
        },
        {
            type: 'line',
            label: wps_js._('visitors'),
            data: data.visitors,
            borderColor: wps_js.hex_to_rgba(colors[0], 0.8),
            yAxisID: 'y',
            borderWidth: 2,
            pointRadius: 0,
            pointBorderColor: 'transparent',
            pointBackgroundColor: colors[0],
            pointBorderWidth: 2,
            hoverPointRadius: 6,
            hoverPointBorderColor: '#fff',
            hoverPointBackgroundColor: colors[0],
            hoverPointBorderWidth: 4
        },
    ]
    if(!is_single_content) datasets.push({
        type: 'bar',
        label: `${wps_js._('published')} Contents`,
        data: data.posts,
        backgroundColor: wps_js.hex_to_rgba(colors[2], 0.5),
        hoverBackgroundColor: colors[2],
        yAxisID: 'y1',
    })

    let scales={
        x: {
            offset:false,
            ticks: {
                maxTicksLimit: 9,
                fontColor: '#898A8E',
                fontSize: 13,
                fontStyle: 'italic',
                fontFamily: '"Roboto",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif',
                fontWeight: 'lighter ',
                padding: 8,
                lineHeight: 15
            },
            border: {
                color: 'transparent',
                width: 0
            },
            grid: {
                display: false,
                drawBorder: false,
                tickLength: 0,
            }
        },
        y: {
            offset:false,
            border: {
                color: 'transparent',
                width: 0
            },
            type: 'linear',
            position: is_single_content ? 'left' :  'right',
            grid: {
                display: true,
                borderDash: [5, 5]
            },
            title: {
                display: true,
                text: wps_js._('Views'),
                color: '#898A8E',
                fontSize: 13
            }
        }
    }
    if (!is_single_content) {
         scales.y1 = {
            type: 'linear',
                position: 'left',
                border: {
                color: 'transparent',
                    width: 0
            },
            grid: {
                display: false,
                    drawBorder: false,
                    tickLength: 0,
            },
            ticks: {
                maxTicksLimit: 7,
                    fontColor: '#898A8E',
                    fontSize: 13,
                    fontStyle: 'italic',
                    fontFamily: '"Roboto",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif',
                    fontWeight: 'lighter ',
                    padding: 8,
                    lineHeight: 15
            },
            title: {
                display: true,
                    text: `${wps_js._('published')} Contents`,
                    color: '#898A8E',
                    fontSize: 13
            }
        }
    }
     const performanceChart = new Chart(ctx_performance, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets:datasets
        },
        options: {
             interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: false
            },
            scales: scales
        }
    });
    legendHandel(performanceChart)
};


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