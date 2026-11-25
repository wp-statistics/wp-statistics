const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

// WP Consent API integration when the cookie banner has not set a consent type yet. CookieYes
// before 3.5.9 sets window.wp_consent_type only on its own consent event, which does not fire for a
// returning visitor, so wp_has_consent() allowed everything and a visitor who clicked Reject was counted.

const trackerBundles = {
    source: [
        'assets/dev/javascript/user-tracker.js',
        'assets/dev/javascript/event-tracker.js',
        'assets/dev/javascript/tracker.js',
    ],
    built: ['assets/js/tracker.js'],
};

function loadTracker(files, options = {}) {
    const documentListeners = {};
    const windowListeners = {};
    let hits = 0;

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
            cookie: options.cookie || '',
            readyState: 'loading',
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
        XMLHttpRequest: class {
            open() {}
            setRequestHeader() {}
            send() { hits++; }
        },
        btoa: value => Buffer.from(value, 'binary').toString('base64'),
        fetch: async () => ({ok: true}),
        setTimeout(callback) { callback(); },
        clearTimeout() {},
        consent_api: {cookie_prefix: 'wp_consent'},
        wp_fallback_consent_type: options.fallbackType || '',
        waitfor_consent_hook: Boolean(options.waitfor),
        WP_Statistics_Tracker_Object: {
            option: {
                consentIntegration: {name: 'wp_consent_api', status: {}},
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
    // wp_has_consent() as WP Consent API 2.0 defines it (assets/js/wp-consent-api.js).
    vm.runInContext(`
        function consent_api_get_cookie(name) {
            name = name + '=';
            const cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i].trim();
                if (cookie.indexOf(name) === 0) return cookie.substring(name.length);
            }
            return '';
        }
        function wp_has_consent(category) {
            const type = typeof window.wp_consent_type !== 'undefined' ? window.wp_consent_type : window.wp_fallback_consent_type;
            const value = consent_api_get_cookie(consent_api.cookie_prefix + '_' + category);
            if (!type) return true;
            if (type.indexOf('optout') !== -1 && value === '') return true;
            return value === 'allow';
        }
        function wp_set_consent(category, value) {
            document.cookie = consent_api.cookie_prefix + '_' + category + '=' + value;
            const detail = {};
            detail[category] = value;
            dispatchConsentChange(detail);
        }
    `, Object.assign(context, {
        dispatchConsentChange: detail => dispatch(documentListeners, 'wp_listen_for_consent_change', detail),
    }));
    for (const file of files) {
        vm.runInContext(fs.readFileSync(file, 'utf8'), context, {filename: file});
    }

    return {
        context,
        dispatchDocument(type, detail) { dispatch(documentListeners, type, detail); },
        dispatchWindow(type, detail) { dispatch(windowListeners, type, detail); },
        getHits() { return hits; },
        // What CookieYes' WP Consent API bridge does: set the type, announce it, then pass each choice.
        bannerSetsType(type, statistics) {
            context.wp_consent_type = type;
            dispatch(documentListeners, 'wp_consent_type_defined');
            if (statistics) {
                vm.runInContext(`wp_set_consent('statistics', '${statistics}'); wp_set_consent('statistics-anonymous', '${statistics}');`, context);
            }
        },
    };
}

const rejected = 'wp_consent_statistics=deny; wp_consent_statistics-anonymous=deny';
const accepted = 'wp_consent_statistics=allow; wp_consent_statistics-anonymous=allow';

for (const [bundle, files] of Object.entries(trackerBundles)) {
    test(`${bundle} tracker: a returning visitor who rejected is not counted when the banner never sets the type`, () => {
        const tracker = loadTracker(files, {cookie: rejected});
        tracker.dispatchDocument('DOMContentLoaded');
        tracker.dispatchWindow('load');
        assert.equal(tracker.getHits(), 0);
    });

    test(`${bundle} tracker: a returning visitor who accepted is counted without waiting for the type`, () => {
        const tracker = loadTracker(files, {cookie: accepted});
        tracker.dispatchDocument('DOMContentLoaded');
        assert.equal(tracker.getHits(), 1);
    });

    test(`${bundle} tracker: a first visit waits for the banner to set the type, then is not counted before an answer`, () => {
        const tracker = loadTracker(files);
        tracker.dispatchDocument('DOMContentLoaded');
        assert.equal(tracker.getHits(), 0, 'no hit while the type is unknown');

        tracker.bannerSetsType('optin', 'deny');
        tracker.dispatchWindow('load');
        assert.equal(tracker.getHits(), 0, 'banner defaults deny statistics');

        tracker.context.document.cookie = accepted;
        tracker.dispatchDocument('wp_listen_for_consent_change', {statistics: 'allow'});
        assert.equal(tracker.getHits(), 1, 'accepting starts tracking');
    });

    test(`${bundle} tracker: an explicit deny is respected even on an opt-out site`, () => {
        const tracker = loadTracker(files, {cookie: 'wp_consent_statistics=deny', fallbackType: 'optout'});
        tracker.dispatchDocument('DOMContentLoaded');
        tracker.dispatchWindow('load');
        assert.equal(tracker.getHits(), 0);
    });

    test(`${bundle} tracker: without any banner it counts once the page has loaded, as WP Consent API allows`, () => {
        const tracker = loadTracker(files);
        tracker.dispatchDocument('DOMContentLoaded');
        tracker.dispatchWindow('load');
        assert.equal(tracker.getHits(), 1);
    });

    test(`${bundle} tracker: a type set on the server is used right away`, () => {
        const tracker = loadTracker(files, {fallbackType: 'optin'});
        tracker.dispatchDocument('DOMContentLoaded');
        tracker.dispatchWindow('load');
        assert.equal(tracker.getHits(), 0, 'opt-in without an answer is not counted');

        const optout = loadTracker(files, {fallbackType: 'optout'});
        optout.dispatchDocument('DOMContentLoaded');
        assert.equal(optout.getHits(), 1, 'opt-out without an answer is counted');
    });

    test(`${bundle} tracker: with waitfor_consent_hook it waits for the banner's type`, () => {
        const tracker = loadTracker(files, {waitfor: true, fallbackType: 'optout'});
        tracker.dispatchDocument('DOMContentLoaded');
        tracker.dispatchWindow('load');
        assert.equal(tracker.getHits(), 0, 'no decision before the type is defined');

        tracker.bannerSetsType('optout');
        assert.equal(tracker.getHits(), 1);
    });
}
