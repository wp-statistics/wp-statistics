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

function loadTracker(files, integration, options = {}) {
    const documentListeners = {};
    const windowListeners = {};
    let hits = 0;
    let consentGranted = true;
    let resolveInitialConsent;
    const initialConsent = options.deferInitialConsent
        ? new Promise(resolve => { resolveInitialConsent = resolve; })
        : null;

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
                if (initialConsent) {
                    return initialConsent;
                }

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
        dispatchWindow(type, detail) { dispatch(windowListeners, type, detail); },
        getHits() { return hits; },
        grant() { consentGranted = true; },
        resolveInitialConsent() { resolveInitialConsent(); },
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
        tracker.dispatchWindow('borlabs-cookie-consent-saved');
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

    test(`${bundle} tracker does not define generic global function names`, () => {
        const tracker = loadTracker(files, 'cookieyes');

        assert.equal(typeof tracker.context.startTracking, 'undefined');
        assert.equal(typeof tracker.context.stopTracking, 'undefined');
    });

    test(`${bundle} tracker does not start when CookieYes reports a decline`, async () => {
        const tracker = loadTracker(files, 'cookieyes');

        tracker.withdraw();
        tracker.dispatchDocument('DOMContentLoaded');
        tracker.dispatchDocument('cookieyes_consent_update', {rejected: ['analytics']});
        await Promise.resolve();

        assert.equal(tracker.getHits(), 0, 'a decline records no visit');
    });

    test(`${bundle} tracker keeps counting on Borlabs when Anonymous Tracking is on`, async () => {
        const tracker = loadTracker(files, 'borlabs_cookie');

        tracker.context.WP_Statistics_Tracker_Object.option.consentIntegration.status = {anonymous_tracking: true};
        tracker.dispatchDocument('DOMContentLoaded');
        await Promise.resolve();
        tracker.withdraw();
        tracker.dispatchWindow('borlabs-cookie-consent-saved');

        tracker.context.location.href = 'https://example.test/next';
        tracker.context.location.pathname = '/next';
        tracker.context.history.pushState({}, '', '/next');
        await Promise.resolve();

        assert.equal(tracker.getHits(), 2, 'the second page is still counted');
    });

    test(`${bundle} tracker stays stopped when Real Cookie Banner consent resolves after withdrawal`, async () => {
        const tracker = loadTracker(files, 'real_cookie_banner', {deferInitialConsent: true});

        tracker.dispatchDocument('DOMContentLoaded');
        tracker.withdraw();
        tracker.dispatchDocument('RCB/OptOut');
        tracker.resolveInitialConsent();
        await Promise.resolve();
        await Promise.resolve();

        tracker.context.location.href = 'https://example.test/next';
        tracker.context.location.pathname = '/next';
        tracker.context.history.pushState({}, '', '/next');
        await Promise.resolve();

        assert.equal(tracker.getHits(), 0, 'stale initial consent must not restart tracking');
    });

    test(`${bundle} tracker records the current SPA page when consent is restored`, async () => {
        const tracker = loadTracker(files, 'wp_consent_api');

        tracker.dispatchDocument('DOMContentLoaded');
        await Promise.resolve();
        assert.equal(tracker.getHits(), 1, 'initial page is tracked');

        tracker.withdraw();
        tracker.dispatchDocument('wp_listen_for_consent_change', {statistics: 'deny'});
        tracker.context.location.href = 'https://example.test/next';
        tracker.context.location.pathname = '/next';
        tracker.context.history.pushState({}, '', '/next');
        await Promise.resolve();
        assert.equal(tracker.getHits(), 1, 'page change is held while consent is withdrawn');

        tracker.grant();
        tracker.dispatchDocument('wp_listen_for_consent_change', {statistics: 'allow'});
        await Promise.resolve();
        assert.equal(tracker.getHits(), 2, 'the current page is tracked after consent returns');

        tracker.dispatchDocument('wp_listen_for_consent_change', {statistics: 'allow'});
        await Promise.resolve();
        assert.equal(tracker.getHits(), 2, 'repeated consent updates do not duplicate the hit');
    });
}
