const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const momentSource = fs.readFileSync(require.resolve('moment/moment.js'), 'utf8');
const chartSource = fs.readFileSync('assets/dev/javascript/chart.js', 'utf8');

function loadChart(nowIso, timezone = 'UTC') {
    const fixed = Date.parse(nowIso);
    const RealDate = Date;

    class FixedDate extends RealDate {
        constructor(...args) {
            if (args.length === 0) {
                super(fixed);
            } else {
                super(...args);
            }
        }

        static now() {
            return fixed;
        }
    }

    const context = {
        Date: FixedDate,
        Intl,
        console,
        window: {},
        document: {body: {addEventListener() {}}},
        wps_js: {
            global: {options: {wp_timezone: timezone}},
            isset(obj, ...keys) {
                return keys.reduce((o, k) => (o && o[k] !== undefined ? o[k] : undefined), obj) !== undefined;
            },
            _: (key) => (key === 'to_range' ? 'to' : ''),
        },
    };
    vm.createContext(context);
    vm.runInContext(momentSource, context);
    vm.runInContext(`${chartSource}\n;globalThis.__wpsChart = {aggregateData, wpsChartToday};`, context);
    return context;
}

function dailyLabels(ctx, from, to) {
    const labels = [];
    const data = [];
    const day = ctx.moment(from, 'YYYY-MM-DD');
    const end = ctx.moment(to, 'YYYY-MM-DD');
    while (day.isSameOrBefore(end, 'day')) {
        labels.push({date: day.format('YYYY-MM-DD'), formatted_date: day.format('MMM D'), month_i18n: day.format('MMMM'), day: day.format('ddd')});
        data.push(1);
        day.add(1, 'day');
    }
    return {labels, datasets: [{label: 'Visitors', data}, {label: 'Views', data: data.map(v => v * 2)}]};
}

const sum = (values) => values.reduce((a, b) => Number(a) + Number(b), 0);

test('This year by month: months after today have no point, the current month stays dashed', () => {
    const ctx = loadChart('2026-11-21T12:00:00Z');
    const {labels, datasets} = dailyLabels(ctx, '2026-01-01', '2026-12-31');
    // The server sends 0 for days that have not happened yet.
    labels.forEach((label, i) => {
        if (label.date > '2026-11-21') {
            datasets.forEach(dataset => { dataset.data[i] = 0; });
        }
    });
    const month = ctx.__wpsChart.aggregateData(labels, datasets, 'month', 'MMM D, YYYY', false);

    assert.equal(month.aggregatedLabels.length, 12);
    assert.equal(month.aggregatedData[0][11], null, 'December is null, not 0');
    assert.equal(month.aggregatedData[1][11], null);
    assert.equal(month.aggregatedData[0][10], 21, 'November keeps its value');
    assert.equal(month.aggregatedData[0][0], 31);
    assert.equal(month.isIncompletePeriod[10], true, 'current month is drawn dashed');
    assert.equal(month.isIncompletePeriod[9], false);
    assert.equal(sum(month.aggregatedData[0]), sum(datasets[0].data), 'legend total is unchanged');
});

test('This month by day: days after today have no point, today is kept', () => {
    const ctx = loadChart('2026-11-21T12:00:00Z');
    const {labels, datasets} = dailyLabels(ctx, '2026-11-01', '2026-11-30');
    const day = ctx.__wpsChart.aggregateData(labels, datasets, 'day', 'MMM D, YYYY', false);

    assert.equal(day.aggregatedData[0][20], 1, 'Nov 21 (today) has a point');
    assert.equal(day.isIncompletePeriod[20], true, 'today is dashed');
    assert.deepEqual(Array.from(day.aggregatedData[0].slice(21)), Array(9).fill(null));
    assert.equal(datasets[0].data[25], 1, 'source data is not mutated');
});

test('Week buckets that start after today have no point', () => {
    const ctx = loadChart('2026-11-21T12:00:00Z');
    const {labels, datasets} = dailyLabels(ctx, '2026-10-01', '2026-12-31');
    const week = ctx.__wpsChart.aggregateData(labels, datasets, 'week', 'MMM D, YYYY', false);
    const values = week.aggregatedData[0];
    const firstNull = values.indexOf(null);

    assert.ok(firstNull > 0, 'some weeks are in the future');
    assert.ok(values.slice(firstNull).every(v => v === null), 'every week after the first future one is null');
    assert.ok(values[firstNull - 1] > 0, 'the week holding today keeps its value');
    assert.equal(week.isIncompletePeriod[firstNull - 1], true);
});

test('A range in the past (Last 30 days) is unchanged', () => {
    const ctx = loadChart('2026-11-21T12:00:00Z');
    const {labels, datasets} = dailyLabels(ctx, '2026-10-23', '2026-11-21');
    for (const unit of ['day', 'week', 'month']) {
        const result = ctx.__wpsChart.aggregateData(labels, datasets, unit, 'MMM D, YYYY', false);
        assert.ok(result.aggregatedData[0].every(v => v !== null), `${unit}: no null points`);
        assert.equal(sum(result.aggregatedData[0]), 30, `${unit}: total unchanged`);
    }
});

test('"Today" follows the site time zone, not the browser', () => {
    // 22:00 UTC on Nov 21 is already Nov 22 in Tehran (+03:30).
    assert.equal(loadChart('2026-11-21T22:00:00Z', 'Asia/Tehran').__wpsChart.wpsChartToday(), '2026-11-22');
    assert.equal(loadChart('2026-11-21T22:00:00Z', '+03:30').__wpsChart.wpsChartToday(), '2026-11-22');
    assert.equal(loadChart('2026-11-21T22:00:00Z', '-05:00').__wpsChart.wpsChartToday(), '2026-11-21');
    assert.equal(loadChart('2026-11-21T22:00:00Z', 'Not/AZone').__wpsChart.wpsChartToday().length, 10);

    const ctx = loadChart('2026-11-21T22:00:00Z', 'Asia/Tehran');
    const {labels, datasets} = dailyLabels(ctx, '2026-11-01', '2026-11-30');
    const day = ctx.__wpsChart.aggregateData(labels, datasets, 'day', 'MMM D, YYYY', false);
    assert.equal(day.aggregatedData[0][21], 1, 'Nov 22 is today on the site, so it has a point');
    assert.equal(day.aggregatedData[0][22], null);
});
