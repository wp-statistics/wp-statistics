const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const trackerBundles = {
    source: [
        'assets/dev/javascript/user-tracker.js',
        'assets/dev/javascript/event-tracker.js',
        'assets/dev/javascript/tracker.js',
    ],
    built: ['assets/js/tracker.js'],
};

function loadTracker(files, integration) {
    const documentListeners = {};
    const windowListeners = {};
    let hits = 0;
    let consentGranted = true;

    class FakeXMLHttpRequest {
        open() {}
        setRequestHeader() {}
        send() { hits++; }
    }

    const addListener = listeners => (type, callback) => {
        (listeners[type] ||= []).push(callback);
    };

    const dispatch = (listeners, type, detail = {}) => {
        for (const callback of listeners[type] || []) {
            callback({type, detail});
        }
    };

    const context = {
        location: {href: 'https://example.test/', pathname: '/', search: '', host: 'example.test'},
        history: {pushState() {}, replaceState() {}},
        navigator: {},
        document: {
            cookie: '',
            prerendering: false,
            referrer: '',
            addEventListener: addListener(documentListeners),
            getElementById() { return null; },
            querySelectorAll() { return []; },
        },
        addEventListener: addListener(windowListeners),
        removeEventListener() {},
        console: {log() {}, warn() {}, error() {}},
        TextEncoder,
        URLSearchParams,
        XMLHttpRequest: FakeXMLHttpRequest,
        btoa: value => Buffer.from(value, 'binary').toString('base64'),
        fetch: async () => ({ok: true}),
        setTimeout(callback) { callback(); },
        clearTimeout() {},
        wp_has_consent(category) {
            return consentGranted && category === 'statistics';
        },
        getCkyConsent() {
            return {categories: {analytics: consentGranted}};
        },
        consentApi: {
            consent() {
                return consentGranted ? Promise.resolve() : Promise.reject(new Error('denied'));
            },
            consentSync(service) {
                if (service === 'wp-statistics-with-data-processing') {
                    // Real Cookie Banner reports cookieOptIn=true when a service is absent.
                    return {cookie: null, cookieOptIn: true};
                }

                return {cookie: true, cookieOptIn: consentGranted};
            },
        },
        BorlabsCookie: {
            Consents: {
                hasConsent() { return consentGranted; },
            },
        },
        WP_Statistics_Tracker_Object: {
            option: {
                consentIntegration: {name: integration, status: {}},
                dntEnabled: false,
                isPreview: false,
                bypassAdBlockers: false,
            },
            hitParams: {endpoint: 'hit'},
            requestUrl: 'https://example.test',
            ajaxUrl: 'https://example.test/ajax',
            isLegacyEventLoaded: false,
        },
    };
    context.window = context;

    vm.createContext(context);
    for (const file of files) {
        vm.runInContext(fs.readFileSync(file, 'utf8'), context, {filename: file});
    }

    return {
        context,
        dispatchDocument(type, detail) { dispatch(documentListeners, type, detail); },
        getHits() { return hits; },
        withdraw() { consentGranted = false; },
    };
}

const withdrawals = {
    wp_consent_api(tracker) {
        tracker.dispatchDocument('wp_listen_for_consent_change', {statistics: 'deny'});
    },
    cookieyes(tracker) {
        tracker.dispatchDocument('cookieyes_consent_update', {rejected: ['analytics']});
    },
    real_cookie_banner(tracker) {
        tracker.dispatchDocument('RCB/OptOut');
    },
    borlabs_cookie(tracker) {
        tracker.dispatchDocument('borlabs-cookie-consent-saved');
    },
};

for (const [bundle, files] of Object.entries(trackerBundles)) {
    for (const [integration, dispatchWithdrawal] of Object.entries(withdrawals)) {
        test(`${bundle} tracker stops SPA hits when ${integration} consent is withdrawn`, async () => {
            const tracker = loadTracker(files, integration);

            tracker.dispatchDocument('DOMContentLoaded');
            await Promise.resolve();
            await Promise.resolve();
            assert.equal(tracker.getHits(), 1, 'initial consent starts tracking');

            tracker.withdraw();
            dispatchWithdrawal(tracker);
            await Promise.resolve();

            tracker.context.location.href = 'https://example.test/next';
            tracker.context.location.pathname = '/next';
            tracker.context.history.pushState({}, '', '/next');
            await Promise.resolve();

            assert.equal(tracker.getHits(), 1, 'withdrawal prevents another hit');
        });
    }
}
