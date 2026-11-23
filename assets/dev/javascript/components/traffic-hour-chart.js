wps_js.TrafficHourCharts=function(data){
    // Same tooltip as every other chart (chart.js: wpsTooltipHtml): Now | Before | Change.
    // Today's hours after the current hour have no value yet ("—"), the current hour is "so far".
    const hourTooltipHandler = (context, dataset, colors, data) => {
        const {chart, tooltip} = context;
        if (tooltip.opacity === 0 || !tooltip.dataPoints || !tooltip.dataPoints.length) {
            wpsShowTooltip(chart, tooltip, null);
            return;
        }
        const dataIndex = tooltip.dataPoints[0].dataIndex;
        const datasets = chart.data.datasets;
        const label = (data.data.labels || [])[dataIndex] || {};
        const isToday = !!label.date && label.date === wpsChartToday();
        const currentHour = new Date().getHours();
        const future = isToday && dataIndex > currentHour;
        const strings = wpsTooltipStrings();
        const hasPrevious = !!data.previousData;
        const prevNoData = hasPrevious && (data.previousData.datasets || []).every(ds => (ds.data || []).every(v => !Number(v)));
        const rows = [];
        datasets.forEach((set, index) => {
            if (String(set.label).includes('(Previous)')) return;
            const meta = chart.getDatasetMeta(index);
            if (meta.hidden) return;
            const prevIndex = datasets.findIndex(ds => ds.label === `${set.label} (Previous)`);
            const hasBefore = prevIndex > -1 && !chart.getDatasetMeta(prevIndex).hidden;
            const now = future ? null : Number(set.data[dataIndex]) || 0;
            const before = hasBefore && !prevNoData ? Number(datasets[prevIndex].data[dataIndex]) || 0 : null;
            rows.push({
                label: set.label,
                color: colors[set.label] || set.backgroundColor,
                bar: true,
                now,
                before,
                hasBefore,
                change: hasBefore && !prevNoData && !future && !(isToday && dataIndex === currentHour) ? wpsChangePercent(now, before) : null,
            });
        });
        const prevLabel = hasPrevious ? (data.previousData.labels || [])[dataIndex] : null;
        const footer = prevNoData
            ? `${strings.noDataFor} ${prevLabel && prevLabel.formatted_date ? prevLabel.formatted_date : ''}`.trim()
            : (isToday && prevLabel && prevLabel.formatted_date ? `${strings.comparedWith} ${prevLabel.formatted_date}` : strings.comparedPrevious);
        wpsShowTooltip(chart, tooltip, {
            title: label.hour || '',
            tag: isToday && dataIndex === currentHour ? strings.soFar : '',
            rows,
            footer,
        });
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
                    // 24 hour labels: Chart.js keeps as many as fit (every 2nd, 3rd... hour), level, no collisions.
                    autoSkip: true,
                    autoSkipPadding: 12,
                    maxRotation: 0,
                    minRotation: 0,
                    callback: function (val, index) {
                        // First-strong isolate: "12 AM" stays "12 AM" in RTL, a translated label keeps its own direction.
                        return '\u2068' + this.getLabelForValue(val) + '\u2069';
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
                    stepSize: 1,
                    callback: renderFormatNum,
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

    window.wpsCharts.push(lineChart);
    wps_js.chartKeyboard(lineChart);

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
            // This chart's own "Previous period" item, not every chart's on the page.
            const previousPeriod = chartElement.parentElement.parentElement.querySelectorAll('.wps-postbox-chart--previousPeriod');
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
                                 <span class="wps-postbox-chart--item--color" style="border-color: ${dataset.backgroundColor}"></span> ${wps_js.escapeHtml(dataset.label)}
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

window.renderTrafficHourChart = (data) => {
    wps_js.TrafficHourCharts(data);
}