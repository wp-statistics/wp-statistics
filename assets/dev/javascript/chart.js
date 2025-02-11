wps_js.hex_to_rgba = function (hex, opacity) {
    const defaultColor = '#3288D7';
    if (typeof hex !== 'string' || hex[0] !== '#' || (hex.length !== 7 && hex.length !== 4)) {
        hex = defaultColor;
    }
    if (hex.length === 4) {
        hex = '#' + hex[1].repeat(2) + hex[2].repeat(2) + hex[3].repeat(2);
    }
    hex = hex.replace('#', '');
    let hex_to_rgba_r = parseInt(hex.substring(0, 2), 16);
    let hex_to_rgba_g = parseInt(hex.substring(2, 4), 16);
    let hex_to_rgba_b = parseInt(hex.substring(4, 6), 16);
    return wps_js.rgba_to_hex(hex_to_rgba_r, hex_to_rgba_g, hex_to_rgba_b, opacity);
}

wps_js.rgba_to_hex = function (r, g, b, a) {
    let hex_r = r.toString(16).padStart(2, '0');
    let hex_g = g.toString(16).padStart(2, '0');
    let hex_b = b.toString(16).padStart(2, '0');
    let hex_a = Math.round(a * 255).toString(16).padStart(2, '0');
    return `#${hex_r}${hex_g}${hex_b}${hex_a}`;
}

const chartColors = {
    'Total': '#27A765', 'Views': '#7362BF', 'Visitors': '#3288D7', 'User Visitors':'#3288D7', 'Anonymous Visitors' :'#7362BF', 'Published Posts' : '#8AC3D0',
    'Posts': '#8AC3D0', 'Other1': '#3288D7', 'Other2': '#7362BF', 'Other3': '#8AC3D0'
};

const chartTensionValues = [0.1, 0.3, 0.5, 0.7];

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

wps_js.setTooltipPosition = function (tooltipEl, chart, tooltip) {
    const {
        offsetLeft: chartLeft,
        offsetTop: chartTop,
        clientWidth: chartWidth,
        clientHeight: chartHeight
    } = chart.canvas;
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
const externalTooltipHandler = (context, data, dateLabels, prevDateLabels, monthTooltip, prevMonthTooltip) => {
    const {chart, tooltip} = context;
    const unitTime = chart.options.plugins.tooltip.unitTime;
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
        titleLines.forEach(title => {
            if (unitTime === 'day') {
                const label = (data.data) ? data.data.labels[dataIndex] : data.labels[dataIndex];
                const {date, day} = label; // Ensure `date` and `day` are correctly extracted
                const phpDateFormat = wps_js.isset(wps_js.global, 'options', 'wp_date_format') ? wps_js.global['options']['wp_date_format'] : 'MM/DD/YYYY';
                let momentDateFormat = phpToMomentFormat(phpDateFormat);
                innerHtml += `<div class="chart-title">${moment(date).format(momentDateFormat)} (${day})</div>`;
            } else if (unitTime === 'month') {
                innerHtml += `<div class="chart-title">${monthTooltip[dataIndex]}</div>`;
            } else {
                innerHtml += `<div class="chart-title">${dateLabels[dataIndex]}</div>`;
            }
        });

        datasets.forEach((dataset, index) => {
            const meta = chart.getDatasetMeta(index);
            const isPrevious = dataset.label.includes('(Previous)');
            if (!meta.hidden && !isPrevious) {
                const value = dataset.data[dataIndex];
                innerHtml += `
                <div class="current-data">
                    <div>
                        <span class="current-data__color" style="background-color: ${dataset.hoverPointBackgroundColor};"></span>
                        ${dataset.label}
                    </div>
                    <span class="current-data__value">${value.toLocaleString()}</span>
                </div>`;
            }

            const previousDataset = datasets.find(ds => ds.label === `${dataset.label} (Previous)`);
            if (previousDataset) {
                const previousMeta = chart.getDatasetMeta(datasets.indexOf(previousDataset));
                const previousPeriodElement = document.querySelector('.wps-postbox-chart--previousPeriod');
                const isPreviousHidden = previousPeriodElement && previousPeriodElement.classList.contains('wps-line-through');

                if (!previousMeta.hidden && !isPreviousHidden) {
                    const previousValue = previousDataset.data[dataIndex] || 0;
                    let previousLabel = null;
                    if (unitTime === 'day') {
                        previousLabel = prevDateLabels[dataIndex];
                    } else if (unitTime === 'month') {
                        previousLabel = prevMonthTooltip[dataIndex];
                    } else {
                        previousLabel = prevDateLabels[dataIndex];
                    }

                    if (previousLabel === undefined) {
                        previousLabel = 'N/A'; // Fallback for undefined labels
                    }
                    innerHtml += `
                    <div class="previous-data">
                        <div>
                            <span class="previous-data__colors">
                                <span class="previous-data__color" style="background-color: ${dataset.hoverPointBackgroundColor};"></span>
                                <span class="previous-data__color" style="background-color: ${dataset.hoverPointBackgroundColor};"></span>
                            </span>
                            ${previousLabel}
                        </div>
                        <span class="previous-data__value"> ${previousValue.toLocaleString()}</span>
                    </div>`;
                }
            }
        });

        innerHtml += `</div>`;
        tooltipEl.innerHTML = innerHtml;
        wps_js.setTooltipPosition(tooltipEl, chart, tooltip);
    }
};

// Custom plugin definition
const drawVerticalLinePlugin = {
    id: 'drawVerticalLine',
    beforeDatasetDraw(chart) {
        const {ctx, scales: {x, y}, tooltip, chartArea: {top, bottom}} = chart;
        if (tooltip && tooltip._active && tooltip._active.length) {
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


const phpToMomentFormat = (phpFormat) => {
    const formatMap = {
        'd': 'DD',
        'j': 'D',
        'S': 'Do',
        'n': 'M',
        'm': 'MM',
        'F': 'MMM',
        'M': 'MMM',
        'y': 'YY',
        'Y': 'YYYY'
    };
    return phpFormat.replace(/([a-zA-Z])/g, (match) => formatMap[match] || match);
}

const formatDateRange = (startDate, endDate, unit, momentDateFormat, isInsideDashboardWidgets) => {
    const startDateFormat = momentDateFormat.replace(/,?\s?(YYYY|YY)[-/\s]?,?|[-/\s]?(YYYY|YY)[-/\s]?,?/g, "");
    if (unit === 'month') {
        const monthFormat = momentDateFormat
            .replace(/D+/g, '')
            .replace(/\/\//g, '/')
            .replace(/^\//, '')
            .replace(/\/$/, '')
            .replace(/\s*,/, '')
            .replace(/-$/, '')
            .trim();
        return moment(startDate).format(monthFormat);

    } else {
        if (moment(startDate).year() === moment(endDate).year()) {
            return `${moment(startDate).format(startDateFormat)} to ${moment(endDate).format(momentDateFormat)}`;
        } else {
            return `${moment(startDate).format(momentDateFormat)} to ${moment(endDate).format(momentDateFormat)}`;
        }
    }
}

const setMonthDateRange = (startDate, endDate, momentDateFormat) => {
    const startDateFormat = momentDateFormat.replace(/,?\s?(YYYY|YY)[-/\s]?,?|[-/\s]?(YYYY|YY)[-/\s]?,?/g, "");
    if (moment(startDate).year() === moment(endDate).year()) {
        return `${moment(startDate).format(startDateFormat)} to ${moment(endDate).format(momentDateFormat)}`;
    } else {
        return `${moment(startDate).format(momentDateFormat)} to ${moment(endDate).format(momentDateFormat)}`;
    }
}

const aggregateData = (labels, datasets, unit, momentDateFormat, isInsideDashboardWidgets) => {
    if (!labels || !labels.length || !datasets || !datasets.length) {
        console.error("Invalid input: labels or datasets are empty.");
        return {
            aggregatedLabels: [],
            aggregatedData: datasets ? datasets.map(() => []) : [],
            monthTooltipTitle: [],
        };
    }
    const isIncompletePeriod = [];
    const now = moment();
    if (unit === 'day') {
        labels.forEach(label => {
            const date = moment(label.date);
            isIncompletePeriod.push(date.isSameOrAfter(now, 'day'));
        });
        return {
            aggregatedLabels: labels.map(label => label.formatted_date),
            aggregatedData: datasets.map(dataset => dataset.data),
            monthTooltipTitle: [],
            isIncompletePeriod
        };
    }

    const aggregatedLabels = [];
    const aggregatedData = datasets.map(() => []);
    const monthTooltipTitle = [];
    const groupedData = {};

    if (unit === 'week') {
        if (wps_js._('start_of_week')) {
            moment.updateLocale('en', {
                week: {
                    dow: parseInt(wps_js._('start_of_week'))
                }
            });
        }

        const startDate = moment(labels[0].date);
        const endDate = moment(labels[labels.length - 1].date);

        // Create an array of all weeks between start and end date
        const weeks = [];
        let currentWeekStart = startDate.clone();

        while (currentWeekStart.isSameOrBefore(endDate)) {
            let nextWeekStart = currentWeekStart.clone().startOf('week').add(1, 'week');
            let weekEnd = nextWeekStart.clone().subtract(1, 'day');

            // For the last week, if it would go beyond endDate, adjust it
            if (weekEnd.isAfter(endDate)) {
                weekEnd = endDate.clone();
            }

            weeks.push({
                start: currentWeekStart.clone(),
                end: weekEnd,
                key: currentWeekStart.format('YYYY-[W]WW'),
                data: new Array(datasets.length).fill(0)
            });

            // Move to next week's start
            currentWeekStart = nextWeekStart;
        }

        labels.forEach((label, i) => {
            if (label.date) { // Check if label.date is valid
                const date = moment(label.date);
                for (let week of weeks) {
                    if (date.isBetween(week.start, week.end, 'day', '[]')) {
                        datasets.forEach((dataset, datasetIndex) => {
                            week.data[datasetIndex] += dataset.data[i] || 0;
                        });
                        break;
                    }
                }
            }
        });

        // Build the output arrays
        weeks.forEach(week => {
            const label = formatDateRange(week.start, week.end, unit, momentDateFormat, isInsideDashboardWidgets);
            aggregatedLabels.push(label);
            monthTooltipTitle.push(setMonthDateRange(week.start, week.end, momentDateFormat));
            week.data.forEach((total, datasetIndex) => {
                if (!aggregatedData[datasetIndex]) {
                    aggregatedData[datasetIndex] = [];
                }
                aggregatedData[datasetIndex].push(total);
            });
        });

        weeks.forEach(week => {
            const isIncomplete = week.end.isSameOrAfter(moment(), 'day');
            isIncompletePeriod.push(isIncomplete);
        });
    } else if (unit === 'month') {
        const startDate = moment(labels[0].date);
        const endDate = moment(labels[labels.length - 1].date);
        let currentDate = startDate.clone();
        while (currentDate.isSameOrBefore(endDate, 'month')) {
            const monthKey = currentDate.format('YYYY-MM');
            if (!groupedData[monthKey]) {
                groupedData[monthKey] = {
                    startDate: currentDate.clone().startOf('month'),
                    endDate: currentDate.clone().endOf('month'),
                    indices: [],
                };
            }
            currentDate.add(1, 'month');
        }
        labels.forEach((label, i) => {
            if (label.date) {
                const date = moment(label.date);
                const monthKey = date.format('YYYY-MM');
                if (groupedData[monthKey]) {
                    groupedData[monthKey].indices.push(i);
                }
            }
        });

        // Aggregate data for each month
        Object.keys(groupedData).forEach(monthKey => {
            const {startDate, endDate, indices} = groupedData[monthKey];
            const actualStartDate = moment.max(startDate, moment(labels[0].date));
            const actualEndDate = moment.min(endDate, moment(labels[labels.length - 1].date));
            if (!actualStartDate.isValid() || !actualEndDate.isValid()) {
                console.error(`Invalid date range for monthKey ${monthKey}`);
                return;
            }
            if (indices.length > 0) {
                const label = formatDateRange(actualStartDate, actualEndDate, unit, momentDateFormat, isInsideDashboardWidgets);
                aggregatedLabels.push(label);
                datasets.forEach((dataset, idx) => {
                    const total = indices.reduce((sum, i) => sum + (dataset.data[i] || 0), 0);
                    aggregatedData[idx].push(total);
                });
                monthTooltipTitle.push(setMonthDateRange(actualStartDate, actualEndDate, momentDateFormat));
            }
        });

        Object.keys(groupedData).forEach(monthKey => {
            const isIncomplete = groupedData[monthKey].endDate.isSameOrAfter(moment(), 'day');
            isIncompletePeriod.push(isIncomplete);
        });
    }

    return {aggregatedLabels, aggregatedData, monthTooltipTitle, isIncompletePeriod};
}
const sortTotal = (datasets) =>{
    datasets.sort((a, b) => {
        if (a.label === 'Total') return -1;
        if (b.label === 'Total') return 1;
        if (a.label === 'Total (Previous)') return -1;
        if (b.label === 'Total (Previous)') return 1;
        return 0;
    });
}
const updateLegend = (lineChart, datasets, tag_id, data) => {
    const chartElement = document.getElementById(tag_id);
    const legendContainer = chartElement.parentElement.parentElement.querySelector('.wps-postbox-chart--items');

    if (legendContainer) {
        legendContainer.innerHTML = '';
         const previousPeriod = chartElement.parentElement.parentElement.querySelector('.wps-postbox-chart--previousPeriod');
        if (previousPeriod) {
            let foundPrevious = datasets.some(dataset => dataset.label.includes('(Previous)'));

            if (foundPrevious) {
                previousPeriod.style.display = 'flex';
                previousPeriod.style.cursor = 'pointer';
                if (previousPeriod._clickHandler) {
                    previousPeriod.removeEventListener('click', previousPeriod._clickHandler);
                }
                previousPeriod._clickHandler = function (e) {
                    e.stopPropagation()
                    const isPreviousHidden = previousPeriod.classList.contains('wps-line-through');
                    previousPeriod.classList.toggle('wps-line-through');

                    datasets.forEach((dataset, datasetIndex) => {
                        if (dataset.label.includes('(Previous)')) {
                            const meta = lineChart.getDatasetMeta(datasetIndex);
                            meta.hidden = !isPreviousHidden;
                        }
                    });

                    const previousDataElements = legendContainer.querySelectorAll('.previous-data');
                    previousDataElements.forEach(elem => {
                        if (isPreviousHidden) {
                            elem.classList.remove('wps-line-through');
                        } else {
                            elem.classList.add('wps-line-through');
                        }
                    });

                    lineChart.update();
                };
                previousPeriod.addEventListener('click', previousPeriod._clickHandler);
            }
        }
        datasets.forEach((dataset, index) => {
            const isPrevious = dataset.label.includes('(Previous)');
            if (!isPrevious) {
                const currentData = dataset.data.reduce((a, b) => Number(a) + Number(b), 0);
                let previousData = null;
                let previousDatasetIndex = null;
                if (data?.previousData?.datasets.length > 0) {
                    const previousDataset = data.previousData.datasets.find((prev, prevIndex) => {
                        if (prev.label === dataset.label) {
                            previousDatasetIndex = prevIndex;
                            return true;
                        }
                        return false;
                    });
                    if (previousDataset && previousDataset.data) {
                        previousData = previousDataset.data.reduce((a, b) => Number(a) + Number(b), 0);
                    }
                }
                const legendItem = document.createElement('div');
                legendItem.className = 'wps-postbox-chart--item';

                const previousDataHTML = previousData !== null ? `
                <div class="previous-data">
                    <span>
                        <span class="wps-postbox-chart--item--color" style="border-color: ${dataset.borderColor}"></span>
                        <span class="wps-postbox-chart--item--color" style="border-color: ${dataset.borderColor}"></span>
                    </span>
                    ${previousData.toLocaleString()}
                </div>` : '';

                legendItem.innerHTML = `
                <span>
                    ${dataset.label}
                </span>
                <div>
                    <div class="current-data">
                        <span class="wps-postbox-chart--item--color" style="border-color: ${dataset.borderColor}"></span>
                        ${currentData.toLocaleString()}
                    </div>
                    ${previousDataHTML}
                </div>`;

                const currentDataDiv = legendItem.querySelector('.current-data');
                currentDataDiv.addEventListener('click', function () {
                    const metaMain = lineChart.getDatasetMeta(index);
                    metaMain.hidden = !metaMain.hidden;
                    currentDataDiv.classList.toggle('wps-line-through');
                    lineChart.update();
                });

                const previousDataDiv = legendItem.querySelector('.previous-data');
                if (previousDataDiv && previousDatasetIndex !== null) {
                    previousDataDiv.addEventListener('click', function () {
                        const metaPrevious = lineChart.data.datasets.find((dataset, dsIndex) => {
                            return dataset.label === `${datasets[index].label} (Previous)` && lineChart.getDatasetMeta(dsIndex);
                        });
                        if (metaPrevious) {
                            const metaPreviousIndex = lineChart.data.datasets.indexOf(metaPrevious);
                            const metaPreviousVisibility = lineChart.getDatasetMeta(metaPreviousIndex);
                            metaPreviousVisibility.hidden = !metaPreviousVisibility.hidden;
                            previousDataDiv.classList.toggle('wps-line-through');

                            const allPreviousData = legendContainer.querySelectorAll('.previous-data');
                            const allHaveLineThrough = Array.from(allPreviousData).every(el =>
                                el.classList.contains('wps-line-through')
                            );

                            if (previousPeriod) {
                                if (allHaveLineThrough) {
                                    previousPeriod.classList.add('wps-line-through');
                                } else {
                                    previousPeriod.classList.remove('wps-line-through');
                                }
                            }

                            lineChart.update();
                        }
                    });
                }
                legendContainer.appendChild(legendItem);
            }
        });
    }
}

const deepCopy = (obj) => JSON.parse(JSON.stringify(obj));

const chartInstances = {};

const getDisplayTextForUnitTime = (unitTime, tag_id) => {
    const select = document.querySelector(`#${tag_id}`).closest('.o-wrap').querySelector('.js-unitTimeSelect');
    if (select) {
        const option = select.querySelector(`.wps-unit-time-chart__option[data-value="${unitTime}"]`);
        if (option) {
            return option.textContent.trim();
        }
    }
    return unitTime;
}

wps_js.new_line_chart = function (data, tag_id, newOptions = null, type = 'line') {
    sortTotal(data.data.datasets);

    const realdata = deepCopy(data);
    const phpDateFormat = wps_js.isset(wps_js.global, 'options', 'wp_date_format') ? wps_js.global['options']['wp_date_format'] : 'MM/DD/YYYY';
    let momentDateFormat = phpToMomentFormat(phpDateFormat);
    const isInsideDashboardWidgets = document.getElementById(tag_id).closest('#dashboard-widgets') !== null;
    // Determine the initial unitTime
    const length = data.data.labels.map(dateObj => dateObj.formatted_date).length;

    const threshold = type === 'performance' ? 30 : 60;
    let unitTime = length <= threshold ? 'day' : length <= 180 ? 'week' : 'month';

    const datasets = [];

    // Check if there is only one data point
    const isSingleDataPoint = data.data.labels.length === 1;

    const select = document.querySelector(`#${tag_id}`).closest('.o-wrap').querySelector('.js-unitTimeSelect');
    if (select) {
        const selectedItem = select.querySelector('.wps-unit-time-chart__selected-item');
        if (selectedItem) {
            selectedItem.textContent = getDisplayTextForUnitTime(unitTime, tag_id);
        }
        const options = select.querySelectorAll('.wps-unit-time-chart__option');
        options.forEach(opt => {
            if (opt.getAttribute('data-value') === unitTime) {
                opt.classList.add('selected');
            } else {
                opt.classList.remove('selected');
            }
        });
    }


    const day = aggregateData(realdata.data.labels, realdata.data.datasets, 'day', momentDateFormat, isInsideDashboardWidgets);
    const week = aggregateData(realdata.data.labels, realdata.data.datasets, 'week', momentDateFormat, isInsideDashboardWidgets);
    const month = aggregateData(realdata.data.labels, realdata.data.datasets, 'month', momentDateFormat, isInsideDashboardWidgets);

    const prevDay = realdata?.previousData
        ? aggregateData(realdata.previousData.labels, realdata.previousData.datasets, 'day', momentDateFormat, isInsideDashboardWidgets)
        : null;
    const prevWeek = realdata.previousData
        ? aggregateData(realdata.previousData.labels, realdata.previousData.datasets, 'week', momentDateFormat, isInsideDashboardWidgets)
        : null;
    const prevMonth = realdata.previousData
        ? aggregateData(realdata.previousData.labels, realdata.previousData.datasets, 'month', momentDateFormat, isInsideDashboardWidgets)
        : null;

    // Initialize dateLabels based on the selected unitTime
    let dateLabels = unitTime === 'day'
        ? day.aggregatedLabels
        : unitTime === 'week'
            ? week.aggregatedLabels
            : month.aggregatedLabels;

// Initialize monthTooltip and prevMonthTooltip
    let monthTooltip = unitTime === 'day'
        ? day.monthTooltipTitle
        : unitTime === 'week'
            ? week.monthTooltipTitle
            : month.monthTooltipTitle;

    let prevMonthTooltip = unitTime === 'day'
        ? (prevDay ? prevDay.monthTooltipTitle : [])
        : unitTime === 'week'
            ? (prevWeek ? prevWeek.monthTooltipTitle : [])
            : (prevMonth ? prevMonth.monthTooltipTitle : []);
    let prevDateLabels = [];
    let prevAggregatedData = [];

    if (prevWeek) {
        prevDateLabels = prevWeek.aggregatedLabels;
        prevAggregatedData = prevWeek.aggregatedData;
        while (prevDateLabels.length < dateLabels.length) {
            prevDateLabels.push("N/A");
            prevAggregatedData.forEach(dataset => dataset.push(0));
        }
    } else {
        prevDateLabels = Array(dateLabels.length).fill("N/A");
        if (datasets && datasets.length > 0 && Array.isArray(datasets)) {
            prevAggregatedData = datasets.map(() => Array(dateLabels.length).fill(0));
        }
    }

    function updateChart(unitTime) {
         const displayText = getDisplayTextForUnitTime(unitTime, tag_id);
        const chartElement = document.getElementById(tag_id);
        const chartContainer = chartElement.parentElement.parentElement.querySelector('.wps-postbox-chart--data');
        const previousPeriodElement = chartContainer?.querySelector('.wps-postbox-chart--previousPeriod');
        if (previousPeriodElement) {
            previousPeriodElement.classList.remove('wps-line-through');
        }


        const select = document.querySelector(`#${tag_id}`).closest('.o-wrap').querySelector('.js-unitTimeSelect');
        if (select) {
            const selectedItem = select.querySelector('.wps-unit-time-chart__selected-item');
            if (selectedItem) {
                selectedItem.textContent = displayText;
            }
        }
        let aggregatedData, prevAggregatedData;
        switch (unitTime) {
            case 'day':
                aggregatedData = day;
                prevAggregatedData = prevDay;
                break;
            case 'week':
                aggregatedData = week;
                prevAggregatedData = prevWeek;
                break;
            case 'month':
                aggregatedData = month;
                prevAggregatedData = prevMonth;
                break;
            default:
                aggregatedData = day;
                prevAggregatedData = prevDay;
        }

        dateLabels = aggregatedData.aggregatedLabels;
        monthTooltip = aggregatedData.monthTooltipTitle;

        prevDateLabels = prevAggregatedData ? prevAggregatedData.aggregatedLabels : [];
        prevMonthTooltip = prevAggregatedData ? prevAggregatedData.monthTooltipTitle : [];

        // If prevDateLabels is empty, fill it with "N/A" for each month
        if (prevDateLabels.length === 0 && dateLabels.length > 0) {
            prevDateLabels = Array(dateLabels.length).fill("N/A");
        }


        const datasets = data.data.datasets.map((dataset, idx) => {
            const datasetType = dataset.type || (type === 'performance' && idx === 2 ? 'bar' : 'line');

            return {
                ...dataset,
                type: datasetType, // Set the type explicitly
                data: aggregatedData.aggregatedData[idx],
                borderColor: chartColors[data.data.datasets[idx].label] || chartColors[`Other${idx}`],
                backgroundColor: chartColors[data.data.datasets[idx].label] || chartColors[`Other${idx}`],
                fill: false,
                yAxisID: datasetType === 'bar' ? 'y1' : 'y', // Use y1 for bar, y for line
                borderWidth: datasetType === 'line' ? 2 : undefined,
                pointRadius: datasetType === 'line' ? dateLabels.length === 1 ? 5 : 0 : undefined,
                pointBorderColor: datasetType === 'line' ? 'transparent' : undefined,
                pointBackgroundColor: datasetType === 'line' ? chartColors[data.data.datasets[idx].label] || chartColors[`Other${idx}`] : undefined,
                pointBorderWidth: datasetType === 'line' ? 2 : undefined,
                hoverPointRadius: datasetType === 'line' ? 6 : undefined,
                hoverPointBorderColor: datasetType === 'line' ? '#fff' : undefined,
                hoverPointBackgroundColor: chartColors[data.data.datasets[idx].label] || chartColors[`Other${idx}`],
                hoverPointBorderWidth: datasetType === 'line' ? 4 : undefined,
                tension: datasetType === 'line' ? chartTensionValues[idx % chartTensionValues.length] : undefined,
                hitRadius: 10,
                meta: {
                    incompletePeriods: aggregatedData.isIncompletePeriod || []
                },
                segment: datasetType === 'line' ? {
                    borderDash: (ctx) => {
                        const incompletePeriods = ctx.chart.data.datasets[ctx.datasetIndex]?.meta?.incompletePeriods || [];
                        const currentIncomplete = incompletePeriods[ctx.p1DataIndex];
                        const previousIncomplete = incompletePeriods[ctx.p0DataIndex];

                        // Dash if either end of segment is in incomplete period
                        if (currentIncomplete || previousIncomplete) {
                            return [5, 5];
                        }
                        return undefined;
                    }
                } : undefined
            };
        });

        if (prevAggregatedData) {
            data.previousData.datasets.forEach((dataset, idx) => {
                datasets.push({
                    ...dataset,
                    type: 'line', // Previous datasets are always lines
                    label: `${dataset.label} (Previous)`,
                    data: prevAggregatedData.aggregatedData[idx],
                    borderColor: wps_js.hex_to_rgba(chartColors[dataset.label] || chartColors[`Other${idx}`], 0.7),
                    hoverBorderColor: chartColors[data.data.datasets[idx].label] || chartColors[`Other${idx}`],
                    backgroundColor: chartColors[data.data.datasets[idx].label] || chartColors[`Other${idx}`],
                    fill: false,
                    yAxisID: 'y',
                    borderWidth: 1,
                    borderDash: [5, 5],
                    pointRadius: aggregatedData.aggregatedLabels.length === 1 ? 5 : 0,
                    pointBorderColor: 'transparent',
                    pointBackgroundColor: chartColors[data.data.datasets[idx].label] || chartColors[`Other${idx}`],
                    pointBorderWidth: 2,
                    hoverPointRadius: 6,
                    hoverPointBorderColor: '#fff',
                    hoverPointBackgroundColor: chartColors[data.data.datasets[idx].label] || chartColors[`Other${idx}`],
                    hoverPointBorderWidth: 4,
                    tension: chartTensionValues[idx % chartTensionValues.length],
                    hitRadius: 10
                });
            });
        }
        lineChart.data.labels = dateLabels;
        lineChart.options.scales.x.offset = lineChart.data.labels.length === 1;
        lineChart.data.datasets = datasets;
        lineChart.options.scales.x.ticks.maxTicksLimit = isInsideDashboardWidgets
            ? unitTime === 'week'
                ? 2
                : 4
            : unitTime === 'week'
                ? 3
                : unitTime === 'month'
                    ? 7
                    : 9;
        lineChart.options.plugins.tooltip.unitTime = unitTime;
        lineChart.options.plugins.tooltip.external = (context) =>
            externalTooltipHandler(context, realdata, dateLabels, prevDateLabels, monthTooltip, prevMonthTooltip);
        updateLegend(lineChart, datasets, tag_id, data);
        lineChart.update();
    }


    let ctx_line = document.getElementById(tag_id).getContext('2d');


    Object.keys(data.data.datasets).forEach((key, index) => {
        let color = chartColors[data.data.datasets[key].label] || chartColors[`Other${index + 1}`];
        let tension = chartTensionValues[index % chartTensionValues.length];

        let datasetType = 'line'; // Default to line
        if (type === 'performance' && index === 2) {
            datasetType = 'bar'; // Set to bar for index 2 in performance charts
        }

        const dataset = {
            type: datasetType,
            label: data.data.datasets[key].label,
            data: data.data.datasets[key].data,
            backgroundColor: color,
            hoverBackgroundColor: color,
            hoverPointBackgroundColor: color,
            yAxisID: datasetType === 'bar' ? 'y1' : 'y', // Use y1 for bar, y for line
        };
        if (datasetType === 'line') {
            dataset.borderColor = color;
            dataset.fill = false;
            dataset.borderWidth = 2;
            dataset.pointRadius = 0;
            dataset.pointBorderColor = 'transparent';
            dataset.pointBackgroundColor = color;
            dataset.pointBorderWidth = 2;
            dataset.hoverPointRadius = 6;
            dataset.hoverPointBorderColor = '#fff';
            dataset.hoverPointBorderWidth = 4;
            dataset.tension = tension;
            dataset.hitRadius = 10;
        }
        datasets.push(dataset);

    });

    if (data?.previousData) {
        Object.keys(data.previousData.datasets).forEach((key, index) => {
            let color = chartColors[data.previousData.datasets[key].label] || chartColors[`Other${index}`];
            let tension = chartTensionValues[index % chartTensionValues.length];

            datasets.push({
                type: 'line',
                label: `${data.previousData.datasets[key].label} (Previous)`,
                data: data.previousData.datasets[key].data,
                borderColor: wps_js.hex_to_rgba(color, 0.7),
                hoverBorderColor: color,
                backgroundColor: color,
                fill: false,
                yAxisID: 'y',
                borderWidth: 1,
                borderDash: [5, 5],
                pointRadius: 0,
                pointBorderColor: 'transparent',
                pointBackgroundColor: color,
                pointBorderWidth: 2,
                hoverPointRadius: 6,
                hoverPointBorderColor: '#fff',
                hoverPointBackgroundColor: color,
                hoverPointBorderWidth: 4,
                tension: tension,
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
                external: (context) => externalTooltipHandler(context, realdata, dateLabels, prevDateLabels, monthTooltip, prevMonthTooltip),
                unitTime: unitTime, // Set initial unitTime
            },
        },
        scales: {
            x: {
                offset: isSingleDataPoint,
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
                    autoSkip: true,
                    maxTicksLimit: isInsideDashboardWidgets ? unitTime === 'week' ? 2 : 4 : unitTime === 'week' ? 3 : unitTime === 'month' ? 7 : 9,
                    font: {
                        color: '#898A8E',
                        style: 'italic',
                        weight: 'lighter',
                        size: isInsideDashboardWidgets ? (unitTime === 'week' ? 9 : 11) : (unitTime === 'week' ? 11 : 13)
                    },
                    padding: 8,
                }
            },
            y: {
                min: 0,
                suggestedMax: 4,
                ticks: {
                    maxTicksLimit: isInsideDashboardWidgets ? 4 : 7,
                    fontColor: '#898A8E',
                    fontSize: 13,
                    fontStyle: 'italic',
                    fontWeight: 'lighter ',
                    padding: 8,
                    lineHeight: 15,
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

    if (type === 'performance' && data.data.datasets.length > 2) {
        defaultOptions.scales.y1 = {
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
                lineHeight: 15,
                stepSize: 1
            },
            title: {
                display: true,
                text: `${wps_js._('published')} Posts`,
                color: '#898A8E',
                fontSize: 13
            }
        }

        defaultOptions.scales.y = {
            border: {
                color: 'transparent',
                width: 0
            },
            ticks: {
                maxTicksLimit: 9,
                fontColor: '#898A8E',
                fontSize: 13,
                fontStyle: 'italic',
                fontFamily: '"Roboto",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif',
                fontWeight: 'lighter ',
                padding: 8,
                lineHeight: 15,
                stepSize: 1
            },
            position: 'right',
            grid: {
                display: true,
                borderDash: [5, 5],
                tickColor: '#EEEFF1',
                color: '#EEEFF1'
            },
            title: {
                display: true,
                text: wps_js._('visits'),
                color: '#898A8E',
                fontSize: 13,
            }
        }
    }

    const lineChart = new Chart(ctx_line, {
        type: type === 'performance' && data.data.datasets.length > 2 ? 'bar' : 'line',
        data: {
            labels: [],
            datasets: [],
        },
        plugins: [drawVerticalLinePlugin],
        options: Object.assign({}, defaultOptions, newOptions)
    });

    // Example usage:
    updateChart(unitTime);
    chartInstances[tag_id] = {
        chart: lineChart,
        updateChart: updateChart,
    };


    return chartInstances[tag_id];
};


document.body.addEventListener('click', function (event) {
    const select = event.target.closest('.wps-unit-time-chart__selected-item');
    const option = event.target.closest('.wps-unit-time-chart__option');

    if (select) {
        if (!option) {
            document.querySelectorAll('.js-unitTimeSelect.open').forEach(openSelect => {
                if (openSelect !== select) {
                    openSelect.parentElement.classList.remove('open');
                }
            });
            select.parentElement.classList.toggle('open');
        }
        event.stopImmediatePropagation();
    } else if (option) {
        const select = option.closest('.js-unitTimeSelect');
        const selectedValue = option.getAttribute('data-value');

        if (select) {
            const selectedItem = select.querySelector('.wps-unit-time-chart__selected-item');
            if (selectedItem) {
                selectedItem.textContent = option.textContent.trim();
            }
            const options = select.querySelectorAll('.wps-unit-time-chart__option');
            options.forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');

            select.classList.remove('open');
            const chartContainer = select.closest('.o-wrap').querySelector('.wps-postbox-chart--container');
            const canvas = chartContainer.querySelector('canvas');
            const canvas_id = canvas.getAttribute('id');
            if (chartInstances[canvas_id]) {
                chartInstances[canvas_id].updateChart(selectedValue);
            }
        }

        event.stopImmediatePropagation();
    } else {
        document.querySelectorAll('.js-unitTimeSelect.open').forEach(openSelect => {
            openSelect.classList.remove('open');
        });
    }
});