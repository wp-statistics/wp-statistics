wps_js.render_line_chart = function(response, key) {
    if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response.output);
        if (response.response?.data) {
            let params = response.response.data;
            const data = {
                data: params['data'],
                previousData: params['previousData']
            };
            wps_js.new_line_chart(data, `wps_${key}_meta_chart`);
        }
        wps_js.initDatePickerHandlers();
    }
};


 
wps_js.render_search_engines = wps_js.render_line_chart;
wps_js.render_daily_traffic_trend = wps_js.render_line_chart;

wps_js.render_traffic_hour = function(response, key) {

    const hourTooltipHandler = (context, dataset, colors, data, dateLabels ) => {
        const {chart, tooltip} = context;
        const tooltipEl = getOrCreateTooltip(chart);
        if (tooltip.opacity === 0) {
            tooltipEl.style.opacity = 0;
            return;
        }
        if (tooltip.body) {
            const dataIndex = tooltip.dataPoints[0].dataIndex;
            const datasets = chart.data.datasets;
            let innerHtml = `<div>`;
            datasets.forEach((dataset, index) => {
                const meta = chart.getDatasetMeta(index);
                const value = dataset.data[dataIndex];
                const isPrevious = dataset.label.includes('(Previous)');
                if (!meta.hidden && !isPrevious) {
                    innerHtml += `
                <div class="current-data">
                    <div>
                        <span class="current-data__color" style="background-color: ${dataset.backgroundColor};"></span>
                        ${dataset.label}
                    </div>
                    <span class="current-data__value">${value.toLocaleString()}</span>
                </div>`;
                }
            });

            innerHtml += `</div>`;

            tooltipEl.innerHTML = innerHtml;
            const {offsetLeft: chartLeft, offsetTop: chartTop, clientWidth: chartWidth, clientHeight: chartHeight} = chart.canvas;
            const {caretX, caretY} = tooltip;

            const tooltipWidth = tooltipEl.offsetWidth;
            const tooltipHeight = tooltipEl.offsetHeight;

            const margin = 16;
            let tooltipX = chartLeft + caretX + margin;
            let tooltipY = chartTop + caretY - tooltipHeight / 2;

            if (tooltipX + tooltipWidth + margin > chartLeft + chartWidth) {
                tooltipX = chartLeft + caretX - tooltipWidth - margin;
            }

            if (tooltipX < chartLeft + margin) {
                tooltipX = chartLeft + margin;
            }

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
    function createPattern(color) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = 5;
        canvas.height = 5;

        // Base fill color
        ctx.fillStyle = color;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Stripe line
        ctx.strokeStyle = 'white';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(canvas.width, 0);
        ctx.lineTo(0, canvas.height);
        ctx.stroke();

        return ctx.createPattern(canvas, 'repeat');
    }

    if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response.output);
    }
    wps_js.metaBoxInner(key).html(response.response.output);
    if (response.response?.data) {
        let params = response.response.data;
        const data = {
            data: params['data'],
            previousData: params['previousData']
        };
        let ctx_line = document.getElementById('hourly-usage-chart').getContext('2d');
        let colors = {
            'Views': '#7362BF',
            'Visitors': '#3288D7',
         };
         const datasets = [];
         Object.keys(data.data.datasets).forEach((key, index) => {

            let color = colors[data.data.datasets[key].label];

              datasets.push({
                type: 'bar',
                label: data.data.datasets[key].label,
                data: data.data.datasets[key].data,
                backgroundColor: wps_js.hex_to_rgba(color, 0.5),
                fill: false,
                yAxisID: 'y',
                hitRadius: 10
            });

        });
        if (data?.previousData) {
            Object.keys(data.previousData.datasets).forEach((key, index) => {
                const dataset = data.previousData.datasets[key];
                const colorKey = dataset.label;
                 // Previous data dataset
                datasets.push({
                    type: 'bar',
                    label: `${data.previousData.datasets[key].label} (Previous)`,
                    data: data.previousData.datasets[key].data,
                    backgroundColor: createPattern(wps_js.hex_to_rgba(colors[colorKey], 0.5)),
                    fill: false,
                    yAxisID: 'y',
                    pointRadius: 0,
                    hitRadius: 10
                });
            });
        }

        const defaultOptions = {
            maintainAspectRatio: false,
            resizeDelay: 200,
            animation: {
                duration: 0,  // Disable animation
            },
            responsive: true,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: false,
                tooltip: {
                    enabled: false,
                    external: (context) => hourTooltipHandler(context, datasets, colors, data, data.data.labels),
                    callbacks: {
                        title: (tooltipItems) => tooltipItems[0].label,
                        label: (tooltipItem) => tooltipItem.formattedValue
                    }
                }
            },
            scales: {
                x: {
                    offset: true,
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
                        autoSkip: false,
                        maxRotation: 90,
                        minRotation: 90,
                        callback: function (val, index) {
                            return ' ' + this.getLabelForValue(val);
                        },
                         font: {
                            color: '#898A8E',
                            style: 'italic',
                            size: window.innerWidth < 768 ? 8 : 13
                         },
                        padding: window.innerWidth < 768 ? 3 : 8,
                    }
                },
                y: {
                    min: 0,
                    ticks: {
                        maxTicksLimit: 7,
                        font: {
                            color: '#898A8E',
                            style: 'italic',
                            size: window.innerWidth < 768 ? 8 : 13
                        },
                        padding: window.innerWidth < 768 ? 2 : 8,
                        lineHeight:window.innerWidth < 768 ? 10 : 15,
                        stepSize: 1
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
                        tickColor: '#EEEFF1',
                        color: '#EEEFF1'
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
 
         const lineChart = new Chart(ctx_line, {
            type: 'bar',
            data: {
                labels: data.data.labels,
                datasets: datasets,
            },
            options: defaultOptions
        });

        const updateLegends = function () {
            const chartElement = document.getElementById('hourly-usage-chart');
            // Find the legend container that is beside this chart
            const legendContainer = chartElement.parentElement.parentElement.querySelector('.wps-postbox-chart--items');

            if (legendContainer) {
                legendContainer.innerHTML = '';
                datasets.sort((a, b) => {
                    // Move "Total" and "Total (Previous)" to the top
                    if (a.label === 'Total') return -1;
                    if (b.label === 'Total') return 1;
                    if (a.label === 'Total (Previous)') return -1;
                    if (b.label === 'Total (Previous)') return 1;
                    return 0;
                });
                const previousPeriod = document.querySelectorAll('.wps-postbox-chart--previousPeriod');
                if (previousPeriod.length > 0) {
                    let foundPrevious = false;

                    datasets.forEach((dataset) => {
                        if (dataset.label.includes('(Previous)')) {
                            foundPrevious = true;
                        }
                    });

                    if (foundPrevious) {
                        previousPeriod.forEach((element) => {
                            element.style.display = 'flex';
                        });
                    }
                }
                datasets.forEach((dataset, index) => {
                    const isPrevious = dataset.label.includes('(Previous)');
                    if (!isPrevious) {
                        const currentData = dataset.data.reduce((a, b) => Number(a) + Number(b), 0);

                        const legendItem = document.createElement('div');
                        legendItem.className = 'wps-postbox-chart--item';


                        // Build the legend item HTML
                        legendItem.innerHTML = `
            <span class="current-data">
                 <span class="wps-postbox-chart--item--color" style="border-color: ${dataset.backgroundColor}"></span> ${dataset.label}
            </span>
           `;

                        // Add click event to toggle visibility of the current dataset only
                        const currentDataDiv = legendItem.querySelector('.current-data');
                        currentDataDiv.addEventListener('click', function () {
                            const metaMain = lineChart.getDatasetMeta(index);
                            metaMain.hidden = !metaMain.hidden;
                            currentDataDiv.classList.toggle('wps-line-through');
                            lineChart.update();
                        });

                        legendContainer.appendChild(legendItem);
                    }
                });
            }
        };
        updateLegends();
    }
    wps_js.initDatePickerHandlers();
 };