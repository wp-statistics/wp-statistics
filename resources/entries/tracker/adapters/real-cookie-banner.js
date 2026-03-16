/**
 * Real Cookie Banner Adapter
 *
 * Integrates with Real Cookie Banner's consentApi.
 */

export default {
    init: function (params) {
        var levels = params.levels;
        var addFilter = params.addFilter;
        var doAction = params.doAction;

        var resolvedLevel = levels.none;

        addFilter('trackingLevel', function () {
            return resolvedLevel;
        });

        if (!window.consentApi || typeof window.consentApi.consent !== 'function') {
            console.warn('WP Statistics: Real Cookie Banner consentApi not found. Tracking disabled until consent API loads.');
            return;
        }

        // Check data processing consent synchronously (grants full tracking)
        var dpConsent = null;
        try {
            dpConsent = window.consentApi.consentSync('wp-statistics-with-data-processing');
        } catch (e) {
            console.warn('WP Statistics: Error checking RCB data processing consent.', e);
        }

        if (dpConsent && dpConsent.cookie != null && dpConsent.cookieOptIn) {
            resolvedLevel = levels.full;
            return;
        }

        // Check base consent synchronously (grants anonymous tracking)
        var baseConsent = null;
        try {
            baseConsent = window.consentApi.consentSync('wp-statistics');
        } catch (e) {
            console.warn('WP Statistics: Error checking RCB base consent.', e);
        }

        if (baseConsent && baseConsent.cookie != null && baseConsent.cookieOptIn) {
            resolvedLevel = levels.anonymous;
            return;
        }

        // Neither resolved synchronously — listen for async consent
        window.consentApi.consent('wp-statistics')
            .then(function () {
                resolvedLevel = levels.anonymous;
                doAction('consentChanged');
            })
            .catch(function (err) {
                if (err) {
                    console.debug('WP Statistics: RCB consent not given or error:', err);
                }
            });
    }
};
