wps_js.render_wp_statistics_hourly_usage_widget = function (response, key) {

    const hourTooltipHandler = (context, dataset, colors, data) => {
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
            const currentDatasets = datasets.filter(d => !d.label.includes('(Previous)'));
            const previousDatasets = datasets.filter(d => d.label.includes('(Previous)'));
            const currentHour = new Date().getHours();
            const hoveredHour = data.data.labels[dataIndex].hour;
            const hoveredHourNum = parseInt(hoveredHour.match(/\d+/)[0]);
            const isPM = hoveredHour.includes('PM');
            // Convert to 24-hour format
            const hoveredHour24 = hoveredHourNum === 12 ?
                (isPM ? 12 : 0) :
                (isPM ? hoveredHourNum + 12 : hoveredHourNum);

            // Check if current period has any non-zero values
            const hasCurrentValues = currentDatasets.some(dataset =>
                dataset.data[dataIndex] > 0
            );
            const hasPreviousValues = previousDatasets.some(dataset =>
                dataset.data[dataIndex] > 0
            );
            const shouldShowCurrent = hoveredHour24 <= currentHour;
            const dateToShow = shouldShowCurrent && (hasCurrentValues || (!hasCurrentValues && !hasPreviousValues)) ?
                data.data.labels[dataIndex].formatted_date + ' (' + data.data.labels[dataIndex].hour + ')' :
                data.previousData.labels[dataIndex].formatted_date + ' (' + data.data.labels[dataIndex].hour + ')';

            innerHtml += `<div class="chart-title">${dateToShow}</div>`;

            if (shouldShowCurrent && (hasCurrentValues || (!hasCurrentValues && !hasPreviousValues))) {
                currentDatasets.forEach(dataset => {
                    const meta = chart.getDatasetMeta(datasets.indexOf(dataset));
                    if (!meta.hidden) {
                        const value = dataset.data[dataIndex];
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
            }
            if (!shouldShowCurrent || hasPreviousValues) {
                const colorValues = Object.values(colors);
                previousDatasets.forEach((dataset, index) => {
                    const meta = chart.getDatasetMeta(datasets.indexOf(dataset));
                    const prevLabel = dataset.label.replace(' (Previous)', '');
                    if (!meta.hidden) {
                        const value = dataset.data[dataIndex];
                        innerHtml += `
                        <div class="previous-data">
                            <div>
                                 <span class="previous-data__colors">
                                    <span class="previous-data__color" style="background-color:${wps_js.hex_to_rgba(colorValues[index], 0.5)};"></span>
                                    <span class="previous-data__color" style="background-color: ${wps_js.hex_to_rgba(colorValues[index], 0.5)}"></span>
                                 </span>
                                 ${prevLabel} 
                            </div>
                            <span class="current-data__value">${value.toLocaleString()}</span>
                        </div>`;
                    }
                });
            }

            innerHtml += `</div>`;
            tooltipEl.innerHTML = innerHtml;
            wps_js.setTooltipPosition(tooltipEl, chart, tooltip);
        }
    };

    function createPattern(color) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = 5;
        canvas.height = 5;
        ctx.fillStyle = color;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
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
                duration: 0,
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
                    external: (context) => hourTooltipHandler(context, datasets, colors, data),
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
                        lineHeight: window.innerWidth < 768 ? 10 : 15,
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
        let dateLabels = data.data.labels.map(dateObj => dateObj.hour);

        const lineChart = new Chart(ctx_line, {
            type: 'bar',
            data: {
                labels: dateLabels,
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