/**
 * WP Statistics Consent Module
 *
 * Filter-based consent using the hook system.
 * Each consent adapter registers a 'trackingLevel' filter that returns
 * one of the tracking level values from the server-side config.
 *
 * Adapters also listen for consent changes and fire doAction('consentChanged').
 */

import { addFilter, doAction } from './hooks.js';
import { getConsentConfig, getTrackingLevels, isAnonymousTracking } from './config.js';

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
            registerBorlabsCookie();
            break;
        case 'none':
        default:
            break;
    }
}

function registerWpConsentApi() {
    var levels = getTrackingLevels();

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

function registerRealCookieBanner() {
    var levels = getTrackingLevels();
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

function registerBorlabsCookie() {
    var levels = getTrackingLevels();

    addFilter('trackingLevel', function () {
        return isAnonymousTracking() ? levels.anonymous : levels.full;
    });
}
