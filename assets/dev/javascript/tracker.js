// Initialize tracking on page load
document.addEventListener('DOMContentLoaded', function (e) {
    WpStatisticsUserTracker.wpStatisticsInitialize(e);
});

// Listen for consent changes
document.addEventListener("wp_listen_for_consent_change", function (e) {
    WpStatisticsUserTracker.wpStatisticsHandleConsentChange(e);
});

// Listen for popstate event (browser back/forward navigation)
window.addEventListener("popstate", WpStatisticsUserTracker.wpStatisticsHandleConsentChange);

// Listen for pushState/replaceState (URL changes in SPA)
history.pushState = function (state, title, url) {
    WpStatisticsUserTracker.originalPushState.apply(history, arguments);
    WpStatisticsUserTracker.wpStatisticsTrackUrlChange();
};

history.replaceState = function (state, title, url) {
    WpStatisticsUserTracker.originalReplaceState.apply(history, arguments);
    WpStatisticsUserTracker.wpStatisticsTrackUrlChange();
};