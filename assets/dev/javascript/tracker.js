document.addEventListener('DOMContentLoaded', function handler() {
    // Defer until a prerendered document is activated by user navigation.
    // The server drops prerender hits via Sec-Purpose, and activation does
    // not re-run scripts — without this gate the real pageview is lost.
    if (document.prerendering) {
        document.addEventListener('prerenderingchange', handler, { once: true });
        return;
    }

    const consentIntegration = WP_Statistics_Tracker_Object.option.consentIntegration.name;

    // If there's no consent integration, or borlabs cookie integration is enabled
    if (!consentIntegration || consentIntegration === 'borlabs_cookie') {
        WpStatisticsUserTracker.init();
        WpStatisticsEventTracker.init();
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
});


function handleWpConsentApiIntegration() {
    if (wp_has_consent('statistics') || wp_has_consent('statistics-anonymous')) {
        WpStatisticsUserTracker.init();
        WpStatisticsEventTracker.init();
    }

    document.addEventListener("wp_listen_for_consent_change", function (e) {
        const changedConsentCategory = e.detail;
        for (let key in changedConsentCategory) {
            if (changedConsentCategory.hasOwnProperty(key)) {
                if ((key === 'statistics' || key === 'statistics-anonymous') && changedConsentCategory[key] === 'allow') {
                    WpStatisticsUserTracker.init();
                    WpStatisticsEventTracker.init();
                }
            }
        }
    });
}

function handleRealCookieBannerIntegration() {
    (window.consentApi?.consent("wp-statistics") || Promise.resolve())
        .then(() => {
            // In case the user has given consent
            WpStatisticsUserTracker.init();
            WpStatisticsEventTracker.init();
        })
        .catch(() => {
            // In case the user has not given base consent,
            // check if they have given consent for data processing service
            const dataProcessing = window.consentApi?.consentSync("wp-statistics-with-data-processing");

            if (dataProcessing.cookie != null && dataProcessing.cookieOptIn) {
                WpStatisticsUserTracker.init();
                WpStatisticsEventTracker.init();
            } else {
                console.log("WP Statistics: Real Cookie Banner consent is not given to track visitor information.");
            }
        });
}

function handleCookieYesIntegration() {
    let started = false;

    const startTracking = () => {
        if (started) return;
        started = true;
        WpStatisticsUserTracker.init();
        WpStatisticsEventTracker.init();
    };

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
    // category, e.g. cookielawinfo-checkbox-analytics=yes, and has no getCkyConsent() or consent
    // event. A banner without an Analytics category has no such cookie, so the visitor's answer to
    // the banner itself (viewed_cookie_policy) decides.
    const readCookie = (name) => {
        const match = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    };

    const hasLegacyAnalyticsConsent = () => {
        const analytics = readCookie('cookielawinfo-checkbox-analytics');
        if (analytics !== null) {
            return analytics === 'yes';
        }

        return readCookie('viewed_cookie_policy') === 'yes';
    };

    if (hasAnalyticsConsent()) {
        startTracking();
    }

    document.addEventListener('cookieyes_consent_update', function (e) {
        const accepted = (e.detail && e.detail.accepted) || [];

        if (accepted.indexOf('analytics') !== -1 || hasAnalyticsConsent()) {
            startTracking();
        }
    });

    // The legacy banner sends no event, so check again once the visitor clicks in it.
    document.addEventListener('click', function (e) {
        if (started || !e.target || typeof e.target.closest !== 'function') return;

        if (e.target.closest('#cookie-law-info-bar, #cliSettingsPopup, .cli-modal, .cli-bar-container')) {
            setTimeout(function () {
                if (hasAnalyticsConsent()) {
                    startTracking();
                }
            }, 300);
        }
    });
}
