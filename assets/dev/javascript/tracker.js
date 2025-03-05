// Initialize tracking on page load
document.addEventListener('DOMContentLoaded', wpStatisticsInitialize);

// Listen for popstate event (browser back/forward navigation)
window.addEventListener("popstate", wpStatisticsTrackUrlChange);

// Listen for pushState/replaceState (URL changes in SPA)
history.pushState = function (state, title, url) {
    WpStatisticsUserTracker.originalPushState.apply(history, arguments);
    wpStatisticsTrackUrlChange();
};

history.replaceState = function (state, title, url) {
    WpStatisticsUserTracker.originalReplaceState.apply(history, arguments);
    wpStatisticsTrackUrlChange();
};

// Detect URL changes caused by History API (pushState, replaceState) or browser navigation
function wpStatisticsTrackUrlChange() {
    if (typeof WP_Statistics_Tracker_Object == "undefined") {
        console.error('WP Statistics: Variable WP_Statistics_Tracker_Object not found. Ensure /wp-content/plugins/wp-statistics/assets/js/tracker.js is either excluded from cache settings or not dequeued by any plugin. Clear your cache if necessary.');
    }

    if (WP_Statistics_Tracker_Object.option.isPreview) {
        return;
    }

    if (window.location.href !== WpStatisticsUserTracker.lastUrl) {
        // Update the WP_Statistics_Tracker_Object
        wpStatisticsUpdateTrackerObject();

        // Update the last visited URL
        WpStatisticsUserTracker.lastUrl = window.location.href;

        // Execute the sendHitRequest() on URL change
        WpStatisticsUserTracker.checkHitRequestConditions();

        // Initialize tracking on page load
        wpStatisticsInitialize();

        // Listen for consent changes
        document.addEventListener("wp_listen_for_consent_change", wpStatisticsHandleConsentChange);
    }
}

// Function to initialize WP Statistics tracking
function wpStatisticsInitialize() {
    if (WP_Statistics_Tracker_Object.option.consentLevel == 'disabled' || WP_Statistics_Tracker_Object.option.trackAnonymously || !WP_Statistics_Tracker_Object.option.isWpConsentApiActive || wp_has_consent(WP_Statistics_Tracker_Object.option.consentLevel)) {
        WpStatisticsUserTracker.init();
        WpStatisticsEventTracker.init();
    }
}

// Function to handle consent changes
function wpStatisticsHandleConsentChange(e) {
    const changedConsentCategory = e.detail;

    for (let key in changedConsentCategory) {
        if (changedConsentCategory.hasOwnProperty(key)) {
            if (key === WP_Statistics_Tracker_Object.option.consentLevel && changedConsentCategory[key] === 'allow') {
                WpStatisticsUserTracker.init();
                WpStatisticsEventTracker.init();

                // When trackAnonymously is enabled, the init() call above will get ignored (since it's already initialized before)
                // So, in this specific case, we can call checkHitRequestConditions() manually
                // This will insert a new record for the user (who just gave consent to us) and prevent other scripts (e.g. event.js) from malfunctioning
                if (WP_Statistics_Tracker_Object.option.trackAnonymously) {
                    WpStatisticsUserTracker.checkHitRequestConditions();
                }
            }
        }
    }
}

// Function to update the WP_Statistics_Tracker_Object when URL changes
function wpStatisticsUpdateTrackerObject() {
    const scriptTag = document.getElementById("wp-statistics-tracker-js-extra");

    if (scriptTag) {
        try {
            // Extract the new JSON object from the script tag
            const newTrackerObject = JSON.parse(scriptTag.innerHTML.replace('var WP_Statistics_Tracker_Object = ', '').replace(';', ''));

            // Update the global WP_Statistics_Tracker_Object with the new data
            WP_Statistics_Tracker_Object = newTrackerObject;
        } catch (error) {
        }
    }
}