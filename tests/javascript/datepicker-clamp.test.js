const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync('assets/dev/javascript/plugin/datepicker.js', 'utf8');

function load() {
    const context = {jQuery: () => ({ready() {}, on() {}}), document: {querySelectorAll: () => [], addEventListener() {}}, window: {}, setTimeout() {}};
    vm.createContext(context);
    vm.runInContext(`${source}\n;globalThis.__clamp = wpsClampRangeStart;`, context);
    return context.__clamp;
}

const clamp = load();

test('range that starts before the publish date starts on the publish date', () => {
    assert.equal(clamp('2025-11-23', '2026-11-23', '2026-03-10 09:00:00'), '2026-03-10');
});

test('range that ends before the publish date is kept, never backwards', () => {
    const from = clamp('2025-01-01', '2025-02-01', '2026-03-10 09:00:00');
    assert.equal(from, '2025-01-01');
    assert.ok(from <= '2025-02-01');
});

test('range that starts after the publish date is unchanged', () => {
    assert.equal(clamp('2026-05-01', '2026-06-01', '2026-03-10'), '2026-05-01');
});

test('range that ends on the publish date starts on it', () => {
    assert.equal(clamp('2026-01-01', '2026-03-10', '2026-03-10'), '2026-03-10');
});

test('missing publish date changes nothing', () => {
    assert.equal(clamp('2026-01-01', '2026-02-01', ''), '2026-01-01');
});
