const assert = require('node:assert/strict');
const test = require('node:test');
const path = require('node:path');

// Visitors card switch (assets/dev/javascript/components/visitors-switch.js): markup and keys.
const sw = require(path.resolve('assets/dev/javascript/components/visitors-switch.js'));

const all = ['latest', 'active', 'online'];
const segments = [
    {key: 'latest', label: 'Latest'},
    {key: 'active', label: 'Most active'},
    {key: 'online', label: 'Online now'},
];

const tabs = html => [...html.matchAll(/<button [^>]*>/g)].map(m => m[0]);
const attr = (tag, name) => (tag.match(new RegExp(name + '="([^"]*)"')) || [])[1];

test('arrows move forward and back and wrap around', () => {
    assert.equal(sw.nextSegment(all, 'latest', 'ArrowRight', false), 'active');
    assert.equal(sw.nextSegment(all, 'online', 'ArrowRight', false), 'latest');
    assert.equal(sw.nextSegment(all, 'latest', 'ArrowLeft', false), 'online');
});

test('RTL flips the arrow keys', () => {
    assert.equal(sw.nextSegment(all, 'latest', 'ArrowLeft', true), 'active');
    assert.equal(sw.nextSegment(all, 'latest', 'ArrowRight', true), 'online');
});

test('Home and End jump to the ends; other keys do nothing', () => {
    assert.equal(sw.nextSegment(all, 'active', 'Home', false), 'latest');
    assert.equal(sw.nextSegment(all, 'active', 'End', false), 'online');
    assert.equal(sw.nextSegment(all, 'active', 'Enter', false), null);
    assert.equal(sw.nextSegment(all, 'active', 'ArrowDown', false), null);
});

test('without Online the keys cycle over two parts', () => {
    assert.equal(sw.nextSegment(['latest', 'active'], 'active', 'ArrowRight', false), 'latest');
    assert.equal(sw.nextSegment(['latest', 'active'], 'active', 'End', false), 'active');
});

test('a tab list with one selected, focusable tab', () => {
    const html = sw.render({segments, selected: 'active', online: 3, label: 'Visitors'});
    assert.match(html, /^<div class="wps-visitors-switch" role="tablist" aria-label="Visitors">/);

    const t = tabs(html);
    assert.equal(t.length, 3);
    assert.deepEqual(t.map(x => attr(x, 'role')), ['tab', 'tab', 'tab']);
    assert.deepEqual(t.map(x => attr(x, 'aria-selected')), ['false', 'true', 'false']);
    assert.deepEqual(t.map(x => attr(x, 'tabindex')), ['-1', '0', '-1']);
    assert.deepEqual(t.map(x => attr(x, 'aria-controls')), [sw.PANEL_ID, sw.PANEL_ID, sw.PANEL_ID]);
    assert.deepEqual(t.map(x => attr(x, 'data-segment')), all);
});

test('Online now gets a live dot and the count', () => {
    const html = sw.render({segments, selected: 'latest', online: 3, label: 'Visitors'});
    assert.match(html, /<span class="wps-pulse-dot" aria-hidden="true"><\/span><span class="wps-visitors-switch__label" data-text="Online now">Online now<\/span><span class="wps-visitors-switch__badge">3<\/span>/);
});

test('nobody online: grey still dot, badge 0, tab still there', () => {
    const html = sw.render({segments, selected: 'online', online: 0, label: 'Visitors'});
    assert.match(html, /wps-pulse-dot is-off/);
    assert.match(html, /wps-visitors-switch__badge is-zero">0</);
    assert.equal(attr(tabs(html)[2], 'aria-selected'), 'true');
});

test('Online feature off: two parts, an Online choice falls back to Latest', () => {
    const html = sw.render({segments: segments.slice(0, 2), selected: 'online', online: null, label: 'Visitors'});
    const t = tabs(html);
    assert.equal(t.length, 2);
    assert.doesNotMatch(html, /pulse/);
    assert.equal(attr(t[0], 'aria-selected'), 'true');
});

test('labels are escaped', () => {
    const html = sw.render({segments: [{key: 'latest', label: '<b>"x"</b>'}], selected: 'latest', label: 'A & B'});
    assert.doesNotMatch(html, /<b>/);
    assert.match(html, /aria-label="A &amp; B"/);
    assert.match(html, /&lt;b&gt;&quot;x&quot;&lt;\/b&gt;/);
});

test('bad counts show 0', () => {
    assert.deepEqual(sw.onlineState(null), {text: '0', zero: true});
    assert.deepEqual(sw.onlineState('7'), {text: '7', zero: false});
});
