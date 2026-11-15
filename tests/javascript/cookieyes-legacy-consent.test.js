const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const trackerFiles = [
    'assets/dev/javascript/tracker.js',
    'assets/js/tracker.js',
];

function loadCookieYesTracker(file, cookie, extra = {}) {
    const listeners = {};
    let userTrackerStarts = 0;
    let eventTrackerStarts = 0;
    let userTrackerStops = 0;
    let eventTrackerStops = 0;

    const context = {
        document: {
            cookie,
            prerendering: false,
            addEventListener(type, callback) {
                (listeners[type] ||= []).push(callback);
            },
        },
        console: {log() {}, warn() {}, error() {}},
        WpStatisticsUserTracker: {init() { userTrackerStarts++; }, stop() { userTrackerStops++; }},
        WpStatisticsEventTracker: {init() { eventTrackerStarts++; }, stop() { eventTrackerStops++; }},
        WP_Statistics_Tracker_Object: {
            option: {
                consentIntegration: {
                    name: 'cookieyes',
                    status: {legacy: true},
                },
            },
        },
        setTimeout(callback) { callback(); },
        ...extra,
    };
    context.window = context;

    vm.createContext(context);
    vm.runInContext(fs.readFileSync(file, 'utf8'), context, {filename: file});
    listeners.DOMContentLoaded[0]();

    return {
        get userTrackerStarts() { return userTrackerStarts; },
        get eventTrackerStarts() { return eventTrackerStarts; },
        get userTrackerStops() { return userTrackerStops; },
        get eventTrackerStops() { return eventTrackerStops; },
        setCookie(value) { context.document.cookie = value; },
        dispatch(type, event) { (listeners[type] || []).forEach(callback => callback(event)); },
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

const bannerClick = {target: {closest: () => ({})}};

for (const file of trackerFiles) {
    test(`${file} legacy banner click stops tracking when consent is withdrawn`, () => {
        const tracker = loadCookieYesTracker(
            file,
            'viewed_cookie_policy=yes; cookielawinfo-checkbox-analytics=yes'
        );
        assert.equal(tracker.userTrackerStops, 0);

        tracker.setCookie('viewed_cookie_policy=yes; cookielawinfo-checkbox-analytics=no');
        tracker.dispatch('click', bannerClick);

        assert.equal(tracker.userTrackerStops, 1);
        assert.equal(tracker.eventTrackerStops, 1);
    });

    test(`${file} legacy banner click starts tracking on accept, unless the banner reloads the page`, () => {
        const declined = 'viewed_cookie_policy=yes; cookielawinfo-checkbox-analytics=no';
        const accepted = 'viewed_cookie_policy=yes; cookielawinfo-checkbox-analytics=yes';

        const noReload = loadCookieYesTracker(file, declined);
        noReload.setCookie(accepted);
        noReload.dispatch('click', bannerClick);
        assert.equal(noReload.userTrackerStarts, 1);

        const reload = loadCookieYesTracker(file, declined, {CLI: {settings: {accept_close_reload: true}}});
        reload.setCookie(accepted);
        reload.dispatch('click', bannerClick);
        assert.equal(reload.userTrackerStarts, 0, 'the reloaded page sends the hit');
    });

    test(`${file} ignores clicks outside the legacy banner`, () => {
        const tracker = loadCookieYesTracker(file, 'viewed_cookie_policy=yes; cookielawinfo-checkbox-analytics=yes');
        tracker.setCookie('viewed_cookie_policy=yes; cookielawinfo-checkbox-analytics=no');
        tracker.dispatch('click', {target: {closest: () => null}});
        assert.equal(tracker.userTrackerStops, 0);
    });

    test(`${file} CookieYes rejected event stops tracking even if a stale cookie says yes`, () => {
        const tracker = loadCookieYesTracker(file, 'cookieyes-consent=consent:yes,analytics:yes');
        tracker.dispatch('cookieyes_consent_update', {detail: {rejected: ['analytics'], accepted: []}});
        assert.equal(tracker.userTrackerStops, 1);
    });
}
