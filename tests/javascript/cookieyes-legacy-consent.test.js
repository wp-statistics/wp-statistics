const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const trackerFiles = [
    'assets/dev/javascript/tracker.js',
    'assets/js/tracker.js',
];

function loadCookieYesTracker(file, cookie) {
    const listeners = {};
    let userTrackerStarts = 0;
    let eventTrackerStarts = 0;

    const context = {
        document: {
            cookie,
            prerendering: false,
            addEventListener(type, callback) {
                (listeners[type] ||= []).push(callback);
            },
        },
        console: {log() {}, warn() {}, error() {}},
        WpStatisticsUserTracker: {init() { userTrackerStarts++; }},
        WpStatisticsEventTracker: {init() { eventTrackerStarts++; }},
        WP_Statistics_Tracker_Object: {
            option: {
                consentIntegration: {
                    name: 'cookieyes',
                    status: {legacy: true},
                },
            },
        },
        setTimeout(callback) { callback(); },
    };
    context.window = context;

    vm.createContext(context);
    vm.runInContext(fs.readFileSync(file, 'utf8'), context, {filename: file});
    listeners.DOMContentLoaded[0]();

    return {
        userTrackerStarts,
        eventTrackerStarts,
    };
}

for (const file of trackerFiles) {
    test(`${file} accepts only an explicit legacy analytics opt-in`, () => {
        const accepted = loadCookieYesTracker(
            file,
            'viewed_cookie_policy=yes; cookielawinfo-checkbox-analytics=yes'
        );
        assert.equal(accepted.userTrackerStarts, 1);
        assert.equal(accepted.eventTrackerStarts, 1);

        const missingCategory = loadCookieYesTracker(file, 'viewed_cookie_policy=yes');
        assert.equal(missingCategory.userTrackerStarts, 1);
        assert.equal(missingCategory.eventTrackerStarts, 1);

        for (const value of ['no', '', 'invalid']) {
            const rejected = loadCookieYesTracker(
                file,
                `viewed_cookie_policy=yes; cookielawinfo-checkbox-analytics=${value}`
            );
            assert.equal(rejected.userTrackerStarts, 0, value);
            assert.equal(rejected.eventTrackerStarts, 0, value);
        }
    });
}
