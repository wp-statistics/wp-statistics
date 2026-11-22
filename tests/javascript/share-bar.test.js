const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

// Load the share helpers and horizontal_bar from helper.js into a sandbox with a tiny fake DOM.
function load() {
    const src = fs.readFileSync('assets/dev/javascript/helper.js', 'utf8');
    const start = src.indexOf('wps_js.share_percent = function');
    const end = src.indexOf('window.renderHorizontalBar = ');
    assert.ok(start > 0 && end > start, 'share helpers found in helper.js');
    const code = src.slice(src.lastIndexOf('/**', start), end);

    class El {
        constructor(tag) { this.tag = tag; this.children = []; this.classList = new Set(); this.attrs = {}; this.style = {}; this.innerHTML = ''; this.parentNode = null; }
        appendChild(c) { c.parentNode = this; this.children.push(c); return c; }
        removeChild(c) { this.children = this.children.filter(x => x !== c); c.parentNode = null; }
        insertBefore(c, ref) { const i = this.children.indexOf(ref); c.parentNode = this; this.children.splice(i < 0 ? this.children.length : i, 0, c); }
        setAttribute(k, v) { this.attrs[k] = String(v); }
        get nextSibling() { const p = this.parentNode; return p ? p.children[p.children.indexOf(this) + 1] || null : null; }
    }
    El.prototype.classList = null;
    const parent = new El('div');
    const canvas = new El('canvas');
    canvas.id = 'bars';
    parent.appendChild(canvas);
    const document = {
        getElementById: id => (id === 'bars' && canvas.parentNode ? canvas : null),
        createElement: tag => {
            const el = new El(tag);
            el.classList = {list: [], add(...c) { this.list.push(...c); }, contains(c) { return this.list.includes(c); }};
            return el;
        },
    };
    const wps_js = {
        escapeHtml: s => String(s).replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c])),
        formatNumber: n => String(n),
    };
    vm.runInNewContext(code, {wps_js, document, Math, Number, Array, Object, String});
    return {wps_js, parent};
}

const rows = [578, 295, 220, 188, 149]; // top 5 of 3,501 visitors

test('share list uses the real total when one is passed (US 16.5%, not 40.4%)', () => {
    const {wps_js} = load();
    const shares = wps_js.horizontal_bar_percentages(rows, [], 3501);
    assert.equal(shares[0], 16.5);
    assert.notEqual(shares[0], 40.4);
});

test('explicit percentages win over any computation', () => {
    const {wps_js} = load();
    assert.deepEqual(wps_js.horizontal_bar_percentages([1, 1], ['12.5', 7], 3501), [12.5, 7]);
});

test('without a total the rows are the whole (lists that already carry an Other row)', () => {
    const {wps_js} = load();
    assert.deepEqual(wps_js.horizontal_bar_percentages([171, 71, 68, 39, 125]), [36.1, 15, 14.3, 8.2, 26.4]);
    assert.deepEqual(wps_js.horizontal_bar_percentages([0, 0]), [0, 0]);
});

test('horizontal_bar renders a thin track per row with the real share and keeps legacy classes', () => {
    const {wps_js, parent} = load();
    wps_js.horizontal_bar('bars', ['United States', 'Germany', 'United Kingdom', 'France', 'Netherlands'], rows, [], [], {total: 3501});
    const list = parent.children[0];
    assert.ok(list.classList.contains('wps-horizontal-bar'));
    assert.ok(list.classList.contains('wps-share-list'));
    const first = list.children[0];
    assert.ok(first.classList.contains('wps-horizontal-bar__item'));
    const data = first.children[1].children[0];
    assert.match(data.innerHTML, /16\.5%/);
    const track = first.children[2];
    assert.ok(track.classList.contains('wps-horizontal-bar__track'));
    assert.equal(track.attrs['aria-hidden'], 'true');
    assert.equal(track.children[0].style.width, '16.5%');
});

test('percent_bar markup: share, rate and ring variants', () => {
    const {wps_js} = load();
    const share = wps_js.percent_bar(16.5);
    assert.match(share, /wps-percent--share/);
    assert.match(share, />16\.5%</);
    assert.match(share, /aria-hidden="true"/);
    assert.match(wps_js.percent_bar(40, {variant: 'rate'}), />40%</);
    assert.match(wps_js.percent_bar(43.4, {variant: 'ring'}), /wps-percent__ring/);
    assert.match(wps_js.percent_bar(150), />100%</);
});
