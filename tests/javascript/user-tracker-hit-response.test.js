const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const trackerFiles = [
    'assets/dev/javascript/user-tracker.js',
    'assets/js/tracker.js',
];

function loadTracker(file) {
    let request;

    class FakeXMLHttpRequest {
        constructor() {
            request = this;
        }

        open() {}
        setRequestHeader() {}
        send() {}
    }

    const context = {
        window: {
            location: {href: 'https://example.test/', pathname: '/', search: ''},
            addEventListener() {},
            removeEventListener() {},
        },
        history: {pushState() {}, replaceState() {}},
        navigator: {},
        document: {
            referrer: '',
            cookie: '',
            addEventListener() {},
            querySelector() { return null; },
            getElementById() { return null; },
        },
        console: {log() {}, warn() {}, error() {}},
        TextEncoder,
        URLSearchParams,
        XMLHttpRequest: FakeXMLHttpRequest,
        btoa: value => Buffer.from(value, 'binary').toString('base64'),
        setTimeout() {},
        clearTimeout() {},
        WP_Statistics_Tracker_Object: {
            option: {dntEnabled: false, isPreview: false, bypassAdBlockers: false},
            hitParams: {endpoint: 'hit'},
            requestUrl: 'https://example.test',
            ajaxUrl: 'https://example.test/ajax',
        },
    };

    vm.createContext(context);
    vm.runInContext(fs.readFileSync(file, 'utf8'), context, {filename: file});

    return {
        tracker: context.window.WpStatisticsUserTracker,
        getRequest: () => request,
    };
}

for (const file of trackerFiles) {
    test(`${file} only accepts an explicit successful hit response`, async () => {
        const cases = [
            ['{"status":true}', true],
            ['PHP notice before JSON\n{"status":true}', true],
            ['{"status":false}', false],
            ['{}', false],
            ['[]', false],
            ['null', false],
            ['"ok"', false],
        ];

        for (const [body, expected] of cases) {
            const {tracker, getRequest} = loadTracker(file);
            await tracker.sendHitRequest();

            const request = getRequest();
            request.readyState = 4;
            request.status = 200;
            request.responseText = body;

            assert.doesNotThrow(() => request.onreadystatechange(), body);
            assert.equal(tracker.hitRequestSuccessful, expected, body);
        }
    });
}
