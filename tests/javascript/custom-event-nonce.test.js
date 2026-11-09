const test = require('node:test');
const assert = require('node:assert');
const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '../..');
const frontend = fs.readFileSync(path.join(root, 'includes/class-wp-statistics-frontend.php'), 'utf8');
const built = fs.readFileSync(path.join(root, 'assets/js/tracker-v2.js'), 'utf8');

function loadHandler() {
    const match = built.match(/handleCustomEvent:function\(t,e=\{\}\)\{[\s\S]*?this\.sendEventData\(t,n\)\}/);
    assert.ok(match, 'handleCustomEvent found in the built tracker');
    return new Function('WP_Statistics_Tracker_Object', `return ({${match[0]}}).handleCustomEvent;`);
}

function send(trackerObject) {
    const calls = [];
    loadHandler()(trackerObject).call({sendEventData: (data, url) => calls.push({data, url})}, 'purchase', {});
    return calls[0];
}

const base = {ajaxUrl: 'https://example.test/wp-admin/admin-ajax.php', hitParams: {source_id: 1}};

test('the page HTML no longer carries a custom event URL with a nonce', () => {
    assert.ok(!frontend.includes('customEventAjaxUrl'));
    assert.ok(frontend.includes("'customEventNonce'"));
    assert.ok(frontend.includes("'js/tracker-v2.js'"), 'an incompatible tracker uses a new cache path');
});

test('the nonce and action go in the POST body, to the plain admin-ajax.php', () => {
    const call = send({...base, customEventNonce: 'abc123'});
    assert.strictEqual(call.url, base.ajaxUrl);
    assert.strictEqual(call.data.action, 'wp_statistics_custom_event');
    assert.strictEqual(call.data.nonce, 'abc123');
});

test('pages cached before the change keep posting to their old URL', () => {
    const oldUrl = base.ajaxUrl + '?action=wp_statistics_custom_event&nonce=old';
    const call = send({...base, customEventAjaxUrl: oldUrl});
    assert.strictEqual(call.url, oldUrl);
    assert.strictEqual(call.data.nonce, undefined);
});
