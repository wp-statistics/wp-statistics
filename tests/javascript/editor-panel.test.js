const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

// Block editor "WP Statistics" panel (assets/blocks/post-summary/post-summary.js, built from
// assets/dev/blocks/post-summary/index.js) and the shared post summary chart helper.

function loadPanel(data) {
    const calls = {registerPlugin: [], slotFills: []};
    const window = {
        React: {createElement: (type, props, ...children) => ({type, props, children})},
        wp: {
            plugins: {registerPlugin: (name, settings) => calls.registerPlugin.push({name, settings})},
            editor: {PluginDocumentSettingPanel: 'PluginDocumentSettingPanel'},
            components: {createSlotFill: name => { calls.slotFills.push(name); return {Fill: 'Fill:' + name, Slot: 'Slot:' + name}; }},
            element: {RawHTML: 'RawHTML', useEffect: () => {}, useRef: () => ({current: null})},
            data: {useSelect: () => ({}), useDispatch: () => ({})},
            preferences: {store: 'core/preferences'},
            i18n: {__: s => s},
        },
    };
    if (data) {
        window.wpStatisticsPostPanelData = data;
    }
    vm.runInNewContext(fs.readFileSync('assets/blocks/post-summary/post-summary.js', 'utf8'), {window});
    return {window, calls};
}

test('the panel bundle uses WordPress globals, not its own React copy', () => {
    const asset = fs.readFileSync('assets/blocks/post-summary/post-summary.asset.php', 'utf8');
    for (const dep of ['wp-plugins', 'wp-editor', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n']) {
        assert.ok(asset.includes(`'${dep}'`), `asset file lists ${dep}`);
    }
    const bundle = fs.readFileSync('assets/blocks/post-summary/post-summary.js', 'utf8');
    assert.ok(bundle.length < 10000, 'small bundle, no React or Chart.js inside');
});

test('registers one "wp-statistics" plugin and exposes the WPStatisticsPostPanel slot', () => {
    const {window, calls} = loadPanel({postId: 12, slot: 'WPStatisticsPostPanel', html: '<div class="wps-post-summary"></div>', chart: null});

    assert.equal(calls.registerPlugin.length, 1);
    assert.equal(calls.registerPlugin[0].name, 'wp-statistics');
    assert.equal(typeof calls.registerPlugin[0].settings.render, 'function');
    assert.ok(calls.registerPlugin[0].settings.icon, 'plugin icon');
    assert.deepEqual(calls.slotFills, ['WPStatisticsPostPanel']);
    assert.equal(window.wpStatistics.PostPanel.name, 'WPStatisticsPostPanel');
    assert.equal(window.wpStatistics.PostPanel.Fill, 'Fill:WPStatisticsPostPanel');
});

test('does nothing visible when PHP did not pass panel data (no access, setting off)', () => {
    const {window, calls} = loadPanel(null);

    assert.equal(calls.registerPlugin.length, 0);
    assert.ok(window.wpStatistics.PostPanel.Fill, 'slot stays available so add-ons do not break');
});

test('summary chart accepts a canvas element and returns the chart, so the panel can destroy it', () => {
    const src = fs.readFileSync('assets/dev/javascript/meta-box/post-summary.js', 'utf8');
    const created = [];
    class Chart { constructor(ctx, config) { this.ctx = ctx; this.config = config; created.push(this); } }
    const canvas = {getContext: () => 'ctx', parentNode: null};
    const document = {getElementById: () => { throw new Error('must not look up by id when given an element'); }};
    const window = {};
    const wps_js = {hex_to_rgba: (c, a) => `${c}/${a}`};
    vm.runInNewContext(src, {wps_js, window, document, Chart, Object, String, parseInt});

    const chart = wps_js.post_summary_chart(canvas, {
        postChartData: {'14 Nov': {hits: 3, fullDate: 'November 14'}, '15 Nov': {hits: 0, fullDate: 'November 15'}},
        postChartSettings: {color: '#404BF2', label: 'Views'},
    });

    assert.equal(created.length, 1);
    assert.equal(chart, created[0]);
    assert.deepEqual(Array.from(chart.config.data.datasets[0].data), [3, 0]);
    assert.equal(window.wpStatisticsPostSummaryChart, wps_js.post_summary_chart);
    assert.equal(wps_js.post_summary_chart(canvas, {}), undefined, 'no data, no chart');
});

test('first visit opens the panel once, even when React runs the effect twice (SCRIPT_DEBUG)', () => {
    const {window, calls} = loadPanel({postId: 12, slot: 'WPStatisticsPostPanel', html: '', chart: null});
    const effects = [];
    let seen = false;
    let opened = false;
    let toggles = 0;
    const stores = {
        'core/preferences': {get: () => seen},
        'core/editor': {isEditorPanelOpened: () => opened},
    };
    window.wp.element.useEffect = effect => effects.push(effect);
    window.wp.data.useRegistry = () => ({select: name => stores[name]});
    window.wp.data.useSelect = map => map(name => stores[name]);
    window.wp.data.useDispatch = () => ({
        set: (scope, key, value) => { seen = value; },
        toggleEditorPanelOpened: () => { toggles++; opened = !opened; },
    });

    calls.registerPlugin[0].settings.render();
    effects[0]();
    effects[0]();

    assert.equal(toggles, 1);
    assert.equal(opened, true);
});
