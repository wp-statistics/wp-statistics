wps_js.post_summary_meta_box = {
    params: function () {
        return { 'ID': wps_js.global['page']['ID'] };
    },

    view: function (args = []) {
        return args.hasOwnProperty('content') ?
            ' <div class="wps-center" style="padding: 15px;"> ' + args['content'] + '</div>' :
            '<p class="wps-wrap wps-meta-box-header">' + args['output'] + '</p>' + '<canvas id="' + wps_js.chart_id('post_summary') + '" height="85"></canvas>';
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
        if (typeof (args.postChartData) !== 'undefined' && args.postChartData !== null) {
            postChartData = args.postChartData;
        }

        const externalTooltipHandler = (context) => {
            const { chart, tooltip } = context;

            let tooltipEl = chart.canvas.parentNode.querySelector('div');
            if (!tooltipEl) {
                tooltipEl = document.createElement('div');
                tooltipEl.classList.add('wps-mini-chart-meta-box-tooltip');
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
                            return postChartData[tooltipItems[0].dataIndex].fullDate;
                        },
                        label: (tooltipItem) => {
                            const count = tooltipItem.formattedValue;
                            if (tooltipItem.label === '-1') {
                                return null;
                            } else {
                                return `<div class="content-itemss"> <div class="content-item"><span>Views</span> <span>${count}</span></div>`;
                            }
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

        const chartContext = jQuery('#' + elementId)[0].getContext('2d')
        new Chart(chartContext, {
            type: 'bar',
            data: {
                labels: postChartData.map(stat => stat.shortDate),
                datasets: [{
                    data: postChartData.map(stat => stat.views),
                    borderColor: '#0D0725',
                    backgroundColor: 'rgba(115, 98, 191, 0.5)',
                    pointBackgroundColor: '#0D0725',
                    fill: true,
                    barPercentage: 0.9,
                    categoryPercentage: 1.0,
                    tension: 0.5,
                    minBarLength: 1,
                    borderWidth: 1,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                }],
            },
            options: chartOptions,
        });
    }
};
