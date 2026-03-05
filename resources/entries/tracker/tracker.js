/**
 * WP Statistics Consent Adapter Registry
 *
 * Each consent provider registers a small adapter that knows how to check
 * for consent. The tracker doesn't need to know about specific providers.
 */
if (!window.WpStatisticsConsentAdapters) {
    window.WpStatisticsConsentAdapters = {};
}

/**
 * None — no consent management, track immediately.
 */
WpStatisticsConsentAdapters['none'] = {
    init: function (config, callback) {
        callback();
    }
};

/**
 * WP Consent API — check wp_has_consent(), listen for changes.
 */
WpStatisticsConsentAdapters['wp_consent_api'] = {
    init: function (config, callback) {
        var consentLevel     = config.consentLevel;
        var trackAnonymously = config.trackAnonymously;
        var initialized      = false;

        function initOnce() {
            if (!initialized) {
                initialized = true;
                callback();
            }
        }

        /**
         * Check if a consent type has been configured (optin/optout).
         * wp_has_consent() returns true when consent_type is unset, assuming
         * "no consent management exists." But since we're in the wp_consent_api
         * adapter, we know a consent plugin should be active. If consent_type
         * isn't set yet (e.g., CookieYes only sets it on banner interaction,
         * not on page refresh), we must not trust wp_has_consent().
         */
        function isConsentTypeConfigured() {
            return (typeof window.wp_consent_type !== 'undefined' && window.wp_consent_type) ||
                (typeof window.wp_fallback_consent_type !== 'undefined' && !!window.wp_fallback_consent_type);
        }

        // If tracking anonymously or consent already granted, init immediately
        if (trackAnonymously || consentLevel === 'disabled' ||
            (typeof wp_has_consent === 'function' && isConsentTypeConfigured() && wp_has_consent(consentLevel))) {
            initOnce();
        } else if (!trackAnonymously && consentLevel !== 'disabled' && typeof wp_has_consent !== 'function') {
            console.warn('WP Statistics: wp_has_consent() not available. Tracker will not initialize until consent API loads.');
        }

        // Listen for consent changes
        document.addEventListener('wp_listen_for_consent_change', function (e) {
            var changedConsentCategory = e.detail;
            for (var key in changedConsentCategory) {
                if (changedConsentCategory.hasOwnProperty(key) && key === consentLevel && changedConsentCategory[key] === 'allow') {
                    if (!initialized) {
                        initOnce();
                    } else if (trackAnonymously) {
                        // Already initialized anonymously, now consent granted — re-record
                        WpStatisticsUserTracker.checkHitRequestConditions();
                    }
                }
            }
        });
    }
};

/**
 * Real Cookie Banner — use consentApi promise.
 */
WpStatisticsConsentAdapters['real_cookie_banner'] = {
    init: function (config, callback) {
        if (!window.consentApi || typeof window.consentApi.consent !== 'function') {
            console.warn('WP Statistics: Real Cookie Banner consentApi not found. Tracking disabled until consent API loads.');
            return;
        }

        window.consentApi.consent('wp-statistics')
            .then(function () {
                callback();
            })
            .catch(function (e) {
                console.log('WP Statistics: RCB base consent not given, checking data processing consent.', e);
                try {
                    var dataProcessing = window.consentApi.consentSync('wp-statistics-with-data-processing');
                    if (dataProcessing && dataProcessing.cookie != null && dataProcessing.cookieOptIn) {
                        callback();
                    }
                } catch (err) {
                    console.warn('WP Statistics: Error checking RCB data processing consent.', err);
                }
            });
    }
};

/**
 * Borlabs Cookie — Borlabs blocks the script itself, so if we're running, consent is given.
 */
WpStatisticsConsentAdapters['borlabs_cookie'] = {
    init: function (config, callback) {
        callback();
    }
};

/**
 * Main tracker initialization.
 */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof WP_Statistics_Tracker_Object === 'undefined' || !WP_Statistics_Tracker_Object.option) {
        console.error('WP Statistics: Tracker configuration (WP_Statistics_Tracker_Object) is missing. Tracking disabled.');
        return;
    }

    var config = WP_Statistics_Tracker_Object.option.consent || {};
    var mode   = config.mode || 'none';

    var adapter = WpStatisticsConsentAdapters[mode];

    if (adapter) {
        adapter.init(config, function () {
            WpStatisticsUserTracker.init();
            WpStatisticsEventTracker.init();
        });
    } else {
        // Unknown mode — fail closed, do not track
        console.warn('WP Statistics: Unknown consent mode "' + mode + '". Tracking disabled.');
    }
});
