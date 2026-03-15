/**
 * WP Statistics Consent Module
 *
 * Filter-based consent using the hook system.
 * Each consent adapter registers a 'trackingLevel' filter that returns
 * 'full', 'anonymous', or 'none'.
 *
 * Adapters also listen for consent changes and fire doAction('consentChanged').
 */

import { addFilter, doAction } from './hooks.js';
import { getConsentConfig } from './config.js';

export function registerConsentAdapter() {
    var config = getConsentConfig();
    var mode = (config && config.mode) ? config.mode : 'none';

    switch (mode) {
        case 'wp_consent_api':
            registerWpConsentApi();
            break;
        case 'real_cookie_banner':
            registerRealCookieBanner();
            break;
        case 'borlabs_cookie':
            // Borlabs blocks the script itself — if running, consent is given
            // No filter needed, default 'full' passes through
            break;
        case 'none':
        default:
            // No consent management — default 'full' passes through
            break;
    }
}

function registerWpConsentApi() {
    // Set fallback consent type to 'optin' when consent_type is not configured
    // (e.g., CookieYes before banner interaction)
    if (typeof window.wp_consent_type === 'undefined' && typeof window.wp_fallback_consent_type === 'undefined') {
        window.wp_fallback_consent_type = 'optin';
    }

    addFilter('trackingLevel', function (level) {
        if (typeof window.wp_has_consent !== 'function') {
            console.warn('WP Statistics: wp_has_consent() is not available. Blocking tracking until consent change.');
            return 'none';
        }

        if (window.wp_has_consent('statistics')) {
            return 'full';
        }
        if (window.wp_has_consent('statistics-anonymous')) {
            return 'anonymous';
        }

        return 'none';
    });

    // Listen for consent changes
    document.addEventListener('wp_listen_for_consent_change', function (e) {
        var changed = e.detail;
        if (changed && (changed['statistics'] === 'allow' || changed['statistics-anonymous'] === 'allow')) {
            doAction('consentChanged');
        }
    });
}

function registerRealCookieBanner() {
    var consentGranted = false;

    addFilter('trackingLevel', function (level) {
        return consentGranted ? 'full' : 'none';
    });

    if (!window.consentApi || typeof window.consentApi.consent !== 'function') {
        console.warn('WP Statistics: Real Cookie Banner consentApi not found. Tracking disabled until consent API loads.');
        return;
    }

    window.consentApi.consent('wp-statistics')
        .then(function () {
            consentGranted = true;
            doAction('consentChanged');
        })
        .catch(function () {
            // Try data processing consent as fallback
            try {
                var dataProcessing = window.consentApi.consentSync('wp-statistics-with-data-processing');
                if (dataProcessing && dataProcessing.cookie != null && dataProcessing.cookieOptIn) {
                    consentGranted = true;
                    doAction('consentChanged');
                }
            } catch (err) {
                console.warn('WP Statistics: Error checking RCB data processing consent.', err);
            }
        });
}
