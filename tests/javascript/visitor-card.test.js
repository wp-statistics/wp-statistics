const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

// Visitor card popover (assets/dev/javascript/components/visitor-card.js): the pure placement helper.

function load() {
    const window = {};
    vm.runInNewContext(fs.readFileSync('assets/dev/javascript/components/visitor-card.js', 'utf8'), {window});
    return window.wpsVisitorCard;
}

const card = {width: 340, height: 200};
const viewport = {width: 1440, height: 900};
const rect = (left, top, width = 102, height = 24) => ({left, top, right: left + width, bottom: top + height, width, height});

test('loads without a DOM and exposes the helper and timings', () => {
    const api = load();
    assert.equal(typeof api.position, 'function');
    assert.equal(api.OPEN_DELAY, 250);
    assert.equal(api.CLOSE_DELAY, 150);
    assert.equal(api.CLOSE_MS, 100);
    assert.equal(api.SHEET_BELOW, 480);
});

test('opens 8px below the pill, start edges lined up', () => {
    const pos = load().position(rect(300, 200), card, viewport, {beside: false});
    assert.equal(pos.side, 'bottom');
    assert.equal(pos.top, 200 + 24 + 8);
    assert.equal(pos.left, 300);
    assert.equal(pos.maxHeight, null);
});

test('flips above near the bottom of the screen', () => {
    const pos = load().position(rect(300, 800), card, viewport, {beside: false});
    assert.equal(pos.side, 'top');
    assert.equal(pos.top, 800 - 8 - 200);
});

test('stays inside the viewport on the right edge and the left edge', () => {
    const api = load();
    const right = api.position(rect(1380, 200), card, viewport, {beside: false});
    assert.equal(right.left, 1440 - 340 - 8);
    const left = api.position(rect(-40, 200), card, viewport, {beside: false});
    assert.equal(left.left, 8);
});

test('RTL lines the card up with the end (right) edge of the pill', () => {
    const pos = load().position(rect(900, 200), card, viewport, {rtl: true, beside: false});
    assert.equal(pos.left, 900 + 102 - 340);
});

test('no room on either side: larger side, capped height, never covers the pill', () => {
    const tall = {width: 340, height: 700};
    const t = rect(100, 300);
    const pos = load().position(t, tall, {width: 1024, height: 800}, {beside: false});
    assert.equal(pos.side, 'bottom');
    assert.equal(pos.top, t.bottom + 8);
    assert.equal(pos.maxHeight, 800 - 8 - (t.bottom + 8));
    assert.ok(pos.top >= t.bottom, 'starts below the pill');

    const up = load().position(rect(100, 600), tall, {width: 1024, height: 800}, {beside: false});
    assert.equal(up.side, 'top');
    assert.equal(up.top + up.maxHeight, 600 - 8, 'ends 8px above the pill');
});

test('RTL near the left edge stays inside the viewport', () => {
    const pos = load().position(rect(20, 200), card, viewport, {rtl: true, beside: false});
    assert.equal(pos.left, 8);
});

test('opens beside the pill by default (right in LTR), top edges lined up, so the next row stays free', () => {
    const pos = load().position(rect(300, 200), card, viewport);
    assert.equal(pos.side, 'right');
    assert.equal(pos.left, 300 + 102 + 8);
    assert.equal(pos.top, 200);
    assert.equal(pos.maxHeight, null);
});

test('beside the pill in RTL opens on the left', () => {
    const pos = load().position(rect(900, 200), card, viewport, {rtl: true});
    assert.equal(pos.side, 'left');
    assert.equal(pos.left, 900 - 8 - 340);
});

test('beside the pill near the bottom moves up to stay on screen', () => {
    const pos = load().position(rect(300, 850), card, viewport);
    assert.equal(pos.side, 'right');
    assert.equal(pos.top, 900 - 8 - 200);
});

test('no room beside the pill: falls back to below', () => {
    const pos = load().position(rect(1200, 200), card, viewport);
    assert.equal(pos.side, 'bottom');
    assert.equal(pos.top, 200 + 24 + 8);
});
