const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const trackerFiles = [
    'assets/dev/javascript/tracker.js',
    'assets/js/tracker.js',
];

function loadCookieYesTracker(file, {cookie = '', anonymousTracking = false, consent = null} = {}) {
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
                    status: {
                        legacy: false,
                        anonymous_tracking: anonymousTracking,
                    },
                },
            },
        },
        setTimeout(callback) { callback(); },
    };

    if (consent !== null) {
        context.getCkyConsent = () => consent;
    }

    context.window = context;

    vm.createContext(context);
    vm.runInContext(fs.readFileSync(file, 'utf8'), context, {filename: file});
    listeners.DOMContentLoaded[0]();

    return {
        dispatch(type, detail) {
            for (const listener of listeners[type] || []) {
                listener({detail});
            }
        },
        get userTrackerStarts() { return userTrackerStarts; },
        get eventTrackerStarts() { return eventTrackerStarts; },
    };
}

for (const file of trackerFiles) {
    test(`${file} starts anonymous tracking only after an explicit CookieYes decline`, () => {
        const declined = loadCookieYesTracker(file, {
            anonymousTracking: true,
            consent: {
                categories: {analytics: false},
                isUserActionCompleted: true,
            },
        });
        assert.equal(declined.userTrackerStarts, 1);
        assert.equal(declined.eventTrackerStarts, 1);

        const unanswered = loadCookieYesTracker(file, {
            anonymousTracking: true,
            consent: {
                categories: {analytics: false},
                isUserActionCompleted: false,
            },
        });
        assert.equal(unanswered.userTrackerStarts, 0);
        assert.equal(unanswered.eventTrackerStarts, 0);

        const missingAnalyticsCategory = loadCookieYesTracker(file, {
            anonymousTracking: true,
            consent: {
                categories: {},
                isUserActionCompleted: true,
            },
        });
        assert.equal(missingAnalyticsCategory.userTrackerStarts, 0);
        assert.equal(missingAnalyticsCategory.eventTrackerStarts, 0);

        const disabled = loadCookieYesTracker(file, {
            anonymousTracking: false,
            consent: {
                categories: {analytics: false},
                isUserActionCompleted: true,
            },
        });
        assert.equal(disabled.userTrackerStarts, 0);
        assert.equal(disabled.eventTrackerStarts, 0);
    });

    test(`${file} starts anonymous tracking when CookieYes reports analytics rejected`, () => {
        const tracker = loadCookieYesTracker(file, {
            anonymousTracking: true,
            consent: {
                categories: {analytics: false},
                isUserActionCompleted: false,
            },
        });

        tracker.dispatch('cookieyes_consent_update', {
            accepted: ['necessary'],
            rejected: ['analytics'],
        });

        assert.equal(tracker.userTrackerStarts, 1);
        assert.equal(tracker.eventTrackerStarts, 1);
    });

    test(`${file} uses the CookieYes cookie fallback for an explicit decline`, () => {
        const declined = loadCookieYesTracker(file, {
            anonymousTracking: true,
            cookie: 'cookieyes-consent=consentid%3Aabc%2Caction%3Ayes%2Canalytics%3Ano',
        });
        assert.equal(declined.userTrackerStarts, 1);

        const unanswered = loadCookieYesTracker(file, {
            anonymousTracking: true,
            cookie: 'cookieyes-consent=consentid%3Aabc%2Caction%3Ano%2Canalytics%3Ano',
        });
        assert.equal(unanswered.userTrackerStarts, 0);

        const missingAnalyticsCategory = loadCookieYesTracker(file, {
            anonymousTracking: true,
            cookie: 'cookieyes-consent=consentid%3Aabc%2Caction%3Ayes%2Cnecessary%3Ayes',
        });
        assert.equal(missingAnalyticsCategory.userTrackerStarts, 0);
    });
}
