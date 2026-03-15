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
 * WP Consent API — check wp_has_consent() for statistics / statistics-anonymous,
 * listen for consent changes on both categories.
 */
WpStatisticsConsentAdapters['wp_consent_api'] = {
    init: function (config, callback) {
        var initialized = false;

        function initOnce() {
            if (!initialized) {
                initialized = true;
                callback();
            }
        }

        // When consent_type is not configured (e.g., CookieYes before banner
        // interaction), wp_has_consent() defaults to true. Set the fallback to
        // 'optin' so it defaults to deny — the safe behavior when we know a
        // consent plugin is active.
        if (typeof window.wp_consent_type === 'undefined' && typeof window.wp_fallback_consent_type === 'undefined') {
            window.wp_fallback_consent_type = 'optin';
        }

        if (typeof wp_has_consent === 'function') {
            if (wp_has_consent('statistics') || wp_has_consent('statistics-anonymous')) {
                initOnce();
            }
        } else {
            console.warn('WP Statistics: wp_has_consent() is not available. Tracker will wait for consent change events.');
        }

        // Listen for consent changes on both categories
        document.addEventListener('wp_listen_for_consent_change', function (e) {
            var changed = e.detail;
            if (changed && (changed['statistics'] === 'allow' || changed['statistics-anonymous'] === 'allow')) {
                if (!initialized) {
                    initOnce();
                } else {
                    // Consent upgraded (e.g., anonymous → full) — re-record
                    WpStatisticsUserTracker.checkHitRequestConditions();
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
