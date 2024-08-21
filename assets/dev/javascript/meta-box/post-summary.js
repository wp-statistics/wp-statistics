wps_js.post_summary_meta_box = {
    params: function () {
        return { 'ID': wps_js.global['page']['ID'] };
    },

    view: function (args = []) {
        return args.hasOwnProperty('content') ?
            '<div class="wps-center" style="padding: 15px;"> ' + args['content'] + '</div>' :
            '<p class="wps-wrap wps-meta-box-header">' + args['output'] + '</p>' + '<div class="c-wps-post-summary-panel-chart"><canvas id="' + wps_js.chart_id('post_summary') + '" height="85"></canvas></div>';
    },

    meta_box_init: function (args = []) {
        if (!args.hasOwnProperty('content')) {
            this.post_summary_chart(wps_js.chart_id('post_summary'), args['summary']);
        } else {
            jQuery("#" + wps_js.getMetaBoxKey('post_summary') + " button[onclick]").remove();
        }
    },

    post_summary_chart: function (elementId, args = []) {
        let postChartData = [];
        let postChartSettings = [];
        let postChartTooltipLabel = 'Visitors';
        let $postChartColor = '#A5AAEA';
        let $postChartStroke = '#2C36D7';
        let gradient;

        if (typeof (args.postChartData) !== 'undefined' && args.postChartData !== null) {
            postChartData = args.postChartData;
        }
        if (typeof (args.postChartSettings) !== 'undefined' && args.postChartSettings !== null) {
            postChartSettings = args.postChartSettings;
            if (postChartSettings.color) $postChartColor = postChartSettings.color;
            if (postChartSettings.border) $postChartStroke = postChartSettings.border;
            if (postChartSettings.label)  postChartTooltipLabel = postChartSettings.label;
        }


        const externalTooltipHandler = (context) => {
            const { chart, tooltip } = context;

            let tooltipEl = chart.canvas.parentNode.querySelector('div');
            if (!tooltipEl) {
                tooltipEl = document.createElement('div');
                tooltipEl.classList.add('c-wps-mini-chart-post-summary-tooltip');
                chart.canvas.parentNode.appendChild(tooltipEl);
            }

            if (tooltip.opacity === 0) {
                tooltipEl.style.opacity = 0;
                return;
            }

            if (tooltip.body) {
                const titleLines = tooltip.title || [];
                const bodyLines = tooltip.body.map(b => b.lines);
                let innerHtml = `<div>`;

                // Title
                titleLines.forEach(title => {
                    innerHtml += `<div class="chart-title">${title}</div>`;
                });

                bodyLines.forEach((body, i) => {
                    const line = body.join(': ');
                    innerHtml += `<div>${line}</div>`;
                });

                tooltipEl.innerHTML = innerHtml;
                const { offsetLeft: positionX, offsetTop: positionY, offsetWidth: canvasWidth, offsetHeight: canvasHeight } = chart.canvas;
                tooltipEl.style.opacity = bodyLines[0].length === 0 ? 0 : 1;
                const tooltipWidth = tooltipEl.offsetWidth;

                let left = positionX + tooltip.caretX - tooltipWidth / 2;
                let top = positionY + canvasHeight;

                if (left < positionX) {
                    left = positionX;
                }

                if (left + tooltipWidth > positionX + canvasWidth) {
                    left = positionX + canvasWidth - tooltipWidth;
                }

                tooltipEl.style.left = `${left}px`;
                tooltipEl.style.top = `${top}px`;
            }
        };

        const chartOptions = {
            animation: false,
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    enabled: false,
                    displayColors: false,
                    position: 'nearest',
                    intersect: false,
                    external: externalTooltipHandler,
                    callbacks: {
                        title: (tooltipItems) => {
                            return postChartData[tooltipItems[0].label].fullDate;
                        },
                        label: (tooltipItem) => {
                            const count = tooltipItem.formattedValue;
                            return `<div class="content-itemss"> <div class="content-item"><span>${postChartTooltipLabel}</span> <span>${count}</span></div>`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    offset: true,
                    grid: {
                        display: false,
                    },
                    border: {
                        color: 'transparent',
                        width: 0
                    },
                    ticks: {
                        align: 'inner',
                        maxTicksLimit: 4,
                        fontColor: '#898A8E',
                        fontSize: 12,
                        padding: 5,
                    }
                },
                y: {
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        display: false,
                    },
                    border: {
                        color: 'transparent',
                        width: 0
                    },
                    ticks: {
                        align: 'inner',
                        maxTicksLimit: 5,
                        fontColor: '#898A8E',
                        fontSize: 12,
                        padding: 8,
                    }
                }
            },
            layout: {
                padding: {
                    left: 0,
                    right: 0,
                    top: 0,
                    bottom: 0,
                },
            }
        };

        const chartContext = jQuery('#' + elementId)[0].getContext('2d');
        const chartData= Object.entries(postChartData).map(([date, stat]) => stat.hits);
        const type= chartData.length <= 30 ? 'bar' : 'line';

        if (type === 'line') {
            gradient = chartContext.createLinearGradient(0, 0, 0, chartContext.canvas.height - 10);
            gradient.addColorStop(0, wps_js.hex_to_rgba($postChartColor,1));
            gradient.addColorStop(0.5, wps_js.hex_to_rgba($postChartColor,0.25));
            gradient.addColorStop(0.75, wps_js.hex_to_rgba($postChartColor,0));
            gradient.addColorStop(1, wps_js.hex_to_rgba($postChartColor,0));
        }

        const getBackgroundColor = (value) => value.hits === 0 ? '#000000b3' : wps_js.hex_to_rgba($postChartColor, 0.5);
        const getHoverBackgroundColor = (value) => value.hits === 0 ? '#000000b3' : $postChartColor;
        const backgroundColors = type === 'line' ? gradient : Object.entries(postChartData).map(getBackgroundColor);
        const hoverBackgroundColors = type === 'line' ? gradient : Object.entries(postChartData).map(getHoverBackgroundColor);
        const borderColor = $postChartStroke;

        new Chart(chartContext, {
            type: type,
            data: {
                labels: Object.entries(postChartData).map(([date, stat]) => date),
                datasets: [{
                    data: chartData,
                    backgroundColor: backgroundColors,
                    hoverBackgroundColor: hoverBackgroundColors,
                    pointBackgroundColor: borderColor,
                    fill: type === 'line',
                    barPercentage: 0.9,
                    categoryPercentage: 1.0,
                    tension: 0.5,
                    minBarLength: 1,
                    borderWidth: type === 'line' ? 1 : 0,
                    pointRadius: type === 'line' ? 0 : undefined,
                    pointHoverRadius: type === 'line' ? 5 : undefined
                }],
            },
            options: chartOptions,
        });
    }
};
