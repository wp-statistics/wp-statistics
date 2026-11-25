function wpStatisticsBootTracker() {
    // Defer until a prerendered document is activated by user navigation.
    // The server drops prerender hits via Sec-Purpose, and activation does
    // not re-run scripts — without this gate the real pageview is lost.
    if (document.prerendering) {
        document.addEventListener('prerenderingchange', wpStatisticsBootTracker, { once: true });
        return;
    }

    const consentIntegration = WP_Statistics_Tracker_Object.option.consentIntegration.name;

    // If there's no consent integration
    if (!consentIntegration) {
        wpStatisticsStartTracking();
    }

    // If Borlabs Cookie integration is enabled
    if (consentIntegration === 'borlabs_cookie') {
        handleBorlabsCookieIntegration();
    }

    // If WP Consent API integration is enabled
    if (consentIntegration === 'wp_consent_api') {
        handleWpConsentApiIntegration();
    }

    // If Real Cookie Banner integration is enabled
    if (consentIntegration === 'real_cookie_banner') {
        handleRealCookieBannerIntegration();
    }

    // If CookieYes integration is enabled
    if (consentIntegration === 'cookieyes') {
        handleCookieYesIntegration();
    }
}

// A consent plugin (Borlabs Cookie, for example) can release this script only after
// the visitor accepts, when DOMContentLoaded has already fired. Start right away then.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', wpStatisticsBootTracker);
} else {
    wpStatisticsBootTracker();
}

function wpStatisticsStartTracking(options) {
    WpStatisticsUserTracker.init(options);
    WpStatisticsEventTracker.init();
}

function wpStatisticsStopTracking() {
    WpStatisticsUserTracker.stop();
    WpStatisticsEventTracker.stop();
}

function handleWpConsentApiIntegration() {
    // The visitor's stored answer, as WP Consent API saves it: "allow", "deny" or "" (no answer yet).
    const storedChoice = (category) => {
        const prefix = (window.consent_api && window.consent_api.cookie_prefix) || 'wp_consent';
        const match = document.cookie.match(new RegExp('(?:^|;\\s*)' + prefix + '_' + category + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : '';
    };

    const hasStoredChoice = () => storedChoice('statistics') !== '' || storedChoice('statistics-anonymous') !== '';

    const hasStatisticsConsent = () => {
        // An answer the visitor gave always counts. Without a consent type (some banners only set it
        // after the page has loaded, or not at all for a returning visitor) wp_has_consent() would
        // allow everything, so a visitor who clicked Reject would still be counted.
        if (hasStoredChoice()) {
            return storedChoice('statistics') === 'allow' || storedChoice('statistics-anonymous') === 'allow';
        }

        return wp_has_consent('statistics') || wp_has_consent('statistics-anonymous');
    };

    // The cookie banner sets window.wp_consent_type (or tells WP Consent API on the server) and fires
    // wp_consent_type_defined. Until then, wp_has_consent() treats the site as having no banner and
    // allows everything, even before the visitor has seen the banner.
    const typeKnown = () => typeof window.wp_consent_type !== 'undefined' || (!window.waitfor_consent_hook && !!window.wp_fallback_consent_type);

    // Some banners (Complianz, for example) reload the page when the visitor withdraws consent but keep
    // statistics-anonymous allowed. The page was already counted with full consent, so the reload must not
    // count it again under a new, anonymous visitor. Later pages are counted anonymously.
    const downgradeKey = 'wp_statistics_consent_downgrade';
    // Page identity: path plus query string (sites with ?p=10 links), without the parameter Complianz adds
    // to force a reload in Firefox.
    const pageKey = href => {
        try {
            const url = new URL(href, window.location.href);
            url.searchParams.delete('cmplz-force-reload');
            return url.pathname + url.search;
        } catch (e) {
            return '';
        }
    };
    const currentPath = () => pageKey(window.location.href);

    const rememberDowngrade = () => {
        try {
            window.sessionStorage.setItem(downgradeKey, JSON.stringify({path: currentPath(), at: Date.now()}));
        } catch (e) {
            // No session storage: the reload is counted as before.
        }
    };

    const isReloadAfterDowngrade = () => {
        let saved = null;
        try {
            saved = JSON.parse(window.sessionStorage.getItem(downgradeKey) || 'null');
            window.sessionStorage.removeItem(downgradeKey);
        } catch (e) {
            return false;
        }

        if (!saved || saved.path !== currentPath() || Date.now() - saved.at > 30000) {
            return false;
        }

        // A reload, or a navigation to the same page (Firefox gets a new URL with a cache-busting parameter).
        const navigation = window.performance && typeof window.performance.getEntriesByType === 'function'
            ? window.performance.getEntriesByType('navigation')[0]
            : null;
        let referrerPath = '';
        try {
            referrerPath = document.referrer ? pageKey(document.referrer) : '';
        } catch (e) {
            referrerPath = '';
        }

        return !navigation || navigation.type === 'reload' || referrerPath === currentPath();
    };

    let decided = false;
    const decide = () => {
        if (decided) {
            return;
        }

        decided = true;
        if (hasStatisticsConsent()) {
            wpStatisticsStartTracking({skipCurrentPage: isReloadAfterDowngrade()});
        }
    };

    if (typeKnown() || hasStoredChoice()) {
        decide();
    } else if (!window.waitfor_consent_hook) {
        // Not waiting forever: without a banner that sets the type, WP Consent API allows everything,
        // so decide once the page has loaded and the banner has had its chance.
        const decideLater = () => setTimeout(decide, 500);
        if (document.readyState === 'complete') {
            decideLater();
        } else {
            window.addEventListener('load', decideLater);
        }
    }

    document.addEventListener('wp_consent_type_defined', decide);

    document.addEventListener("wp_listen_for_consent_change", function (e) {
        const changedConsentCategory = e.detail;
        for (let key in changedConsentCategory) {
            if (changedConsentCategory.hasOwnProperty(key)) {
                if (key === 'statistics' || key === 'statistics-anonymous') {
                    // A consent change means the banner is running, so the decision is made from here on.
                    decided = true;
                    if (hasStatisticsConsent()) {
                        // Full consent withdrawn, anonymous still allowed: this page is already counted.
                        // Only when this page's hit was already sent: a page still waiting for its hit must be counted after the reload.
                        if (key === 'statistics' && changedConsentCategory[key] === 'deny' && WpStatisticsUserTracker.isTrackingEnabled
                            && !(typeof WpStatisticsUserTracker.hasPendingHit === 'function' && WpStatisticsUserTracker.hasPendingHit())) {
                            rememberDowngrade();
                        }
                        wpStatisticsStartTracking();
                    } else {
                        wpStatisticsStopTracking();
                    }
                    break;
                }
            }
        }
    });
}

function handleRealCookieBannerIntegration() {
    const updateTracking = () => {
        const baseConsent = window.consentApi?.consentSync("wp-statistics");
        const dataProcessingConsent = window.consentApi?.consentSync("wp-statistics-with-data-processing");
        // The base service counts as before even when the owner never added it in Real Cookie Banner.
        // The data-processing service must exist, so a declined visitor is not counted when IPs are hashed.
        const baseOptIn = !!(baseConsent && baseConsent.cookieOptIn);
        const dataProcessingOptIn = !!(dataProcessingConsent && dataProcessingConsent.cookie != null && dataProcessingConsent.cookieOptIn);

        if (baseOptIn || dataProcessingOptIn) {
            wpStatisticsStartTracking();
            return true;
        }

        wpStatisticsStopTracking();
        return false;
    };

    (window.consentApi?.consent("wp-statistics") || Promise.resolve())
        .then(updateTracking)
        .catch(() => {
            if (!updateTracking()) {
                console.log("WP Statistics: Real Cookie Banner consent is not given to track visitor information.");
            }
        });

    document.addEventListener('RCB/OptIn', updateTracking);
    document.addEventListener('RCB/OptOut', updateTracking);
}

function handleBorlabsCookieIntegration() {
    const consentStatus = (WP_Statistics_Tracker_Object.option.consentIntegration || {}).status || {};

    // Borlabs normally releases this script only after the WP Statistics service is accepted, but the
    // script can also run without that (blocker not set up, a cached page, an optimisation plugin moving
    // the script). So ask Borlabs for the visitor's answer instead of assuming it.
    const updateTracking = () => {
        // With Anonymous Tracking on, the site owner chose to keep counting visitors without consent.
        if (consentStatus.anonymous_tracking === true) {
            wpStatisticsStartTracking();
            return;
        }

        const consents = window.BorlabsCookie && window.BorlabsCookie.Consents;

        // Borlabs is not ready yet: wait for its init or consent event.
        if (!consents || typeof consents.hasConsent !== 'function') {
            return;
        }

        if (consents.hasConsent('wp-statistics')) {
            wpStatisticsStartTracking();
        } else {
            wpStatisticsStopTracking();
        }
    };

    updateTracking();

    window.addEventListener('borlabs-cookie-after-init', updateTracking);
    window.addEventListener('borlabs-cookie-consent-saved', updateTracking);
}

function handleCookieYesIntegration() {
    const hasAnalyticsConsent = () => {
        if (typeof window.getCkyConsent === 'function') {
            const consent = window.getCkyConsent();
            return !!(consent && consent.categories && consent.categories.analytics === true);
        }

        // Fallback when the CookieYes script is not loaded yet: read its consent cookie
        const match = document.cookie.match(/(?:^|;\s*)cookieyes-consent=([^;]*)/);
        if (match) {
            return decodeURIComponent(match[1]).split(',').indexOf('analytics:yes') !== -1;
        }

        return hasLegacyAnalyticsConsent();
    };

    // CookieYes in its legacy mode (the older "GDPR Cookie Consent" banner) stores one cookie per
    // category, e.g. cookielawinfo-checkbox-analytics=yes, plus viewed_cookie_policy for the
    // visitor's answer to the banner. It has no getCkyConsent() or consent event. The category
    // cookie is written on the first page load from the category's default, before any answer,
    // so it only counts once the visitor accepted the banner, as CookieYes itself checks (#1175).
    // Only read in legacy mode, so old legacy cookies cannot count on the newer banner.
    const consentStatus = (WP_Statistics_Tracker_Object.option.consentIntegration || {}).status || {};
    const isLegacy = consentStatus.legacy === true || typeof window.Cli_Data !== 'undefined';

    const readCookie = (name) => {
        const match = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    };

    const hasLegacyAnalyticsConsent = () => {
        if (!isLegacy || readCookie('viewed_cookie_policy') !== 'yes') {
            return false;
        }

        // A banner without an Analytics category sets no analytics cookie: accepting it is enough.
        const analytics = readCookie('cookielawinfo-checkbox-analytics');
        return analytics === null || analytics === 'yes';
    };

    // With "Reload after Accept" on, the banner reloads the page and the new page sends the hit.
    const legacyReloadsOnAccept = () => !!(window.CLI && window.CLI.settings && Boolean(window.CLI.settings.accept_close_reload) === true);

    if (hasAnalyticsConsent()) {
        wpStatisticsStartTracking();
    }

    document.addEventListener('cookieyes_consent_update', function (e) {
        const accepted = (e.detail && e.detail.accepted) || [];

        const rejected = (e.detail && e.detail.rejected) || [];

        if (rejected.indexOf('analytics') !== -1) {
            wpStatisticsStopTracking();
        } else if (accepted.indexOf('analytics') !== -1 || hasAnalyticsConsent()) {
            wpStatisticsStartTracking();
        } else {
            wpStatisticsStopTracking();
        }
    });

    // The legacy banner sends no event, so check again once the visitor clicks in it.
    document.addEventListener('click', function (e) {
        if (!isLegacy || !e.target || typeof e.target.closest !== 'function') return;

        if (e.target.closest('#cookie-law-info-bar, #cliSettingsPopup, .cli-modal, .cli-bar-container')) {
            setTimeout(function () {
                if (!hasAnalyticsConsent()) {
                    wpStatisticsStopTracking();
                } else if (!legacyReloadsOnAccept()) {
                    wpStatisticsStartTracking();
                }
            }, 300);
        }
    });
}
