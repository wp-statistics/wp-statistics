const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync('assets/dev/javascript/components/notification.js', 'utf8');

function loadNotificationScript(initialCountdowns = []) {
    const listeners = {};
    const intervals = [];
    const countdowns = [...initialCountdowns];
    const removedClasses = [];
    const removedAttrs = [];
    const clipboardWrites = [];

    const emptyCollection = {
        length: 0,
        ready(callback) { callback(); return this; },
        hide() {},
        remove() {},
        removeClass(value) { removedClasses.push(value); return this; },
        removeAttr(value) { removedAttrs.push(value); return this; },
        css() {},
        first() { return this; },
        last() { return this; },
        each() {},
        on() {},
    };

    const document = {
        body: {classList: {toggle() {}, remove() {}}},
        documentElement: {clientWidth: 1000, style: {setProperty() {}}},
        querySelector(selector) {
            if (selector === '.wps-notification-sidebar__tab--active') {
                return {dataset: {tab: 'tab-1'}};
            }
            return null;
        },
        querySelectorAll(selector) {
            if (selector === '.wps-notification-sidebar__countdown') return countdowns;
            return [];
        },
        getElementById() { return {classList: {add() {}}}; },
        addEventListener(type, callback) { listeners[type] = callback; },
    };

    function jQuery(target) {
        if (typeof target === 'function') {
            target();
            return emptyCollection;
        }
        return emptyCollection;
    }

    const context = {
        console,
        document,
        jQuery,
        navigator: {clipboard: {writeText(value) { clipboardWrites.push(value); return Promise.resolve(); }}},
        setInterval(callback) { intervals.push(callback); return 1; },
        setTimeout(callback) { callback(); return 1; },
        window: {innerWidth: 1000},
        wps_js: {global: {rest_api_nonce: 'nonce', admin_url: '/'}},
    };

    vm.runInNewContext(source, context);
    return {listeners, intervals, countdowns, removedClasses, removedAttrs, clipboardWrites};
}

function countdown(endsAt) {
    const attrs = {
        'data-ends-at': endsAt,
        'data-prefix': 'Ends in',
        'data-d': 'd',
        'data-h': 'h',
        'data-m': 'm',
    };
    return {innerHTML: '', getAttribute(name) { return attrs[name]; }};
}

test('newly cloned promo cards keep live countdowns', () => {
    const original = countdown(new Date(Date.now() + 7200000).toISOString());
    const env = loadNotificationScript([original]);
    const clone = countdown(new Date(Date.now() + 3600000).toISOString());
    env.countdowns.push(clone);
    assert.equal(clone.innerHTML, '');
    assert.equal(env.intervals.length, 1);
    env.intervals[0]();
    assert.match(clone.innerHTML, /^Ends in <strong>/);
});

test('copy handling works for promo cards added after initialization', async () => {
    const env = loadNotificationScript();
    assert.equal(typeof env.listeners.click, 'function');
    const attrs = {'data-code': 'SAVE50', 'data-copied': 'Copied'};
    const button = {
        textContent: 'Copy',
        getAttribute(name) { return attrs[name]; },
        closest(selector) { return selector === '.wps-notification-sidebar__copy' ? this : null; },
    };
    env.listeners.click({target: button});
    await Promise.resolve();
    assert.deepEqual(env.clipboardWrites, ['SAVE50']);
});

test('an empty inbox clears stale offer badge state', () => {
    const env = loadNotificationScript();
    assert.ok(env.removedClasses.includes('wps-notifications--has-items wps-notifications--has-badge wps-notifications--urgent'));
    assert.ok(env.removedAttrs.includes('data-badge'));
});
