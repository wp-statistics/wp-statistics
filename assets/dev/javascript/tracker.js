document.addEventListener('DOMContentLoaded', function handler() {
    // Defer until a prerendered document is activated by user navigation.
    // The server drops prerender hits via Sec-Purpose, and activation does
    // not re-run scripts — without this gate the real pageview is lost.
    if (document.prerendering) {
        document.addEventListener('prerenderingchange', handler, { once: true });
        return;
    }

    const consentIntegration = WP_Statistics_Tracker_Object.option.consentIntegration.name;

    // If there's no consent integration
    if (!consentIntegration) {
        startTracking();
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
});

function startTracking() {
    WpStatisticsUserTracker.init();
    WpStatisticsEventTracker.init();
}

function stopTracking() {
    WpStatisticsUserTracker.stop();
    WpStatisticsEventTracker.stop();
}

function handleWpConsentApiIntegration() {
    if (wp_has_consent('statistics') || wp_has_consent('statistics-anonymous')) {
        startTracking();
    }

    document.addEventListener("wp_listen_for_consent_change", function (e) {
        const changedConsentCategory = e.detail;
        for (let key in changedConsentCategory) {
            if (changedConsentCategory.hasOwnProperty(key)) {
                if (key === 'statistics' || key === 'statistics-anonymous') {
                    if (wp_has_consent('statistics') || wp_has_consent('statistics-anonymous')) {
                        startTracking();
                    } else {
                        stopTracking();
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
        const hasConsent = consent => consent && consent.cookie != null && consent.cookieOptIn;

        if (hasConsent(baseConsent) || hasConsent(dataProcessingConsent)) {
            startTracking();
            return true;
        }

        stopTracking();
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
    // Borlabs only releases this script after the WP Statistics service is accepted.
    startTracking();

    document.addEventListener('borlabs-cookie-consent-saved', function () {
        const consents = window.BorlabsCookie && window.BorlabsCookie.Consents;

        if (!consents || typeof consents.hasConsent !== 'function') {
            return;
        }

        if (consents.hasConsent('wp-statistics')) {
            startTracking();
        } else {
            stopTracking();
        }
    });
}

function handleCookieYesIntegration() {
    const consentStatus = (WP_Statistics_Tracker_Object.option.consentIntegration || {}).status || {};
    const isLegacy = consentStatus.legacy === true || typeof window.Cli_Data !== 'undefined';
    const anonymousTrackingEnabled = consentStatus.anonymous_tracking === true;

    const readCookie = (name) => {
        const match = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    };
    const hasAnalyticsConsent = () => {
        if (typeof window.getCkyConsent === 'function') {
            const consent = window.getCkyConsent();
            return !!(consent && consent.categories && consent.categories.analytics === true);
        }

        // Fallback when the CookieYes script is not loaded yet: read its consent cookie
        const consent = readCookie('cookieyes-consent');
        if (consent !== null) {
            return consent.split(',').indexOf('analytics:yes') !== -1;
        }

        return hasLegacyAnalyticsConsent();
    };

    // CookieYes in its legacy mode (the older "GDPR Cookie Consent" banner) stores one cookie per
    // category, e.g. cookielawinfo-checkbox-analytics=yes, plus viewed_cookie_policy for the
    // visitor's answer to the banner. It has no getCkyConsent() or consent event. The category
    // cookie is written on the first page load from the category's default, before any answer,
    // so it only counts once the visitor accepted the banner, as CookieYes itself checks (#1175).
    // Only read in legacy mode, so old legacy cookies cannot count on the newer banner.
    const hasLegacyAnalyticsConsent = () => {
        if (!isLegacy || readCookie('viewed_cookie_policy') !== 'yes') {
            return false;
        }

        // A banner without an Analytics category sets no analytics cookie: accepting it is enough.
        const analytics = readCookie('cookielawinfo-checkbox-analytics');
        return analytics === null || analytics === 'yes';
    };

    const hasAnalyticsDecline = () => {
        if (typeof window.getCkyConsent === 'function') {
            const consent = window.getCkyConsent();
            return !!(consent
                && consent.isUserActionCompleted === true
                && consent.categories
                && consent.categories.analytics === false);
        }

        const consent = readCookie('cookieyes-consent');
        if (consent !== null) {
            const values = consent.split(',');
            return values.indexOf('action:yes') !== -1
                && values.indexOf('analytics:no') !== -1;
        }

        const legacyAnswer = readCookie('viewed_cookie_policy');
        return isLegacy
            && (legacyAnswer === 'yes' || legacyAnswer === 'no')
            && !hasLegacyAnalyticsConsent();
    };

    const shouldStartTracking = () => hasAnalyticsConsent()
        || (anonymousTrackingEnabled && hasAnalyticsDecline());

    // With "Reload after Accept" on, the banner reloads the page and the new page sends the hit.
    const legacyReloadsOnAccept = () => !!(window.CLI && window.CLI.settings && Boolean(window.CLI.settings.accept_close_reload) === true);

    if (shouldStartTracking()) {
        startTracking();
    }

    document.addEventListener('cookieyes_consent_update', function (e) {
        const accepted = (e.detail && e.detail.accepted) || [];
        const rejected = (e.detail && e.detail.rejected) || [];

        const analyticsRejected = rejected.indexOf('analytics') !== -1;

        if (accepted.indexOf('analytics') !== -1
            || (anonymousTrackingEnabled && analyticsRejected)
            || (!analyticsRejected && shouldStartTracking())) {
            startTracking();
        } else {
            stopTracking();
        }
    });

    // The legacy banner sends no event, so check again once the visitor clicks in it.
    document.addEventListener('click', function (e) {
        if (!isLegacy || legacyReloadsOnAccept() || !e.target || typeof e.target.closest !== 'function') return;

        if (e.target.closest('#cookie-law-info-bar, #cliSettingsPopup, .cli-modal, .cli-bar-container')) {
            setTimeout(function () {
                if (shouldStartTracking()) {
                    startTracking();
                } else {
                    stopTracking();
                }
            }, 300);
        }
    });
}
