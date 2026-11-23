const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

// Inline teaser close (assets/dev/javascript/components/inline-teaser.js): hides at once, posts the nonce'd dismiss.

function load({fetch, global} = {}) {
    const listeners = {};
    const calls = [];
    const window = {
        fetch: fetch === undefined ? (url, opts) => {
            calls.push({url, opts});
            return Promise.resolve({});
        } : fetch,
    };
    const context = {
        window,
        URLSearchParams,
        document: {addEventListener: (type, fn) => (listeners[type] = fn)},
        wps_js: {global: global || {ajax_url: '/wp-admin/admin-ajax.php'}},
    };
    vm.runInNewContext(fs.readFileSync('assets/dev/javascript/components/inline-teaser.js', 'utf8'), context);
    return {window, listeners, calls};
}

function teaser(attrs = {'data-teaser-id': 'dp-journey', 'data-nonce': 'abc123'}) {
    return {hidden: false, getAttribute: (name) => (name in attrs ? attrs[name] : null)};
}

test('close hides the teaser and posts id + nonce to admin-ajax', () => {
    const {window, calls} = load();
    const el = teaser();
    window.wpsInlineTeaserDismiss(el);

    assert.equal(el.hidden, true);
    assert.equal(calls.length, 1);
    assert.equal(calls[0].url, '/wp-admin/admin-ajax.php');
    assert.equal(calls[0].opts.method, 'POST');
    const body = calls[0].opts.body;
    assert.equal(body.get('action'), 'wp_statistics_dismiss_teaser');
    assert.equal(body.get('teaser_id'), 'dp-journey');
    assert.equal(body.get('wps_nonce'), 'abc123');
});

test('falls back to admin_url when ajax_url is missing', () => {
    const {window, calls} = load({global: {admin_url: 'http://x.test/wp-admin/'}});
    window.wpsInlineTeaserDismiss(teaser());
    assert.equal(calls[0].url, 'http://x.test/wp-admin/admin-ajax.php');
});

test('without a nonce it still hides but sends nothing', () => {
    const {window, calls} = load();
    const el = teaser({'data-teaser-id': 'dp-journey'});
    window.wpsInlineTeaserDismiss(el);
    assert.equal(el.hidden, true);
    assert.equal(calls.length, 0);
});

test('delegated click only reacts to the close button', () => {
    const {listeners, calls} = load();
    const el = teaser();
    let prevented = false;
    const button = {closest: (sel) => (sel === '.js-wps-inline-teaser' ? el : null)};
    listeners.click({target: {closest: (sel) => (sel === '.js-wps-inline-teaser-close' ? button : null)}, preventDefault: () => (prevented = true)});
    assert.equal(el.hidden, true);
    assert.equal(prevented, true);
    assert.equal(calls.length, 1);

    listeners.click({target: {closest: () => null}, preventDefault: () => assert.fail('should not prevent')});
    assert.equal(calls.length, 1);
});
