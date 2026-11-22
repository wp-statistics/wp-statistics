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
const formatNumChart = (value) => {
    return wps_js.formatNumber(value);
}
const wpsBuildTicks = (scale) => {
    const ticks = scale.getTicks();
    if (ticks.length > scale.options.ticks.maxTicksLimit) {
        const range = scale.max - scale.min;
        const desiredTicks = scale.options.ticks.maxTicksLimit || 1000;
        let stepSize = Math.ceil(range / desiredTicks);
        scale.options.ticks.stepSize = Math.max(1, stepSize);
    } else if (!scale.options.ticks.stepSize || scale.options.ticks.stepSize < 1) {
        scale.options.ticks.stepSize = 1;
    }
}

const chartColors = {
    'total': '#27A765', 'views': '#7362BF', 'visitors': '#3288D7', 'user-visitors': '#3288D7', 'anonymous-visitors': '#7362BF', 'published': '#8AC3D0','published-contents': '#8AC3D0', 'published-products': '#8AC3D0',
    'published-pages': '#8AC3D0', 'published-posts': '#8AC3D0', 'posts': '#8AC3D0' , downloads: '#3288D7', 'clicks': '#3288D7', 'impressions': '#7362BF', 'Other1': '#3288D7', 'Other2': '#7362BF', 'Other3': '#8AC3D0'
};

const chartTensionValues = [0.1, 0.3, 0.5, 0.7];

// A dataset's own colour (`color`, e.g. a source category's fixed colour from
// SourceChannels::getStyles(), so chart lines match the widget icons), else its slug colour,
// else a colour by position.
const wpsDatasetColor = (dataset) => {
    if (dataset && typeof dataset.color === 'string' && /^#[0-9a-f]{3,8}$/i.test(dataset.color)) {
        return dataset.color;
    }
    return chartColors[dataset && dataset.slug] || chartColors[`Other${(dataset && dataset.originalIndex) + 1}`];
};
if (typeof wps_js !== 'undefined') {
    wps_js.datasetColor = wpsDatasetColor;
}

// ---------------------------------------------------------------------------
// Chart tooltip (V1-B): a small "Now | Before | Change" table, the title written per
// interval ("Thu, Nov 19", "Sep 28 – Oct 4", "August 2026"), the previous period named
// once in a footer ("Compared with …"). One builder for every line and bar chart drawn by
// wps_js.new_line_chart (Data Plus and Marketing too, via renderWPSLineChart) and for
// Traffic by Hour. Markup: .wps-chart-tooltip > .wps-chart-tooltip__title + table + __footer.
// ---------------------------------------------------------------------------

const getOrCreateTooltip = (chart) => {
    const parent = chart.canvas.parentNode;
    let tooltipEl = parent.querySelector(':scope > .wps-chart-tooltip') || parent.querySelector('div');
    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        parent.appendChild(tooltipEl);
    }
    if (!tooltipEl.classList.contains('wps-chart-tooltip')) {
        tooltipEl.classList.add('wps-chart-tooltip');
    }
    tooltipEl.setAttribute('role', 'tooltip');
    tooltipEl.style.pointerEvents = 'none';
    tooltipEl.style.position = 'absolute';
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
    // Next to the point, on the side with room (flips to the left near the right edge).
    let tooltipX = chartLeft + caretX + margin;
    let tooltipY = chartTop + caretY - tooltipHeight / 2;

    if (tooltipX + tooltipWidth + 8 > chartLeft + chartWidth) {
        tooltipX = chartLeft + caretX - tooltipWidth - margin;
    }

    if (tooltipX < chartLeft + 8) {
        tooltipX = chartLeft + 8;
    }

    if (tooltipY < chartTop + 8) {
        tooltipY = chartTop + 8;
    }
    if (tooltipY + tooltipHeight + 8 > chartTop + chartHeight) {
        tooltipY = Math.max(chartTop + 8, chartTop + chartHeight - tooltipHeight - 8);
    }
    tooltipEl.style.opacity = 1;
    tooltipEl.style.left = tooltipX + 'px';
    tooltipEl.style.top = tooltipY + 'px';
}

// The user's language for numbers and dates ("fa-IR" gives Persian digits and month names).
const wpsTooltipLocale = () => {
    const lang = (typeof document !== 'undefined' && document.documentElement && document.documentElement.lang) || '';
    try {
        return lang ? Intl.getCanonicalLocales(lang)[0] : 'en-US';
    } catch (e) {
        return 'en-US';
    }
};

const wpsTooltipText = (key, fallback) => {
    const text = (typeof wps_js !== 'undefined' && wps_js._) ? wps_js._(key) : '';
    return text || fallback;
};

const wpsTooltipStrings = () => ({
    now: wpsTooltipText('tooltip_now', 'Now'),
    before: wpsTooltipText('tooltip_before', 'Before'),
    change: wpsTooltipText('tooltip_change', 'Change'),
    comparedWith: wpsTooltipText('compared_with', 'Compared with'),
    comparedPrevious: wpsTooltipText('compared_with_previous', 'Compared with the previous period'),
    soFar: wpsTooltipText('so_far', 'so far'),
    partMonth: wpsTooltipText('part_of_month', 'part of month'),
    partWeek: wpsTooltipText('part_of_week', 'part of week'),
    newValue: wpsTooltipText('new_value', 'New'),
    noDataFor: wpsTooltipText('no_data_for', 'No data for'),
});

const wpsNumber = (value, locale) => {
    try {
        return new Intl.NumberFormat(locale || wpsTooltipLocale()).format(value);
    } catch (e) {
        return String(value);
    }
};

// Signed change in percent, rounded: null when there is nothing to compare, 'new' when before was 0.
const wpsChangePercent = (now, before) => {
    if (now === null || now === undefined || before === null || before === undefined || isNaN(now) || isNaN(before)) {
        return null;
    }
    now = Number(now);
    before = Number(before);
    if (before === 0) {
        return now > 0 ? 'new' : 0;
    }
    return Math.round((now - before) / before * 100);
};

const wpsDays = (start, end) => moment(end, 'YYYY-MM-DD').diff(moment(start, 'YYYY-MM-DD'), 'days') + 1;

// Bucket title + tag, written per interval. bucket: {start, end, partial, current} (YYYY-MM-DD).
const wpsBucketTitle = (bucket, unit, opts = {}) => {
    const locale = opts.locale || wpsTooltipLocale();
    const strings = opts.strings || wpsTooltipStrings();
    const today = opts.today || wpsChartToday();
    const isPrevious = !!opts.isPrevious;
    const toDate = (s) => new Date(s + 'T00:00:00Z');
    const withYear = String(bucket.start).slice(0, 4) !== String(today).slice(0, 4) || String(bucket.end).slice(0, 4) !== String(bucket.start).slice(0, 4);
    const fmt = (options) => {
        const base = Object.assign({timeZone: 'UTC', calendar: 'gregory'}, options);
        try {
            return new Intl.DateTimeFormat(locale, base);
        } catch (e) {
            return new Intl.DateTimeFormat('en-US', base);
        }
    };
    const range = (options) => {
        const f = fmt(options);
        if (bucket.start === bucket.end) return f.format(toDate(bucket.start));
        try {
            return f.formatRange(toDate(bucket.start), toDate(bucket.end));
        } catch (e) {
            return f.format(toDate(bucket.start)) + ' – ' + f.format(toDate(bucket.end));
        }
    };
    const year = withYear ? {year: 'numeric'} : {};
    let title, tag = '';

    if (unit === 'day') {
        title = fmt(Object.assign({weekday: 'short', month: 'short', day: 'numeric'}, year)).format(toDate(bucket.start));
        if (bucket.current) tag = strings.soFar;
    } else if (unit === 'week') {
        title = range(Object.assign({month: 'short', day: 'numeric'}, year));
        if (bucket.current) tag = strings.soFar;
        else if (bucket.partial) tag = strings.partWeek;
    } else if (bucket.current || (isPrevious && opts.asRange)) {
        // This month so far ("Nov 1 – 22"), and the same days of the month it is compared with ("May 1 – 22, 2026").
        title = range(Object.assign({month: 'short', day: 'numeric'}, isPrevious ? {year: 'numeric'} : year));
        tag = bucket.current ? strings.soFar : '';
    } else if (bucket.partial && wpsDays(bucket.start, bucket.end) < 28 && String(bucket.start).slice(0, 7) !== String(bucket.end).slice(0, 7)) {
        // A previous "month" made of the same days (shifted range): write the dates.
        title = range({month: 'short', day: 'numeric', year: 'numeric'});
    } else {
        title = fmt({month: isPrevious ? 'short' : 'long', year: 'numeric'}).format(toDate(bucket.start));
        if (bucket.partial) tag = strings.partMonth;
    }

    return {title, tag};
};

// Previous-period buckets that line up with the current ones (same count, same lengths).
// Month: the calendar months of the previous range when the count matches (May vs Nov),
// otherwise (and for weeks) the current buckets moved back by the range length, so every
// previous day is counted once and the last previous week is never dropped (13 vs 14).
const wpsAlignPrevious = (currentBuckets, prevLabels, prevDatasets, unit, prevAggregated) => {
    const empty = {buckets: [], data: (prevDatasets || []).map(() => [])};
    if (!currentBuckets || !currentBuckets.length || !prevLabels || !prevLabels.length || !prevDatasets) {
        return empty;
    }
    if (unit === 'day' || (unit === 'month' && prevAggregated && prevAggregated.buckets && prevAggregated.buckets.length === currentBuckets.length)) {
        return {
            buckets: prevAggregated.buckets.map(b => Object.assign({}, b, {current: false})),
            data: prevAggregated.aggregatedData.map(values => values.map(v => (v === null ? 0 : v))),
        };
    }
    const shift = moment(currentBuckets[0].start, 'YYYY-MM-DD').diff(moment(prevLabels[0].date, 'YYYY-MM-DD'), 'days');
    const byDate = {};
    prevLabels.forEach((label, i) => { if (label && label.date) byDate[label.date] = i; });
    const buckets = currentBuckets.map(b => {
        const start = moment(b.start, 'YYYY-MM-DD').subtract(shift, 'days');
        const end = moment(b.end, 'YYYY-MM-DD').subtract(shift, 'days');
        const length = end.diff(start, 'days') + 1;
        const full = unit === 'week' ? length >= 7 : (start.date() === 1 && end.isSame(end.clone().endOf('month'), 'day'));
        return {start: start.format('YYYY-MM-DD'), end: end.format('YYYY-MM-DD'), partial: !full, current: false};
    });
    const data = prevDatasets.map(dataset => buckets.map(b => {
        let total = 0;
        const day = moment(b.start, 'YYYY-MM-DD');
        const end = moment(b.end, 'YYYY-MM-DD');
        while (day.isSameOrBefore(end, 'day')) {
            const i = byDate[day.format('YYYY-MM-DD')];
            if (i !== undefined) total += Number((dataset.data || [])[i]) || 0;
            day.add(1, 'day');
        }
        return total;
    }));
    return {buckets, data};
};

const wpsEscape = (value) => (typeof wps_js !== 'undefined' && wps_js.escapeHtml) ? wps_js.escapeHtml(String(value)) : String(value).replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));

// model: {title, tag, rows: [{label, color, now, before, hasBefore, change, bar}], footer}
const wpsTooltipHtml = (model, opts = {}) => {
    const locale = opts.locale || wpsTooltipLocale();
    const strings = opts.strings || wpsTooltipStrings();
    const anyBefore = model.rows.some(row => row.hasBefore);
    const dash = '<span class="wps-chart-tooltip__none" aria-hidden="true">—</span>';
    const percent = (value) => {
        try {
            return new Intl.NumberFormat(locale, {style: 'percent', maximumFractionDigits: 0}).format(Math.abs(value) / 100);
        } catch (e) {
            return Math.abs(value) + '%';
        }
    };
    const chip = (change) => {
        if (change === null || change === undefined) return dash;
        if (change === 'new') return `<span class="wps-change wps-change--flat wps-chart-tooltip__new">${wpsEscape(strings.newValue)}</span>`;
        if (change === 0) return `<span class="wps-change wps-change--flat">${percent(0)}</span>`;
        const dir = change > 0 ? 'up' : 'down';
        return `<span class="wps-change wps-change--${dir} wps-change--${change > 0 ? 'good' : 'bad'}">${percent(change)}</span>`;
    };
    let html = `<div class="wps-chart-tooltip__title"><span>${wpsEscape(model.title)}</span>${model.tag ? `<span class="wps-chart-tooltip__tag">${wpsEscape(model.tag)}</span>` : ''}</div>`;
    html += '<table class="wps-chart-tooltip__table"><thead><tr><th scope="col"><span class="screen-reader-text">&nbsp;</span></th>';
    html += `<th scope="col">${wpsEscape(strings.now)}</th>`;
    if (anyBefore) html += `<th scope="col">${wpsEscape(strings.before)}</th><th scope="col">${wpsEscape(strings.change)}</th>`;
    html += '</tr></thead><tbody>';
    model.rows.forEach(row => {
        const now = row.now === null || row.now === undefined ? dash : wpsNumber(row.now, locale);
        html += `<tr><th scope="row"><span class="wps-chart-tooltip__metric"><span class="wps-chart-tooltip__swatch${row.bar ? ' wps-chart-tooltip__swatch--bar' : ''}" style="--wps-swatch:${wpsEscape(row.color || '#8C8F94')}"></span>${wpsEscape(row.label)}</span></th>`;
        html += `<td class="wps-chart-tooltip__now">${now}</td>`;
        if (anyBefore) {
            html += `<td class="wps-chart-tooltip__before">${row.hasBefore && row.before !== null && row.before !== undefined ? wpsNumber(row.before, locale) : dash}</td>`;
            html += `<td class="wps-chart-tooltip__change">${row.hasBefore ? chip(row.change) : dash}</td>`;
        }
        html += '</tr>';
    });
    html += '</tbody></table>';
    if (anyBefore && model.footer) {
        html += `<div class="wps-chart-tooltip__footer"><span class="wps-chart-tooltip__swatch wps-chart-tooltip__swatch--dashed" aria-hidden="true"></span><span>${wpsEscape(model.footer)}</span></div>`;
    }
    return html;
};

// Build the model for one point of a wps_js.new_line_chart chart (state in chart.$wps).
const wpsLineTooltipModel = (chart, dataIndex, opts = {}) => {
    const state = chart.$wps || {};
    const strings = opts.strings || wpsTooltipStrings();
    const locale = opts.locale || wpsTooltipLocale();
    const today = opts.today || wpsChartToday();
    const unit = state.unit || 'day';
    const bucket = (state.buckets || [])[dataIndex] || {start: today, end: today};
    const prevBucket = (state.prevBuckets || [])[dataIndex];
    const datasets = chart.data.datasets;
    const head = wpsBucketTitle(bucket, unit, {locale, strings, today});
    let comparable = true;
    if (bucket.current) {
        comparable = unit === 'month' && !!prevBucket && wpsDays(prevBucket.start, prevBucket.end) === wpsDays(bucket.start, moment.min(moment(bucket.end, 'YYYY-MM-DD'), moment(today, 'YYYY-MM-DD')).format('YYYY-MM-DD'));
    }
    const rows = [];
    datasets.forEach((dataset, index) => {
        if (!dataset || String(dataset.label).includes('(Previous)')) return;
        const meta = chart.getDatasetMeta(index);
        if (meta && meta.hidden) return;
        const now = dataset.data[dataIndex];
        const prevIndex = datasets.findIndex(ds => ds && ds.label === `${dataset.label} (Previous)`);
        const prevMeta = prevIndex > -1 ? chart.getDatasetMeta(prevIndex) : null;
        const hasBefore = prevIndex > -1 && !!prevBucket && !(prevMeta && prevMeta.hidden);
        const before = hasBefore && !state.prevNoData ? datasets[prevIndex].data[dataIndex] : null;
        rows.push({
            label: dataset.label,
            color: dataset.hoverPointBackgroundColor || dataset.borderColor || dataset.backgroundColor,
            bar: dataset.type === 'bar',
            now: now === undefined ? null : now,
            before: before === undefined ? null : before,
            hasBefore,
            change: hasBefore && !state.prevNoData && comparable ? wpsChangePercent(now, before) : null,
        });
    });
    let footer = '';
    if (prevBucket) {
        const prevHead = wpsBucketTitle(prevBucket, unit, {locale, strings, today, isPrevious: true, asRange: !!bucket.current});
        footer = state.prevNoData
            ? `${strings.noDataFor} ${prevHead.title}`
            : `${strings.comparedWith} ${prevHead.title}${prevHead.tag ? ` (${prevHead.tag})` : ''}`;
    }
    return {title: head.title, tag: head.tag, rows, footer};
};

const wpsShowTooltip = (chart, tooltip, model) => {
    const tooltipEl = getOrCreateTooltip(chart);
    if (tooltip.opacity === 0 || !model || !model.rows.length) {
        tooltipEl.style.opacity = 0;
        tooltipEl.setAttribute('aria-hidden', 'true');
        return;
    }
    tooltipEl.dir = (typeof document !== 'undefined' && document.documentElement && document.documentElement.dir) || (wps_js.isset && wps_js.isset(wps_js.global, 'options', 'rtl') && wps_js.global.options.rtl == 1 ? 'rtl' : 'ltr');
    tooltipEl.innerHTML = wpsTooltipHtml(model);
    tooltipEl.setAttribute('aria-hidden', 'false');
    wps_js.setTooltipPosition(tooltipEl, chart, tooltip);
};

const externalTooltipHandler = (context) => {
    const {chart, tooltip} = context;
    if (tooltip.opacity === 0 || !tooltip.dataPoints || !tooltip.dataPoints.length) {
        wpsShowTooltip(chart, tooltip, null);
        return;
    }
    wpsShowTooltip(chart, tooltip, wpsLineTooltipModel(chart, tooltip.dataPoints[0].dataIndex));
};

// Keyboard: the chart takes focus, ← → (Home / End) move between points, Esc hides.
wps_js.chartKeyboard = function (chart) {
    const canvas = chart && chart.canvas;
    if (!canvas || canvas.dataset.wpsKeys) return;
    canvas.dataset.wpsKeys = '1';
    canvas.setAttribute('tabindex', '0');
    if (!canvas.getAttribute('role')) canvas.setAttribute('role', 'img');
    if (!canvas.getAttribute('aria-label')) {
        const names = (chart.data.datasets || []).filter(ds => ds && !String(ds.label).includes('(Previous)')).map(ds => ds.label);
        if (names.length) canvas.setAttribute('aria-label', names.join(', '));
    }
    const hide = () => {
        chart.$wpsKeyIndex = -1;
        chart.setActiveElements([]);
        chart.tooltip.setActiveElements([], {x: 0, y: 0});
        chart.update('none');
    };
    canvas.addEventListener('keydown', (event) => {
        const count = (chart.data.labels || []).length;
        if (!count) return;
        let index = typeof chart.$wpsKeyIndex === 'number' ? chart.$wpsKeyIndex : -1;
        if (event.key === 'ArrowRight') index = index < 0 ? 0 : Math.min(count - 1, index + 1);
        else if (event.key === 'ArrowLeft') index = index < 0 ? count - 1 : Math.max(0, index - 1);
        else if (event.key === 'Home') index = 0;
        else if (event.key === 'End') index = count - 1;
        else if (event.key === 'Escape') { hide(); return; }
        else return;
        event.preventDefault();
        chart.$wpsKeyIndex = index;
        const active = [];
        chart.data.datasets.forEach((dataset, datasetIndex) => {
            const meta = chart.getDatasetMeta(datasetIndex);
            if (!meta.hidden && meta.data && meta.data[index]) active.push({datasetIndex, index});
        });
        if (!active.length) return;
        const point = chart.getDatasetMeta(active[0].datasetIndex).data[index];
        chart.setActiveElements(active);
        chart.tooltip.setActiveElements(active, {x: point.x, y: point.y});
        chart.update('none');
    });
    canvas.addEventListener('blur', hide);
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

    const baseFormat = momentDateFormat
        .replace(/\/YYYY|YYYY/g, '')
        .replace(/\/YY|YY/g, '')
        .replace(/[,/\s-]/g, ' ')
        .trim();
    const cleanFormat = baseFormat
        .replace(/MM|MMM/g, 'MMM')
        .replace(/DD|D/g, 'D')
        .trim();

    if (unit === 'month') {
        const monthFormat = cleanFormat
            .replace(/\s*D/g, '')
            .replace(/YYYY|YY/g, '')
            .replace(/\/YY|YY/g, '')
            .trim();
        return moment(startDate).format(monthFormat);
    } else {
        return `${moment(startDate).format(cleanFormat)} ${wps_js._('to_range')} ${moment(endDate).format(cleanFormat)}`;
    }
}

const setMonthDateRange = (startDate, endDate, momentDateFormat) => {
    const startDateFormat = momentDateFormat.replace(/,?\s?(YYYY|YY)[-/\s]?,?|[-/\s]?(YYYY|YY)[-/\s]?,?/g, "");
    return `${moment(startDate).format(startDateFormat)} ${wps_js._('to_range')} ${moment(endDate).format(startDateFormat)}`;
}

// Today's date (YYYY-MM-DD) in the site's time zone, so "future" matches the server's day.
const wpsChartToday = () => {
    const tz = (typeof wps_js !== 'undefined' && wps_js.isset && wps_js.isset(wps_js.global, 'options', 'wp_timezone')) ? wps_js.global['options']['wp_timezone'] : null;
    if (tz) {
        try {
            if (/^(UTC)?[+-]\d/.test(tz)) {
                return moment().utcOffset(tz.replace('UTC', '')).format('YYYY-MM-DD');
            }
            const parts = new Intl.DateTimeFormat('en-CA', {timeZone: tz, year: 'numeric', month: '2-digit', day: '2-digit'}).formatToParts(new Date());
            const get = type => (parts.find(p => p.type === type) || {}).value;
            if (get('year') && get('month') && get('day')) {
                return `${get('year')}-${get('month')}-${get('day')}`;
            }
        } catch (e) {
            // Unknown zone name: fall back to the browser's day.
        }
    }
    return moment().format('YYYY-MM-DD');
}

const aggregateData = (labels, datasets, unit, momentDateFormat, isInsideDashboardWidgets) => {
    if (!labels || !labels.length || !datasets || !datasets.length) {
        return {
            aggregatedLabels: [],
            aggregatedData: datasets ? datasets.map(() => []) : [],
            monthTooltipTitle: [],
            buckets: [],
        };
    }
    const isIncompletePeriod = [];
    // One entry per point: {start, end, partial, current} (YYYY-MM-DD), for the tooltip titles and tags.
    const buckets = [];
    const ymd = (m) => moment(m).format('YYYY-MM-DD');
    const holdsToday = (start, end) => !moment(start).isAfter(now, 'day') && !moment(end).isBefore(now, 'day');
    const now = moment(wpsChartToday(), 'YYYY-MM-DD');
    // A period that starts after today has no data yet: draw no point (null), not 0.
    const isFuture = (date) => moment(date).isAfter(now, 'day');
    const aggregatedLabels = [];
    const aggregatedData = datasets.map(() => []);
    const monthTooltipTitle = [];


    if (unit === 'day') {
        labels.forEach(label => {
            const date = moment(label.date);
            isIncompletePeriod.push(date.isSameOrAfter(now, 'day'));
        });
        labels.forEach(label => {
            buckets.push({start: label.date, end: label.date, partial: false, current: !!label.date && moment(label.date).isSame(now, 'day')});
        });
        return {
            buckets,
            aggregatedLabels: labels.map(label => label.formatted_date || 'N/A'),
            aggregatedData: datasets.map(dataset => (dataset.data || []).map((value, i) => labels[i] && labels[i].date && isFuture(labels[i].date) ? null : value)),
            monthTooltipTitle: labels.map(label => label.formatted_date || 'N/A'),
            isIncompletePeriod
        };
    }

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
            const startLabel = labels.find((label, j) => moment(label.date).isSame(week.start, 'day'));
            const endLabel = labels.find((label, j) => moment(label.date).isSame(week.end, 'day'));
            let label, tooltipLabel;

            if (startLabel && endLabel) {
                label = `${startLabel.formatted_date} ${wps_js._('to_range')} ${endLabel.formatted_date}`;
                tooltipLabel = `${startLabel.formatted_date} ${wps_js._('to_range')} ${endLabel.formatted_date}`;
            } else {
                label = formatDateRange(week.start, week.end, unit, momentDateFormat, isInsideDashboardWidgets);
                tooltipLabel = label;
            }

            aggregatedLabels.push(label);
            monthTooltipTitle.push(tooltipLabel);

            const weekIsFuture = isFuture(week.start);
            const weekCurrent = holdsToday(week.start, week.end);
            const weekLength = week.end.diff(week.start, 'days') + 1;
            buckets.push({start: ymd(week.start), end: ymd(week.end), partial: !weekCurrent && weekLength < 7, current: weekCurrent});
            week.data.forEach((total, datasetIndex) => {
                if (!aggregatedData[datasetIndex]) {
                    aggregatedData[datasetIndex] = [];
                }
                aggregatedData[datasetIndex].push(weekIsFuture ? null : total);
            });
        });

        weeks.forEach(week => {
            const isIncomplete = week.end.isSameOrAfter(now, 'day');
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

        Object.keys(groupedData).forEach(monthKey => {
            const {startDate, endDate, indices} = groupedData[monthKey];
            const actualStartDate = moment.max(startDate, moment(labels[0].date));
            const actualEndDate = moment.min(endDate, moment(labels[labels.length - 1].date));
            if (!actualStartDate.isValid() || !actualEndDate.isValid()) {
                return;
            }
            if (indices.length > 0) {
                const startLabel = labels.find(label => moment(label.date).isSame(actualStartDate, 'day'));
                const endLabel = labels.find(label => moment(label.date).isSame(actualEndDate, 'day'));
                let label, tooltipLabel;
                if (startLabel && endLabel) {
                    label = startLabel.month_i18n || formatDateRange(actualStartDate, actualEndDate, unit, momentDateFormat, isInsideDashboardWidgets);
                    tooltipLabel = `${startLabel.formatted_date} ${wps_js._('to_range')} ${endLabel.formatted_date}`;
                } else {
                    label = formatDateRange(actualStartDate, actualEndDate, unit, momentDateFormat, isInsideDashboardWidgets);
                    tooltipLabel = label;
                }
                aggregatedLabels.push(label);
                monthTooltipTitle.push(tooltipLabel);
                const monthIsFuture = isFuture(actualStartDate);
                const monthCurrent = holdsToday(actualStartDate, actualEndDate);
                buckets.push({
                    start: ymd(actualStartDate),
                    end: ymd(actualEndDate),
                    partial: !monthCurrent && (!actualStartDate.isSame(startDate, 'day') || !actualEndDate.isSame(endDate, 'day')),
                    current: monthCurrent
                });
                datasets.forEach((dataset, idx) => {
                    const total = indices.reduce((sum, i) => sum + (dataset.data[i] || 0), 0);
                    aggregatedData[idx].push(monthIsFuture ? null : total);
                });
            }
            const isIncomplete = groupedData[monthKey].endDate.isSameOrAfter(now, 'day');
            isIncompletePeriod.push(isIncomplete);
        });

    }

    return {aggregatedLabels, aggregatedData, monthTooltipTitle, isIncompletePeriod, buckets};
}

const sortTotal = (datasets) => {
    // Store original indices before sorting
    datasets.forEach((dataset, index) => {
        dataset.originalIndex = index;
    });

    datasets.sort((a, b) => {
        if (a.slug === 'total') return -1;
        if (b.slug === 'total') return 1;
        if (a.slug === 'total-previous') return -1;
        if (b.slug === 'total-previous') return 1;
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
                    e.stopPropagation();
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
                <span>${wps_js.escapeHtml(dataset.label)}</span>
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
                            const allHaveLineThrough = Array.from(allPreviousData).every(el => el.classList.contains('wps-line-through'));

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

window.wpsCharts = [];
const chartInstances = {};

const getDisplayTextForUnitTime = (unitTime, tag_id) => {
    const select = document.querySelector(`#${tag_id}`).closest('.o-wrap').querySelector('.js-unitTimeSelect');
    if (select) {
        const option = select.querySelector(`.wps-unit-time-chart__option[data-value="${unitTime}"]`);
        if (option) {
            return option.textContent.trim();
        }
    }
    return unitTime.charAt(0).toUpperCase() + unitTime.slice(1); // Fallback to capitalized unitTime
}

// Function to determine available intervals based on date range
const getAvailableIntervals = (startDate, endDate) => {
    if (!moment(startDate).isValid() || !moment(endDate).isValid()) {
        return ['day'];
    }
    // Swap dates if endDate is before startDate
    if (moment(endDate).isBefore(moment(startDate))) {
        [startDate, endDate] = [endDate, startDate];
    }
    const duration = moment(endDate).diff(moment(startDate), 'days') + 1;
    const intervals = [];
    if (duration >= 1) intervals.push('day');
    if (duration >= 8) intervals.push('week');
    if (duration >= 31) intervals.push('month');
    return intervals;
}

// Function to select a valid interval
const selectValidInterval = (currentUnitTime, availableIntervals) => {
    return availableIntervals.includes(currentUnitTime) ? currentUnitTime : availableIntervals[0] || 'day';
}

// Function to update dropdown options
const updateIntervalDropdown = (tag_id, availableIntervals, selectedUnitTime) => {
    const select = document.querySelector(`#${tag_id}`).closest('.o-wrap').querySelector('.js-unitTimeSelect');
    if (!select) {
        return;
    }
    const optionsContainer = select.querySelector('.wps-unit-time-chart__dropdown');
    if (!optionsContainer) {
        return;
    }
    optionsContainer.innerHTML = '';
    const allOptions = [
        { value: 'day', text: wps_js._('daily') || 'Daily' },
        { value: 'week', text: wps_js._('weekly') || 'Weekly' },
        { value: 'month', text: wps_js._('monthly') || 'Monthly' }
    ];
    allOptions.forEach(opt => {
        if (availableIntervals.includes(opt.value)) {
            const option = document.createElement('div');
            option.className = 'wps-unit-time-chart__option';
            option.setAttribute('data-value', opt.value);
            option.textContent = opt.text;
            if (opt.value === selectedUnitTime) option.classList.add('selected');
            optionsContainer.appendChild(option);
        }
    });
    const selectedItem = select.querySelector('.wps-unit-time-chart__selected-item');
    if (selectedItem) {
        const selectedOption = allOptions.find(opt => opt.value === selectedUnitTime);
        selectedItem.textContent = selectedOption ? selectedOption.text : 'Daily';
    }
}

wps_js.new_line_chart = function (data, tag_id, newOptions = null, type = 'line') {

    if (wps_js.allDatasetsZero(data.data.datasets, data.previousData?.datasets)) {
        const chartElement = document.getElementById(tag_id);
        if (!chartElement) return null;

        const parentElement = chartElement.parentElement;
        if (!parentElement) return null;

        const wrap = parentElement.closest('.o-wrap');
        const chartData = wrap?.querySelector('.wps-postbox-chart--data');

        parentElement.innerHTML = wps_js.no_results();
        parentElement.classList.add('wps-no-result');
        if (chartData) chartData.style.display = 'none';
        return null;
    }

    sortTotal(data.data.datasets);
    const realdata = deepCopy(data);
    const phpDateFormat = wps_js.isset(wps_js.global, 'options', 'wp_date_format') ? wps_js.global['options']['wp_date_format'] : 'MM/DD/YYYY';
    let momentDateFormat = phpToMomentFormat(phpDateFormat);
    const isInsideDashboardWidgets = document.getElementById(tag_id).closest('#dashboard-widgets') !== null;

    // Calculate date range and determine available intervals
    const startDate = moment(data.data.labels[0]?.date);
    const endDate = moment(data.data.labels[data.data.labels.length - 1]?.date);
    if (!startDate.isValid() || !endDate.isValid()) {
        console.error("Invalid start or end date:", startDate, endDate);
    }
    const availableIntervals = getAvailableIntervals(startDate, endDate);

    // Determine the initial unitTime
    const length = data.data.labels.map(dateObj => dateObj.formatted_date).length;
    const threshold = type === 'performance' ? 30 : 60;
    let unitTime = length <= threshold ? 'day' : length <= 180 ? 'week' : 'month';
    unitTime = selectValidInterval(unitTime, availableIntervals);

    const datasets = [];
    const isSingleDataPoint = data.data.labels.length === 1;

    // Update dropdown options
    updateIntervalDropdown(tag_id, availableIntervals, unitTime);

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
    let dateLabels = unitTime === 'week'
            ? week.aggregatedLabels
            : month.aggregatedLabels;

// Initialize monthTooltip and prevMonthTooltip
    let monthTooltip =  unitTime === 'week'
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

    function updateChart(unitTime, newStartDate = startDate, newEndDate = endDate) {
        // Recalculate available intervals based on new date range
        const availableIntervals = getAvailableIntervals(newStartDate, newEndDate);
        unitTime = selectValidInterval(unitTime, availableIntervals);

        // Update dropdown options
        updateIntervalDropdown(tag_id, availableIntervals, unitTime);

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

        // Previous buckets aligned to the current ones (same count and lengths), and the per-chart
        // tooltip state: no document-wide lookups, so one chart never changes another's tooltip.
        const alignedPrevious = prevAggregatedData && realdata.previousData
            ? wpsAlignPrevious(aggregatedData.buckets, realdata.previousData.labels, realdata.previousData.datasets, unitTime, prevAggregatedData)
            : null;
        lineChart.$wps = {
            unit: unitTime,
            buckets: aggregatedData.buckets || [],
            prevBuckets: alignedPrevious ? alignedPrevious.buckets : [],
            prevNoData: !!(realdata.previousData && (realdata.previousData.datasets || []).every(ds => (ds.data || []).every(v => !Number(v))))
        };

        prevDateLabels = prevAggregatedData ? prevAggregatedData.aggregatedLabels : [];
        prevMonthTooltip = prevAggregatedData ? prevAggregatedData.monthTooltipTitle : [];

        // If prevDateLabels is empty, fill it with "N/A" for each month
        if (prevDateLabels.length === 0 && dateLabels.length > 0) {
            prevDateLabels = Array(dateLabels.length).fill("N/A");
        }

        const datasets = data.data.datasets.map((dataset, idx) => {
            let color = wpsDatasetColor(data.data.datasets[idx]);
            let tension = chartTensionValues[idx % chartTensionValues.length];

            let datasetType = 'line'; // Default to line
            if (type === 'performance' && idx === 2) {
                datasetType = 'bar'; // Set to bar for index 2 in performance charts
            }

            const yAxisID = dataset?.slug === 'clicks' || datasetType === 'bar' ? 'y1' : 'y';
            return {
                ...dataset,
                type: datasetType, // Set the type explicitly
                data: aggregatedData.aggregatedData[idx],
                borderColor: color,
                backgroundColor: color,
                fill: false,
                yAxisID: yAxisID,
                borderWidth: datasetType === 'line' ? 2 : undefined,
                pointRadius: datasetType === 'line' ? dateLabels.length === 1 ? 5 : 0 : undefined,
                pointBorderColor: datasetType === 'line' ? 'transparent' : undefined,
                pointBackgroundColor: datasetType === 'line' ? color : undefined,
                pointBorderWidth: datasetType === 'line' ? 2 : undefined,
                hoverPointRadius: datasetType === 'line' ? 6 : undefined,
                hoverPointBorderColor: datasetType === 'line' ? '#fff' : undefined,
                hoverPointBackgroundColor: color,
                hoverPointBorderWidth: datasetType === 'line' ? 4 : undefined,
                tension: datasetType === 'line' ? tension : undefined,
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
                const currentDataset = data.data.datasets.find(d => d.label === dataset.label);
                const slug = currentDataset?.slug || dataset.slug || `Other${idx + 1}`;
                let color = chartColors[slug] || chartColors[`Other${idx + 1}`];
                let tension = chartTensionValues[idx % chartTensionValues.length];

                datasets.push({
                    ...dataset,
                    type: 'line',
                    label: `${dataset.label} (Previous)`,
                    data: alignedPrevious && alignedPrevious.data[idx] ? alignedPrevious.data[idx] : prevAggregatedData.aggregatedData[idx],
                    borderColor: wps_js.hex_to_rgba(color, 0.7),
                    hoverBorderColor: color,
                    backgroundColor: color,
                    fill: false,
                    yAxisID: dataset?.slug === 'clicks' ? 'y1' : 'y',
                    borderWidth: 1,
                    borderDash: [5, 5],
                    pointRadius: aggregatedData.aggregatedLabels.length === 1 ? 5 : 0,
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

        // Calculate max values for y and y1 axes
        const yAxisData = datasets
            .filter(dataset => dataset.yAxisID === 'y')
            .flatMap(dataset => dataset.data)
            .filter(val => typeof val === 'number' && !isNaN(val));
        const y1AxisData = datasets
            .filter(dataset => dataset.yAxisID === 'y1')
            .flatMap(dataset => dataset.data)
            .filter(val => typeof val === 'number' && !isNaN(val));

        const yMax = yAxisData.length > 0 ? Math.max(...yAxisData) : 0;
        const y1Max = y1AxisData.length > 0 ? Math.max(...y1AxisData) : 0;

        // Set dynamic stepSize based on max values
        const maxTicksY = isInsideDashboardWidgets ? 4 : 7;
        const stepSizeY = yMax > 0 ? Math.ceil(yMax / maxTicksY) : 1;
        const suggestedMaxY = yMax > 0 ? stepSizeY * (maxTicksY + 1) : 4;

        const maxTicksY1 = 7;
        const stepSizeY1 = y1Max > 0 ? Math.ceil(y1Max / maxTicksY1) : 1;
        const suggestedMaxY1 = y1Max > 0 ? stepSizeY1 * (maxTicksY1 + 1) : 4;

        lineChart.options.scales.y.min = 0;
        lineChart.options.scales.y.ticks.stepSize = stepSizeY;
        lineChart.options.scales.y.max = suggestedMaxY;

        if (lineChart.options.scales.y1) {
            lineChart.options.scales.y1.min = 0;
            lineChart.options.scales.y1.ticks.stepSize = stepSizeY1;
            lineChart.options.scales.y1.max = suggestedMaxY1;
        }

        lineChart.data.labels = dateLabels;
        lineChart.options.scales.x.offset = lineChart.data.labels.length === 1;
        lineChart.data.datasets = datasets;
        const baseTicks = isInsideDashboardWidgets
            ? unitTime === 'week' ? 2 : 4
            : unitTime === 'week' ? 3 : unitTime === 'month' ? 7 : 9;
        const chartWidth = lineChart.width || (lineChart.canvas ? lineChart.canvas.clientWidth : 0) || 9999;
        lineChart.options.scales.x._wpsMaxTicks = baseTicks;
        lineChart.options.scales.x.ticks.maxTicksLimit = Math.max(2, Math.min(baseTicks, Math.floor(chartWidth / 80)));
        lineChart.options.plugins.tooltip.unitTime = unitTime;
        lineChart.options.plugins.tooltip.external = externalTooltipHandler;
        updateLegend(lineChart, datasets, tag_id, data);
        lineChart.update();
    }

    let ctx_line = document.getElementById(tag_id).getContext('2d');

    Object.keys(data.data.datasets).forEach((key, index) => {
        let color = wpsDatasetColor(data.data.datasets[key]);
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
            yAxisID: datasetType === 'bar' || data.data.datasets[key].label === 'Clicks' ? 'y1' : 'y', // Use y1 for bar, y for line
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
            const currentDataset = data.data.datasets.find(d => d.label === data.previousData.datasets[key].label);
            const slug = currentDataset?.slug || data.previousData.datasets[key].slug || `Other${index + 1}`;
            let color = chartColors[slug] || chartColors[`Other${index + 1}`];
            let tension = chartTensionValues[index % chartTensionValues.length];

            datasets.push({
                type: 'line',
                label: `${data.previousData.datasets[key].label} (Previous)`,
                data: data.previousData.datasets[key].data,
                borderColor: wps_js.hex_to_rgba(color, 0.7),
                hoverBorderColor: color,
                backgroundColor: color,
                fill: false,
                yAxisID: data.previousData.datasets[key].label === 'Clicks' ? 'y1' : 'y',
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
        animation: {duration: 0},
        responsive: true,
        interaction: {intersect: false, mode: 'index'},
        plugins: {
            legend: false,
            tooltip: {
                enabled: false,
                external: externalTooltipHandler,
                unitTime: unitTime, // Set initial unitTime
            },
        },
        scales: {
            x: {
                offset: isSingleDataPoint,
                grid: {display: false, drawBorder: false, tickLength: 0, drawTicks: false},
                border: {color: 'transparent', width: 0},
                ticks: {
                    // No align:'inner' (it pushes the first/last label inwards onto its neighbour);
                    // autoSkip keeps a gap between labels, wpsCompactAxes lowers the count on narrow cards.
                    autoSkip: true,
                    autoSkipPadding: 16,
                    maxRotation: 0,
                    maxTicksLimit: isInsideDashboardWidgets ? (unitTime === 'week' ? 2 : 4) : (unitTime === 'week' ? 3 : unitTime === 'month' ? 7 : 9),
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
                    autoSkip: true,
                    maxTicksLimit: isInsideDashboardWidgets ? 4 : 7,
                    fontColor: '#898A8E',
                    fontSize: 13,
                    fontStyle: 'italic',
                    fontWeight: 'lighter',
                    padding: 8,
                    lineHeight: 15,
                    callback: renderFormatNum,
                },
                afterBuildTicks: wpsBuildTicks,
                border: {color: 'transparent', width: 0},
                type: 'linear',
                position: 'right',
                grid: {display: true, tickMarkLength: 0, drawBorder: false, tickColor: '#EEEFF1', color: '#EEEFF1'},
                gridLines: {drawTicks: false},
                title: {display: false},
            }
        },
    };

    if (type === 'performance' && data.data.datasets.length > 2) {
        defaultOptions.scales.y1 = {
            type: 'linear',
            position: 'left',
            border: {color: 'transparent', width: 0},
            grid: {display: false, drawBorder: false, tickLength: 0},
            ticks: {
                autoSkip: true,
                maxTicksLimit: 7,
                fontColor: '#898A8E',
                fontSize: 13,
                fontStyle: 'italic',
                fontFamily: '"Roboto",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif',
                fontWeight: 'lighter',
                padding: 8,
                lineHeight: 15,
                callback: renderFormatNum,
            },
            afterBuildTicks: wpsBuildTicks,
            title: {
                display: true,
                // The published dataset's own label ("Published Products", "Published Pages"...), not a fixed "Posts".
                text: (function () {
                    const sets = Array.isArray(data.data.datasets) ? data.data.datasets : Object.values(data.data.datasets || {});
                    const published = sets.find(function (set) { return set && set.slug && String(set.slug).indexOf('published') === 0; });
                    return published && published.label ? published.label : `${wps_js._('published')} Posts`;
                })(),
                color: '#898A8E',
                fontSize: 13
            }
        };

        defaultOptions.scales.y = {
            border: {color: 'transparent', width: 0},
            ticks: {
                autoSkip: true,
                maxTicksLimit: 9,
                fontColor: '#898A8E',
                fontSize: 13,
                fontStyle: 'italic',
                fontFamily: '"Roboto",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif',
                fontWeight: 'lighter',
                padding: 8,
                lineHeight: 15,
                callback: renderFormatNum,
            },
            position: 'right',
            grid: {display: true, borderDash: [5, 5], tickColor: '#EEEFF1', color: '#EEEFF1'},
            title: {
                display: true,
                text: wps_js._('visits'),
                color: '#898A8E',
                fontSize: 13,
            }
        };
    }

    // Narrow cards (under 480px): drop the y-axis titles ("Published Posts", "Views") and use
    // fewer ticks, so the plot keeps its width and the labels do not crowd.
    const wpsCompactAxes = function (chart, width) {
        const narrow = width < 480;
        // Date labels need about 80px each ("Oct 24", "Nov 2026"): fewer of them on narrower cards.
        const x = chart.options.scales && chart.options.scales.x;
        if (x && x.ticks && x.type !== 'linear') {
            if (x._wpsMaxTicks === undefined) x._wpsMaxTicks = x.ticks.maxTicksLimit;
            if (x._wpsMaxTicks) x.ticks.maxTicksLimit = Math.max(2, Math.min(x._wpsMaxTicks, Math.floor(width / 80)));
        }
        ['y', 'y1'].forEach(function (key) {
            const scale = chart.options.scales && chart.options.scales[key];
            if (!scale) return;
            if (scale.title) {
                if (scale._wpsTitle === undefined) scale._wpsTitle = !!scale.title.display;
                scale.title.display = narrow ? false : scale._wpsTitle;
            }
            if (scale.ticks) {
                if (scale._wpsMaxTicks === undefined) scale._wpsMaxTicks = scale.ticks.maxTicksLimit;
                scale.ticks.maxTicksLimit = narrow ? 5 : scale._wpsMaxTicks;
            }
        });
    };

    const chartOptions = Object.assign({}, defaultOptions, newOptions);
    const userOnResize = chartOptions.onResize;
    chartOptions.onResize = function (chart, size) {
        wpsCompactAxes(chart, size.width); // Chart.js updates right after onResize
        if (typeof userOnResize === 'function') userOnResize(chart, size);
    };

    const lineChart = new Chart(ctx_line, {
        type: type === 'performance' && data.data.datasets.length > 2 ? 'bar' : 'line',
        data: {labels: [], datasets: []},
        plugins: [drawVerticalLinePlugin],
        options: chartOptions
    });
    wpsCompactAxes(lineChart, lineChart.width || (ctx_line.canvas ? ctx_line.canvas.clientWidth : 0) || 9999);

    updateChart(unitTime);
    wps_js.chartKeyboard(lineChart);
    chartInstances[tag_id] = {chart: lineChart, updateChart: updateChart};

    window.wpsCharts.push(lineChart);

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

document.body.addEventListener('change', function (event) {
    const datePicker = event.target.closest('.wps-date-picker');
    if (datePicker) {
        const chartContainer = datePicker.closest('.o-wrap').querySelector('.wps-postbox-chart--container');
        if (!chartContainer) return;
        const canvas = chartContainer.querySelector('canvas');
        const canvas_id = canvas.getAttribute('id');
        if (!chartInstances[canvas_id]) return;
        const startDateInput = datePicker.querySelector('.wps-date-picker__start');
        const endDateInput = datePicker.querySelector('.wps-date-picker__end');
        if (!startDateInput || !endDateInput) return;
        const startDate = moment(startDateInput.value);
        const endDate = moment(endDateInput.value);
        if (!startDate.isValid() || !endDate.isValid()) {
            return;
        }
        const currentUnitTime = chartInstances[canvas_id].chart.options.plugins.tooltip.unitTime;
        chartInstances[canvas_id].updateChart(currentUnitTime, startDate, endDate);
    }
});

window.renderWPSLineChart = function (chartId, data, newOptions) {
    const chartItem = document.getElementById(chartId);
    if (!chartItem) return;

    const parentElement = chartItem.parentElement;
    if (!parentElement) return;

    const placeholder = wps_js.rectangle_placeholder();
    parentElement.innerHTML += placeholder;

    const hasAllZeroData = wps_js.allDatasetsZero(data?.data?.datasets, data?.previousData?.datasets);
    const hasNoData = !data?.data?.datasets || data.data.datasets.length === 0;

    if (hasNoData || hasAllZeroData) {
        const wrap = parentElement.closest('.o-wrap');
        const chartData = wrap?.querySelector('.wps-postbox-chart--data');

        parentElement.innerHTML = wps_js.no_results();
        parentElement.classList.add('wps-no-result');
        document.querySelectorAll('.wps-ph-item').forEach(el => el.remove());

        if (chartData) chartData.style.display = 'none';
    } else {
        wps_js.new_line_chart(data, chartId, newOptions);
        document.querySelectorAll('.wps-ph-item').forEach(el => el.remove());
        document.querySelectorAll('.wps-postbox-chart--data')
            .forEach(el => el.classList.remove('c-chart__wps-skeleton--legend'));
        parentElement.classList.remove('c-chart__wps-skeleton', 'wps-no-result');
    }
}
