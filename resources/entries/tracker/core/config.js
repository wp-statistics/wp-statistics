/**
 * WP Statistics Config Module
 *
 * Single reader of WP_Statistics_Tracker_Object.
 * All other modules import config values from here.
 */

let cachedConfig = null;

function read() {
    if (typeof window.WP_Statistics_Tracker_Object === 'undefined') {
        return null;
    }
    return window.WP_Statistics_Tracker_Object;
}

export function getConfig() {
    if (!cachedConfig) {
        cachedConfig = read();
    }
    return cachedConfig;
}

export function refresh() {
    cachedConfig = read();
    return cachedConfig;
}

function getOption(key, fallback) {
    const cfg = getConfig();
    if (!cfg || !cfg.option) return fallback;
    return cfg.option[key] !== undefined ? cfg.option[key] : fallback;
}

export function getSignature() {
    const cfg = getConfig();
    return (cfg && cfg.signature) ? cfg.signature : '';
}

/**
 * Get the current resource info.
 *
 * @param {string} [key] Optional key to return a specific field (e.g. 'resourceId', 'resourceType', 'resourceUriId').
 * @returns {object|*} The full resource object, or the value of the requested key.
 */
export function getResource(key) {
    const cfg = getConfig();
    var resource = (cfg && cfg.resource) ? cfg.resource : {};

    if (key) {
        return resource[key] !== undefined ? resource[key] : '';
    }

    return resource;
}

export function getUserId() {
    const cfg = getConfig();
    return (cfg && cfg.userId) ? cfg.userId : 0;
}

export function isPreview() {
    return !!getOption('isPreview', false);
}

export function getConsentConfig() {
    return getOption('consent', {});
}

export function getTrackingLevels() {
    var levels = getOption('trackingLevel', null);
    if (!levels) {
        return { full: 'full', anonymous: 'anonymous', none: 'none' };
    }
    return levels;
}

export function isAnonymousTracking() {
    return !!getOption('anonymousTracking', false);
}

