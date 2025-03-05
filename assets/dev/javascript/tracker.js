document.addEventListener('DOMContentLoaded', function () {
    const consentIntegration = WP_Statistics_Tracker_Object.option.consentIntegration.name;

    // If there's no consent integration
    if (!consentIntegration) {
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

// Listen for popstate event (browser back/forward navigation)
window.addEventListener("popstate", wpStatisticsTrackUrlChange);

// Listen for pushState/replaceState (URL changes in SPA)
history.pushState = function(state, title, url) {
    WpStatisticsUserTracker.originalPushState.apply(history, arguments);
    wpStatisticsTrackUrlChange();
};

history.replaceState = function (state, title, url) {
    WpStatisticsUserTracker.originalReplaceState.apply(history, arguments);
    wpStatisticsTrackUrlChange();
};

function handleWpConsentApiIntegration() {
    const consentLevel      = WP_Statistics_Tracker_Object.option.consentIntegration.status['consent_level'];
    const trackAnonymously  = WP_Statistics_Tracker_Object.option.consentIntegration.status['track_anonymously'];

    if (trackAnonymously || consentLevel == 'disabled' || wp_has_consent(consentLevel)) {
        WpStatisticsUserTracker.init();
        WpStatisticsEventTracker.init();
    }

    document.addEventListener("wp_listen_for_consent_change", function (e) {
        const changedConsentCategory = e.detail;
        for (let key in changedConsentCategory) {
            if (changedConsentCategory.hasOwnProperty(key)) {
                if (key === consentLevel && changedConsentCategory[key] === 'allow') {
                    WpStatisticsUserTracker.init();
                    WpStatisticsEventTracker.init();

                    // When trackAnonymously is enabled, the init() call above will get ignored (since it's already initialized before)
                    // So, in this specific case, we can call checkHitRequestConditions() manually
                    // This will insert a new record for the user (who just gave consent to us) and prevent other scripts (e.g. event.js) from malfunctioning
                    if (trackAnonymously) {
                        WpStatisticsUserTracker.checkHitRequestConditions();
                    }
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

// Detect URL changes caused by History API (pushState, replaceState) or browser navigation
function wpStatisticsTrackUrlChange() {
    if (typeof WP_Statistics_Tracker_Object == "undefined") {
        console.error('WP Statistics: Variable WP_Statistics_Tracker_Object not found. Ensure /wp-content/plugins/wp-statistics/assets/js/tracker.js is either excluded from cache settings or not dequeued by any plugin. Clear your cache if necessary.');
    }

    if (WP_Statistics_Tracker_Object.option.isPreview) {
        return;
    }

    if(window.location.href !== WpStatisticsUserTracker.lastUrl ) {
        // Update the last visited URL
        WpStatisticsUserTracker.lastUrl = window.location.href;

        // Execute the sendHitRequest() on URL change
        WpStatisticsUserTracker.checkHitRequestConditions();
    }
}