/**
 * Mini bar chart of the post summary. Used by the "Statistics Summary" meta box and by the
 * block editor "WP Statistics" panel (window.wpStatisticsPostSummaryChart).
 *
 * @param {string|HTMLCanvasElement} elementId Canvas element or its id.
 * @param {object} args `postChartData` and `postChartSettings` from the summary data.
 * @returns {object|undefined} The Chart instance, so the caller can destroy it.
 */
wps_js.post_summary_chart = function (elementId, args = []) {
    const canvas = typeof elementId === 'string' ? document.getElementById(elementId) : elementId;
    if (!canvas || !args || !args.postChartData || typeof Chart === 'undefined') return;

    const postChartData = args.postChartData;
    const entries = Object.entries(postChartData);
    let postChartTooltipLabel = 'Visitors';
    let postChartColor = '#404BF2';

    if (typeof (args.postChartSettings) !== 'undefined' && args.postChartSettings !== null) {
        if (args.postChartSettings.color) postChartColor = args.postChartSettings.color;
        if (args.postChartSettings.label) postChartTooltipLabel = args.postChartSettings.label;
    }

    // The Mini Chart color is picked for the small list charts and may be very pale
    // (e.g. #e2ebfe). Thin bars in that color vanish on white, so fall back to the brand blue.
    const hex = String(postChartColor).replace('#', '');
    if (/^[0-9a-f]{6}$/i.test(hex)) {
        const [r, g, b] = [0, 2, 4].map(i => parseInt(hex.substr(i, 2), 16) / 255);
        if (0.2126 * r + 0.7152 * g + 0.0722 * b > 0.75) postChartColor = '#404BF2';
    } else if (!/^[0-9a-f]{3}$/i.test(hex)) {
        postChartColor = '#404BF2';
    }

    const escapeHtml =value => String(value).replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));

    // Tooltip as HTML next to the canvas, kept inside the meta box.
    const externalTooltipHandler = (context) => {
        const {chart, tooltip} = context;
        const wrapper = chart.canvas.parentNode;

        let tooltipEl = wrapper.querySelector('.c-wps-mini-chart-post-summary-tooltip');
        if (!tooltipEl) {
            tooltipEl = document.createElement('div');
            tooltipEl.classList.add('c-wps-mini-chart-post-summary-tooltip');
            wrapper.appendChild(tooltipEl);
        }

        if (tooltip.opacity === 0 || !tooltip.dataPoints || !tooltip.dataPoints.length) {
            tooltipEl.style.opacity = 0;
            return;
        }

        const point = tooltip.dataPoints[0];
        const day = entries[point.dataIndex] ? entries[point.dataIndex][1] : null;
        tooltipEl.innerHTML =
            `<div class="chart-title">${escapeHtml(day ? day.fullDate : point.label)}</div>` +
            `<div class="content-item"><span>${escapeHtml(postChartTooltipLabel)}</span> <span>${escapeHtml(point.formattedValue)}</span></div>`;
        tooltipEl.style.opacity = 1;

        const {offsetLeft: positionX, offsetTop: positionY, offsetWidth: canvasWidth} = chart.canvas;
        const tooltipWidth = tooltipEl.offsetWidth;
        const tooltipHeight = tooltipEl.offsetHeight;

        // Centre on the hovered bar, but never past the left or right edge of the chart.
        let left = positionX + tooltip.caretX - tooltipWidth / 2;
        left = Math.max(positionX, Math.min(left, positionX + canvasWidth - tooltipWidth));

        // Above the bar. It may cover the caption and rows above the chart, but must not
        // leave the meta box (the box above would cover it).
        let top = positionY + tooltip.caretY - tooltipHeight - 8;
        const inside = wrapper.closest('.inside, .wps-post-panel');
        if (inside) {
            const minTop = inside.getBoundingClientRect().top - wrapper.getBoundingClientRect().top + 4;
            top = Math.max(top, minTop);
        }

        tooltipEl.style.left = `${left}px`;
        tooltipEl.style.top = `${top}px`;
    };

    const labels = entries.map(([date]) => date);
    const values = entries.map(([, stat]) => parseInt(stat.hits, 10) || 0);
    const lastIndex = labels.length - 1;
    const zeroColor = '#DCDCDE';

    return new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: values.map(v => v === 0 ? zeroColor : wps_js.hex_to_rgba(postChartColor, 0.85)),
                hoverBackgroundColor: values.map(v => v === 0 ? '#A7AAAD' : postChartColor),
                borderWidth: 0,
                borderRadius: 1,
                // Many days in a narrow sidebar: use the full slot so bars stay visible.
                barPercentage: values.length > 60 ? 1.0 : 0.8,
                categoryPercentage: 1.0,
                // Days with no traffic still show a thin baseline mark.
                minBarLength: 2,
            }],
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {display: false},
                tooltip: {
                    enabled: false,
                    external: externalTooltipHandler,
                },
            },
            scales: {
                x: {
                    grid: {display: false},
                    border: {color: '#DCDCDE'},
                    ticks: {
                        // Only the first and last date, aligned to the chart edges.
                        autoSkip: false,
                        maxRotation: 0,
                        minRotation: 0,
                        align: 'inner',
                        color: '#757575',
                        font: {size: 11},
                        padding: 4,
                        callback: (value, index) => (index === 0 || index === lastIndex) ? labels[index] : '',
                    },
                },
                y: {
                    // No y-axis: the caption above the chart names the peak value.
                    display: false,
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        maxTicksLimit: 3,
                    },
                },
            },
            layout: {
                padding: 0,
            },
        },
    });
};

window.wpStatisticsPostSummaryChart = wps_js.post_summary_chart;

wps_js.render_wp_statistics_post_summary_widget = function(response, key){
    if (response && response.response) {
        const inner = wps_js.metaBoxInner(key);
        inner.html(response.response.output);
        // The canvas inside this meta box, never another one with the same id (e.g. the block editor panel).
        const canvas = inner.find('canvas').get(0);
        if (canvas && response.response?.data) {
            wps_js.post_summary_chart(canvas, response.response.data);
        }
        wps_js.initDatePickerHandlers();
    }
}
