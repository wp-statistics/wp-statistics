document.addEventListener('DOMContentLoaded', function () {
    if (WP_Statistics_Tracker_Object.option.consentLevel == 'disabled' || WP_Statistics_Tracker_Object.option.trackAnonymously || !WP_Statistics_Tracker_Object.option.isWpConsentApiActive || wp_has_consent(WP_Statistics_Tracker_Object.option.consentLevel)) {
        WpStatisticsUserTracker.init();
        WpStatisticsEventTracker.init();
    }

    document.addEventListener("wp_listen_for_consent_change", function (e) {
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
    });
});