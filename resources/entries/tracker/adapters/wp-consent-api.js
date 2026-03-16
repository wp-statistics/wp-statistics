/**
 * WP Consent API Adapter
 *
 * Integrates with the WP Consent API (wp_has_consent / wp_listen_for_consent_change).
 */

export default {
    init: function (params) {
        var levels = params.levels;
        var addFilter = params.addFilter;
        var doAction = params.doAction;

        if (typeof window.wp_consent_type === 'undefined' && typeof window.wp_fallback_consent_type === 'undefined') {
            window.wp_fallback_consent_type = 'optin';
        }

        addFilter('trackingLevel', function () {
            if (typeof window.wp_has_consent !== 'function') {
                console.warn('WP Statistics: wp_has_consent() is not available. Blocking tracking until consent change.');
                return levels.none;
            }

            if (window.wp_has_consent('statistics')) {
                return levels.full;
            }
            if (window.wp_has_consent('statistics-anonymous')) {
                return levels.anonymous;
            }

            return levels.none;
        });

        document.addEventListener('wp_listen_for_consent_change', function (e) {
            var changed = e.detail;
            if (changed && (changed['statistics'] === 'allow' || changed['statistics-anonymous'] === 'allow')) {
                doAction('consentChanged');
            }
        });
    }
};
