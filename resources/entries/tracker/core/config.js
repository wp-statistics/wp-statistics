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

export function getHitParams() {
    const cfg = getConfig();
    return (cfg && cfg.hitParams) ? cfg.hitParams : {};
}

export function getRequestUrl() {
    const cfg = getConfig();
    return (cfg && cfg.requestUrl) ? cfg.requestUrl : '';
}

export function getAjaxUrl() {
    const cfg = getConfig();
    return (cfg && cfg.ajaxUrl) ? cfg.ajaxUrl : '';
}

export function getResourceUriId() {
    const cfg = getConfig();
    return (cfg && cfg.resourceUriId) ? cfg.resourceUriId : '';
}

export function isBypassAdBlockers() {
    return !!getOption('bypassAdBlockers', false);
}

export function isPreview() {
    return !!getOption('isPreview', false);
}

export function getConsentConfig() {
    return getOption('consent', {});
}

export function isAnonymousTracking() {
    return !!getOption('anonymousTracking', false);
}

export function getMuPluginUrl() {
    const cfg = getConfig();
    return (cfg && cfg.muPluginUrl) ? cfg.muPluginUrl : '';
}

export function getBatchUrl() {
    const cfg = getConfig();
    return (cfg && cfg.batchUrl) ? cfg.batchUrl : '';
}
