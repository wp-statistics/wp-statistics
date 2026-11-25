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
    const requests = [];
    let legacyEvents = 0;
    let consentGranted = true;
    let resolveInitialConsent;
    const initialConsent = options.deferInitialConsent
        ? new Promise(resolve => { resolveInitialConsent = resolve; })
        : null;

    class FakeXMLHttpRequest {
        open() {}
        setRequestHeader() {}
        send(body) {
            hits++;
            requests.push(Object.fromEntries(new URLSearchParams(body)));
        }
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
        location: (() => {
            const url = new URL(options.href || 'https://example.test/');
            return {href: url.href, pathname: url.pathname, search: url.search, host: url.host};
        })(),
        history: {pushState() {}, replaceState() {}},
        navigator: {},
        document: {
            cookie: '',
            readyState: options.readyState || 'loading',
            prerendering: false,
            referrer: options.referrer || '',
            addEventListener: addListener(documentListeners),
            getElementById() { return null; },
            querySelectorAll() { return []; },
        },
        addEventListener: addListener(windowListeners),
        removeEventListener() {},
        console: {log() {}, warn() {}, error() {}},
        TextEncoder,
        URLSearchParams,
        URL,
        XMLHttpRequest: FakeXMLHttpRequest,
        btoa: value => Buffer.from(value, 'binary').toString('base64'),
        fetch: async () => ({ok: true}),
        countLegacyEvent() { legacyEvents++; },
        setTimeout(callback) { callback(); },
        clearTimeout() {},
        wp_has_consent(category) {
            return consentGranted && category === 'statistics';
        },
        // A cookie banner that has set its consent type, as WP Consent API expects.
        wp_consent_type: 'optin',
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
            isLegacyEventLoaded: Boolean(options.legacyEventLoaded),
        },
    };
    if (options.noBorlabs) {
        delete context.BorlabsCookie;
    }
    if (options.sessionStorage) {
        context.sessionStorage = options.sessionStorage;
    }
    if (options.performance) {
        context.performance = options.performance;
    }
    if (options.cookie) {
        context.document.cookie = options.cookie;
    }
    context.window = context;

    vm.createContext(context);
    // Older Data Plus builds declare their tracker with a top-level `let`, which never becomes a window property.
    vm.runInContext('let wpStatisticsDataPlusEvents = {sendEventData() { countLegacyEvent(); return Promise.resolve(); }};', context);
    for (const file of files) {
        vm.runInContext(fs.readFileSync(file, 'utf8'), context, {filename: file});
    }

    return {
        context,
        dispatchDocument(type, detail) { dispatch(documentListeners, type, detail); },
        dispatchWindow(type, detail) { dispatch(windowListeners, type, detail); },
        getHits() { return hits; },
        getRequests() { return requests; },
        getLegacyEvents() { return legacyEvents; },
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

    test(`${bundle} tracker stops legacy Data Plus events when consent is withdrawn`, async () => {
        const tracker = loadTracker(files, 'wp_consent_api', {legacyEventLoaded: true});

        tracker.dispatchDocument('DOMContentLoaded');
        await vm.runInContext('wpStatisticsDataPlusEvents.sendEventData()', tracker.context);
        assert.equal(tracker.getLegacyEvents(), 1, 'legacy event is sent while consent is active');

        tracker.withdraw();
        tracker.dispatchDocument('wp_listen_for_consent_change', {statistics: 'deny'});
        await vm.runInContext('wpStatisticsDataPlusEvents.sendEventData()', tracker.context);

        assert.equal(tracker.getLegacyEvents(), 1, 'legacy event is blocked after withdrawal');
    });

    test(`${bundle} tracker coalesces rapid SPA changes around consent restoration`, async () => {
        const tracker = loadTracker(files, 'wp_consent_api');
        const callbacks = [];

        tracker.dispatchDocument('DOMContentLoaded');
        await Promise.resolve();
        tracker.context.WpStatisticsUserTracker.updateTrackerObject = callback => callbacks.push(callback);

        tracker.withdraw();
        tracker.dispatchDocument('wp_listen_for_consent_change', {statistics: 'deny'});
        tracker.context.location.href = 'https://example.test/one';
        tracker.context.location.pathname = '/one';
        tracker.context.history.pushState({}, '', '/one');
        tracker.context.location.href = 'https://example.test/two';
        tracker.context.location.pathname = '/two';
        tracker.context.history.pushState({}, '', '/two');

        callbacks[0]();
        tracker.grant();
        tracker.dispatchDocument('wp_listen_for_consent_change', {statistics: 'allow'});
        callbacks[1]();

        assert.equal(tracker.getHits(), 2, 'only the final SPA page is recorded once');
    });

    test(`${bundle} tracker waits for the current SPA page before restoring a pending hit`, async () => {
        const tracker = loadTracker(files, 'wp_consent_api');
        const callbacks = [];

        tracker.dispatchDocument('DOMContentLoaded');
        await Promise.resolve();
        tracker.context.WpStatisticsUserTracker.updateTrackerObject = callback => callbacks.push(callback);

        tracker.withdraw();
        tracker.dispatchDocument('wp_listen_for_consent_change', {statistics: 'deny'});
        tracker.context.location.href = 'https://example.test/one';
        tracker.context.location.pathname = '/one';
        tracker.context.history.pushState({}, '', '/one');
        callbacks[0]({
            ...tracker.context.WP_Statistics_Tracker_Object,
            hitParams: {endpoint: 'hit', source_id: 101},
        });

        tracker.context.WpStatisticsUserTracker.lastUrlChangeAt -= 1000;
        tracker.context.location.href = 'https://example.test/two';
        tracker.context.location.pathname = '/two';
        tracker.context.history.pushState({}, '', '/two');

        tracker.grant();
        tracker.dispatchDocument('wp_listen_for_consent_change', {statistics: 'allow'});
        assert.equal(tracker.getHits(), 1, 'restoration waits while the current page identity is unresolved');

        callbacks[1]({
            ...tracker.context.WP_Statistics_Tracker_Object,
            hitParams: {endpoint: 'hit', source_id: 102},
        });

        const restoredRequests = tracker.getRequests().slice(1).map(request => ({
            path: Buffer.from(request.page_uri, 'base64').toString('utf8'),
            sourceId: request.source_id,
        }));
        assert.deepEqual(restoredRequests, [
            {path: '/two', sourceId: '102'},
        ], 'restoration records the current page exactly once with its matching identity');
    });

    test(`${bundle} tracker records every separate SPA navigation`, async () => {
        const tracker = loadTracker(files, 'wp_consent_api');
        const callbacks = [];

        tracker.dispatchDocument('DOMContentLoaded');
        await Promise.resolve();
        const userTracker = tracker.context.WpStatisticsUserTracker;
        userTracker.updateTrackerObject = callback => callbacks.push(callback);

        tracker.context.location.href = 'https://example.test/one';
        tracker.context.location.pathname = '/one';
        tracker.context.history.pushState({}, '', '/one');
        userTracker.lastUrlChangeAt -= 1000; // the visitor stays on /one for a second
        tracker.context.location.href = 'https://example.test/two';
        tracker.context.location.pathname = '/two';
        tracker.context.history.pushState({}, '', '/two');

        callbacks[0]({
            ...tracker.context.WP_Statistics_Tracker_Object,
            hitParams: {endpoint: 'hit', source_id: 101},
        });
        callbacks[1]({
            ...tracker.context.WP_Statistics_Tracker_Object,
            hitParams: {endpoint: 'hit', source_id: 102},
        });

        const navigationRequests = tracker.getRequests().slice(1).map(request => ({
            path: Buffer.from(request.page_uri, 'base64').toString('utf8'),
            sourceId: request.source_id,
        }));
        assert.deepEqual(navigationRequests, [
            {path: '/one', sourceId: '101'},
            {path: '/two', sourceId: '102'},
        ], 'each separate navigation keeps its matching page identity');
    });

    test(`${bundle} tracker keeps older separate SPA navigations when a later pair is coalesced`, async () => {
        const tracker = loadTracker(files, 'wp_consent_api');
        const callbacks = [];

        tracker.dispatchDocument('DOMContentLoaded');
        await Promise.resolve();
        const userTracker = tracker.context.WpStatisticsUserTracker;
        userTracker.updateTrackerObject = callback => callbacks.push(callback);

        for (const path of ['/one', '/two', '/three']) {
            tracker.context.location.href = `https://example.test${path}`;
            tracker.context.location.pathname = path;
            tracker.context.history.pushState({}, '', path);
            if (path !== '/three') {
                userTracker.lastUrlChangeAt -= 1000;
            }
        }

        tracker.context.location.href = 'https://example.test/four';
        tracker.context.location.pathname = '/four';
        tracker.context.history.replaceState({}, '', '/four');

        callbacks.forEach(callback => callback());

        assert.equal(tracker.getHits(), 4, 'the first page, /one, /two and coalesced /four are recorded');
    });

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

for (const [bundle, files] of Object.entries(trackerBundles)) {
    // Borlabs Cookie releases the tracker only after consent, when DOMContentLoaded has already fired.
    for (const integration of ['', 'borlabs_cookie']) {
        test(`${bundle} tracker loaded after the page is ready still counts the visit (${integration || 'no integration'})`, () => {
            const tracker = loadTracker(files, integration, {readyState: 'complete'});
            assert.equal(tracker.getHits(), 1);
            tracker.dispatchDocument('DOMContentLoaded');
            assert.equal(tracker.getHits(), 1, 'no second hit');
        });
    }
}

function memoryStorage() {
    const items = {};
    return {
        getItem(key) { return Object.prototype.hasOwnProperty.call(items, key) ? items[key] : null; },
        setItem(key, value) { items[key] = String(value); },
        removeItem(key) { delete items[key]; },
    };
}

for (const [bundle, files] of Object.entries(trackerBundles)) {
    // Complianz keeps statistics-anonymous allowed when the visitor withdraws, then reloads the page.
    test(`${bundle} tracker does not count the same page again after Complianz withdraws to anonymous`, async () => {
        const storage = memoryStorage();
        const accepted = 'wp_consent_statistics=allow; wp_consent_statistics-anonymous=allow';
        const first = loadTracker(files, 'wp_consent_api', {sessionStorage: storage, cookie: accepted});

        first.dispatchDocument('DOMContentLoaded');
        assert.equal(first.getHits(), 1, 'the page is counted with full consent');

        first.context.document.cookie = 'wp_consent_statistics=deny; wp_consent_statistics-anonymous=allow';
        first.dispatchDocument('wp_listen_for_consent_change', {statistics: 'deny'});
        assert.equal(first.getHits(), 1, 'no hit on the withdrawal itself');

        // The reload Complianz does after the withdrawal.
        const reloaded = loadTracker(files, 'wp_consent_api', {
            sessionStorage: storage,
            cookie: 'wp_consent_statistics=deny; wp_consent_statistics-anonymous=allow',
            performance: {getEntriesByType() { return [{type: 'reload'}]; }},
        });
        reloaded.dispatchDocument('DOMContentLoaded');
        assert.equal(reloaded.getHits(), 0, 'the reloaded page is not counted a second time');

        reloaded.context.location.href = 'https://example.test/next';
        reloaded.context.location.pathname = '/next';
        reloaded.context.history.pushState({}, '', '/next');
        assert.equal(reloaded.getHits(), 1, 'later pages are counted anonymously');

        // A later visit to the same page is counted again.
        const later = loadTracker(files, 'wp_consent_api', {
            sessionStorage: storage,
            cookie: 'wp_consent_statistics=deny; wp_consent_statistics-anonymous=allow',
            performance: {getEntriesByType() { return [{type: 'reload'}]; }},
        });
        later.dispatchDocument('DOMContentLoaded');
        assert.equal(later.getHits(), 1, 'the marker is used once');
    });

    // Sites with ?p= links: a different page right after the withdrawal is a new page, not the reload.
    test(`${bundle} tracker still counts a different ?p= page after a withdrawal to anonymous`, async () => {
        const storage = memoryStorage();
        const first = loadTracker(files, 'wp_consent_api', {
            sessionStorage: storage, href: 'https://example.test/?p=10',
            cookie: 'wp_consent_statistics=allow; wp_consent_statistics-anonymous=allow',
        });
        first.dispatchDocument('DOMContentLoaded');
        first.context.document.cookie = 'wp_consent_statistics=deny; wp_consent_statistics-anonymous=allow';
        first.dispatchDocument('wp_listen_for_consent_change', {statistics: 'deny'});

        const next = loadTracker(files, 'wp_consent_api', {
            sessionStorage: storage, href: 'https://example.test/?p=20', referrer: 'https://example.test/?p=10',
            cookie: 'wp_consent_statistics=deny; wp_consent_statistics-anonymous=allow',
            performance: {getEntriesByType() { return [{type: 'navigate'}]; }},
        });
        next.dispatchDocument('DOMContentLoaded');
        assert.equal(next.getHits(), 1, '/?p=20 is a new page and is counted');
    });

    // Complianz reloads Firefox with a cmplz-force-reload parameter: still the same page.
    test(`${bundle} tracker treats the Complianz Firefox reload URL as the same page`, async () => {
        const storage = memoryStorage();
        const first = loadTracker(files, 'wp_consent_api', {
            sessionStorage: storage, href: 'https://example.test/?p=10',
            cookie: 'wp_consent_statistics=allow; wp_consent_statistics-anonymous=allow',
        });
        first.dispatchDocument('DOMContentLoaded');
        first.context.document.cookie = 'wp_consent_statistics=deny; wp_consent_statistics-anonymous=allow';
        first.dispatchDocument('wp_listen_for_consent_change', {statistics: 'deny'});

        const reloaded = loadTracker(files, 'wp_consent_api', {
            sessionStorage: storage, href: 'https://example.test/?p=10&cmplz-force-reload=123', referrer: 'https://example.test/?p=10',
            cookie: 'wp_consent_statistics=deny; wp_consent_statistics-anonymous=allow',
            performance: {getEntriesByType() { return [{type: 'navigate'}]; }},
        });
        reloaded.dispatchDocument('DOMContentLoaded');
        assert.equal(reloaded.getHits(), 0, 'same page, not counted again');
    });

    // A page change still waiting for its hit when consent is withdrawn: the reload must count it.
    test(`${bundle} tracker counts a page whose hit was still pending when consent was withdrawn`, async () => {
        const storage = memoryStorage();
        const first = loadTracker(files, 'wp_consent_api', {
            sessionStorage: storage, href: 'https://example.test/?p=10',
            cookie: 'wp_consent_statistics=allow; wp_consent_statistics-anonymous=allow',
        });
        first.dispatchDocument('DOMContentLoaded');
        vm.runInContext('WpStatisticsUserTracker.pendingUrlChangeGenerations = {9: true};', first.context);
        first.context.document.cookie = 'wp_consent_statistics=deny; wp_consent_statistics-anonymous=allow';
        first.dispatchDocument('wp_listen_for_consent_change', {statistics: 'deny'});

        const reloaded = loadTracker(files, 'wp_consent_api', {
            sessionStorage: storage, href: 'https://example.test/?p=10',
            cookie: 'wp_consent_statistics=deny; wp_consent_statistics-anonymous=allow',
            performance: {getEntriesByType() { return [{type: 'reload'}]; }},
        });
        reloaded.dispatchDocument('DOMContentLoaded');
        assert.equal(reloaded.getHits(), 1, 'no marker was written, so the reloaded page is counted');
    });

    test(`${bundle} tracker sends nothing more when both statistics categories are denied`, async () => {
        const storage = memoryStorage();
        const tracker = loadTracker(files, 'wp_consent_api', {sessionStorage: storage, cookie: 'wp_consent_statistics=allow; wp_consent_statistics-anonymous=allow'});

        tracker.dispatchDocument('DOMContentLoaded');
        tracker.context.document.cookie = 'wp_consent_statistics=deny; wp_consent_statistics-anonymous=deny';
        tracker.dispatchDocument('wp_listen_for_consent_change', {statistics: 'deny'});
        tracker.dispatchDocument('wp_listen_for_consent_change', {'statistics-anonymous': 'deny'});
        tracker.context.location.href = 'https://example.test/next';
        tracker.context.location.pathname = '/next';
        tracker.context.history.pushState({}, '', '/next');

        assert.equal(tracker.getHits(), 1, 'only the page counted before the deny');
    });

    test(`${bundle} tracker does not start on Borlabs when the visitor declined and the script was not blocked`, () => {
        const tracker = loadTracker(files, 'borlabs_cookie');

        tracker.withdraw();
        tracker.dispatchDocument('DOMContentLoaded');
        tracker.dispatchWindow('borlabs-cookie-consent-saved');
        tracker.context.location.href = 'https://example.test/next';
        tracker.context.location.pathname = '/next';
        tracker.context.history.pushState({}, '', '/next');

        assert.equal(tracker.getHits(), 0, 'a declined visitor is not counted');

        tracker.grant();
        tracker.dispatchWindow('borlabs-cookie-consent-saved');
        assert.equal(tracker.getHits(), 1, 'accepting later counts the current page once');
    });

    test(`${bundle} tracker waits for Borlabs to initialise before deciding`, () => {
        const tracker = loadTracker(files, 'borlabs_cookie', {noBorlabs: true});

        tracker.dispatchDocument('DOMContentLoaded');
        assert.equal(tracker.getHits(), 0, 'nothing is sent before Borlabs answers');

        let consent = false;
        tracker.context.BorlabsCookie = {Consents: {hasConsent: service => consent && service === 'wp-statistics'}};
        tracker.dispatchWindow('borlabs-cookie-after-init');
        assert.equal(tracker.getHits(), 0, 'no consent after init, no hit');

        consent = true;
        tracker.dispatchWindow('borlabs-cookie-consent-saved');
        tracker.dispatchWindow('borlabs-cookie-consent-saved');
        assert.equal(tracker.getHits(), 1, 'one hit once the visitor accepts');
    });

    test(`${bundle} tracker released by Borlabs after consent counts the page once`, () => {
        // Borlabs fires consent-saved before it unblocks the script, so the tracker loads late with consent given.
        const tracker = loadTracker(files, 'borlabs_cookie', {readyState: 'complete'});
        tracker.dispatchWindow('borlabs-cookie-consent-saved');
        assert.equal(tracker.getHits(), 1);
    });
}
