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