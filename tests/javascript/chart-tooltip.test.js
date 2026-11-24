const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

// Chart tooltip (V1-B): title per interval, aligned previous buckets, change chip, markup.
const momentSource = fs.readFileSync(require.resolve('moment/moment.js'), 'utf8');
const chartSource = fs.readFileSync('assets/dev/javascript/chart.js', 'utf8');

function load(nowIso, lang = 'en-US') {
    const fixed = Date.parse(nowIso);
    const RealDate = Date;
    class FixedDate extends RealDate {
        constructor(...args) { if (args.length === 0) { super(fixed); } else { super(...args); } }
        static now() { return fixed; }
    }
    const context = {
        Date: FixedDate,
        Intl,
        console,
        window: {},
        document: {body: {addEventListener() {}}, documentElement: {lang, dir: lang.startsWith('fa') ? 'rtl' : 'ltr'}},
        wps_js: {
            global: {options: {wp_timezone: 'UTC'}},
            isset(obj, ...keys) { return keys.reduce((o, k) => (o && o[k] !== undefined ? o[k] : undefined), obj) !== undefined; },
            // Monday weeks, like the site the designs were made on.
            _: (key) => (key === 'to_range' ? 'to' : key === 'start_of_week' ? '1' : ''),
        },
    };
    vm.createContext(context);
    vm.runInContext(momentSource, context);
    vm.runInContext(`${chartSource}\n;globalThis.__t = {aggregateData, wpsBucketTitle, wpsAlignPrevious, wpsChangePercent, wpsTooltipHtml, wpsLineTooltipModel, wpsTooltipLocale};`, context);
    return context;
}

function days(ctx, from, to, value = 1) {
    const labels = [];
    const data = [];
    const d = ctx.moment(from, 'YYYY-MM-DD');
    const end = ctx.moment(to, 'YYYY-MM-DD');
    while (d.isSameOrBefore(end, 'day')) {
        labels.push({date: d.format('YYYY-MM-DD'), formatted_date: d.format('MMM D')});
        data.push(value);
        d.add(1, 'day');
    }
    return {labels, datasets: [{label: 'Visitors', data}]};
}

test('Titles are written per interval', () => {
    const ctx = load('2026-11-22T12:00:00Z');
    // Intl puts thin spaces around the range dash.
    const t = (...args) => { const r = ctx.__t.wpsBucketTitle(...args); return {title: r.title.replace(/[\u2009\u202f]/g, ' '), tag: r.tag}; };
    assert.equal(t({start: '2026-11-19', end: '2026-11-19'}, 'day').title, 'Thu, Nov 19');
    assert.equal(t({start: '2026-11-22', end: '2026-11-22', current: true}, 'day').tag, 'so far');
    assert.equal(t({start: '2026-09-28', end: '2026-10-04'}, 'week').title, 'Sep 28 – Oct 4');
    assert.equal(t({start: '2026-08-25', end: '2026-08-30', partial: true}, 'week').tag, 'part of week');
    assert.equal(t({start: '2026-08-01', end: '2026-08-31'}, 'month').title, 'August 2026');
    assert.equal(t({start: '2026-02-01', end: '2026-02-28'}, 'month', {isPrevious: true}).title, 'Feb 2026');
    const may = t({start: '2026-05-23', end: '2026-05-31', partial: true}, 'month');
    assert.equal(may.title, 'May 2026');
    assert.equal(may.tag, 'part of month');
    const nov = t({start: '2026-11-01', end: '2026-11-22', current: true}, 'month');
    assert.equal(nov.title, 'Nov 1 – 22');
    assert.equal(nov.tag, 'so far');
    assert.equal(t({start: '2025-11-19', end: '2025-11-19'}, 'day').title, 'Wed, Nov 19, 2025', 'another year adds the year');
});

test('Persian: localized digits and month names, Gregorian calendar', () => {
    const ctx = load('2026-11-22T12:00:00Z', 'fa-IR');
    const {title} = ctx.__t.wpsBucketTitle({start: '2026-08-01', end: '2026-08-31'}, 'month');
    assert.match(title, /[۰-۹]/, 'Persian digits');
    assert.match(title, /۲۰۲۶/, 'Gregorian year, not 1405');
    const html = ctx.__t.wpsTooltipHtml({title, tag: '', rows: [{label: 'Visitors', color: '#3288D7', now: 338, before: 197, hasBefore: true, change: 72}], footer: 'x'});
    assert.match(html, /۳۳۸/);
});

test('Weekly previous buckets line up with the current ones (13 vs 14 fix)', () => {
    const ctx = load('2026-11-22T12:00:00Z');
    const cur = days(ctx, '2026-08-25', '2026-11-22');
    const prev = days(ctx, '2026-05-27', '2026-08-24', 2);
    const week = ctx.__t.aggregateData(cur.labels, cur.datasets, 'week', 'MMM D, YYYY', false);
    const prevWeek = ctx.__t.aggregateData(prev.labels, prev.datasets, 'week', 'MMM D, YYYY', false);
    assert.equal(week.buckets.length, 13);
    assert.equal(prevWeek.buckets.length, 14, 'calendar weeks of the previous 90 days: one more');

    const aligned = ctx.__t.wpsAlignPrevious(week.buckets, prev.labels, prev.datasets, 'week', prevWeek);
    assert.equal(aligned.buckets.length, week.buckets.length);
    aligned.buckets.forEach((b, i) => {
        const len = (s, e) => ctx.moment(e).diff(ctx.moment(s), 'days') + 1;
        assert.equal(len(b.start, b.end), len(week.buckets[i].start, week.buckets[i].end), `bucket ${i} has the same length`);
    });
    assert.equal(aligned.buckets[aligned.buckets.length - 1].end, '2026-08-24', 'the last previous day is counted');
    const sum = aligned.data[0].reduce((a, b) => a + b, 0);
    assert.equal(sum, 90 * 2, 'every previous day counted once');
    assert.equal(week.buckets[0].partial, true, 'first week is a part week');
    assert.equal(week.buckets[week.buckets.length - 1].current, true, 'this week is "so far"');
});

test('Monthly previous keeps calendar months when the count matches', () => {
    const ctx = load('2026-11-22T12:00:00Z');
    const cur = days(ctx, '2026-05-23', '2026-11-22');
    const prev = days(ctx, '2025-11-21', '2026-05-22');
    const month = ctx.__t.aggregateData(cur.labels, cur.datasets, 'month', 'MMM D, YYYY', false);
    const prevMonth = ctx.__t.aggregateData(prev.labels, prev.datasets, 'month', 'MMM D, YYYY', false);
    const aligned = ctx.__t.wpsAlignPrevious(month.buckets, prev.labels, prev.datasets, 'month', prevMonth);
    assert.equal(aligned.buckets.length, 7);
    assert.equal(aligned.buckets[6].start, '2026-05-01');
    assert.equal(aligned.buckets[6].end, '2026-05-22');
    assert.equal(month.buckets[0].partial, true, 'May 23-31 is a part month');
    assert.equal(month.buckets[6].current, true);
});

test('Change percent: none, new, flat, rounded', () => {
    const ctx = load('2026-11-22T12:00:00Z');
    const c = ctx.__t.wpsChangePercent;
    assert.equal(c(338, 197), 72);
    assert.equal(c(5, 0), 'new');
    assert.equal(c(0, 0), 0);
    assert.equal(c(10, null), null);
    assert.equal(c(50, 100), -50);
});

function fakeChart(datasets, state) {
    return {
        data: {datasets},
        getDatasetMeta: (i) => ({hidden: !!datasets[i].hidden}),
        $wps: state,
    };
}

test('Model: Before / Change per row, footer once, previous state per chart', () => {
    const ctx = load('2026-11-22T12:00:00Z');
    const state = {unit: 'month', buckets: [{start: '2026-08-01', end: '2026-08-31'}], prevBuckets: [{start: '2026-02-01', end: '2026-02-28'}], prevNoData: false};
    const sets = [
        {label: 'Visitors', data: [338], borderColor: '#3288D7'},
        {label: 'Views', data: [836], borderColor: '#7362BF'},
        {label: 'Visitors (Previous)', data: [197]},
        {label: 'Views (Previous)', data: [553]},
    ];
    const model = ctx.__t.wpsLineTooltipModel(fakeChart(sets, state), 0);
    assert.equal(model.title, 'August 2026');
    assert.equal(model.rows.length, 2);
    assert.equal(model.rows[0].change, 72);
    assert.equal(model.footer, 'Compared with Feb 2026');

    const html = ctx.__t.wpsTooltipHtml(model);
    assert.equal((html.match(/Feb 2026/g) || []).length, 1, 'previous period named once');
    assert.match(html, /wps-change--up wps-change--good/);
    assert.doesNotMatch(html, / To /, 'no capitalised "To"');

    // Another chart with its previous lines hidden: only that chart loses the Before column.
    const hidden = sets.map(s => Object.assign({}, s, {hidden: s.label.includes('(Previous)')}));
    const other = ctx.__t.wpsLineTooltipModel(fakeChart(hidden, state), 0);
    assert.equal(other.rows.every(r => !r.hasBefore), true);
    assert.doesNotMatch(ctx.__t.wpsTooltipHtml(other), /Compared with/);
    assert.equal(ctx.__t.wpsLineTooltipModel(fakeChart(sets, state), 0).rows[0].hasBefore, true, 'first chart unchanged');
});

test('Model: so far hides the change unless the same days are compared; no data; New', () => {
    const ctx = load('2026-11-22T12:00:00Z');
    const sets = [{label: 'Visitors', data: [369]}, {label: 'Visitors (Previous)', data: [0]}];
    const day = ctx.__t.wpsLineTooltipModel(fakeChart(sets, {unit: 'day', buckets: [{start: '2026-11-22', end: '2026-11-22', current: true}], prevBuckets: [{start: '2026-10-23', end: '2026-10-23'}]}), 0);
    assert.equal(day.tag, 'so far');
    assert.equal(day.rows[0].change, null, 'today: no change %');

    const month = ctx.__t.wpsLineTooltipModel(fakeChart([{label: 'Visitors', data: [369]}, {label: 'Visitors (Previous)', data: [300]}], {unit: 'month', buckets: [{start: '2026-11-01', end: '2026-11-22', current: true}], prevBuckets: [{start: '2026-05-01', end: '2026-05-22'}]}), 0);
    assert.equal(month.rows[0].change, 23, 'Nov 1-22 vs May 1-22 is comparable');
    assert.equal(month.footer.replace(/[\u2009\u202f]/g, ' '), 'Compared with May 1 – 22, 2026', 'the same days, written as dates');

    const fresh = ctx.__t.wpsLineTooltipModel(fakeChart(sets, {unit: 'month', buckets: [{start: '2026-08-01', end: '2026-08-31'}], prevBuckets: [{start: '2026-02-01', end: '2026-02-28'}], prevNoData: true}), 0);
    assert.equal(fresh.rows[0].before, null);
    assert.equal(fresh.footer, 'No data for Feb 2026');
    assert.match(ctx.__t.wpsTooltipHtml(fresh), /—/);

    const isNew = ctx.__t.wpsLineTooltipModel(fakeChart(sets, {unit: 'month', buckets: [{start: '2026-08-01', end: '2026-08-31'}], prevBuckets: [{start: '2026-02-01', end: '2026-02-28'}]}), 0);
    assert.equal(isNew.rows[0].change, 'new');
    assert.match(ctx.__t.wpsTooltipHtml(isNew), />New</);
});

test('Rows without a previous series show "—" (Search Engines: only Total has one)', () => {
    const ctx = load('2026-11-22T12:00:00Z');
    const sets = [{label: 'Total', data: [15]}, {label: 'Google', data: [11]}, {label: 'Total (Previous)', data: [3]}];
    const model = ctx.__t.wpsLineTooltipModel(fakeChart(sets, {unit: 'day', buckets: [{start: '2026-11-19', end: '2026-11-19'}], prevBuckets: [{start: '2026-10-20', end: '2026-10-20'}]}), 0);
    assert.equal(model.rows[1].hasBefore, false);
    const html = ctx.__t.wpsTooltipHtml(model);
    assert.equal((html.match(/<tr>/g) || []).length, 3, 'header + 2 rows');
    assert.match(html, /Compared with Tue, Oct 20/);
});

test('A dataset with its own colour (source category) keeps it; others keep slug or position colours', () => {
    const ctx = load('2026-11-22T12:00:00Z');
    vm.runInContext('globalThis.__c = wpsDatasetColor;', ctx);
    const color = ctx.__c;
    assert.equal(color({label: 'Organic Search', color: '#4285F4', originalIndex: 2}), '#4285F4');
    assert.equal(color({label: 'Direct Traffic', color: '#7D8083', originalIndex: 0}), '#7D8083');
    assert.equal(color({label: 'Total', slug: 'total', originalIndex: 3}), '#27A765');
    assert.equal(color({label: 'Bing', originalIndex: 1}), '#7362BF');
    assert.equal(color({label: 'Bad', color: 'red;background:url(x)', originalIndex: 0}), '#3288D7');
    assert.equal(color({label: 'No index'}), undefined);
});
