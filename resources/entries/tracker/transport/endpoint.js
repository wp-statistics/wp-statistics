/**
 * URL resolution for the active tracking method.
 *
 * Base URLs (ajax, rest, content) are provided globally by FrontendHandler.
 * The active method type determines which base URL to use for each endpoint.
 */

import { getConfig } from '../core/config.js';

/**
 * Base URL mapping per method type.
 *
 * Hit endpoint uses the transport-specific base URL.
 * Batch endpoint uses the same base, except hybrid mode
 * where batch goes through REST (requires full WP bootstrap).
 */
var hitBaseMap   = { ajax: 'ajax', rest: 'rest', hybrid: 'content' };
var batchBaseMap = { ajax: 'ajax', rest: 'rest', hybrid: 'rest' };

function getBaseUrl(type) {
    var cfg = getConfig();
    if (!cfg || !cfg.baseUrls) return '';
    return cfg.baseUrls[type] || '';
}

export function getHitEndpoint() {
    var cfg = getConfig();
    if (!cfg) return '';
    return getBaseUrl(hitBaseMap[cfg.trackingMethod]) + (cfg.hitEndpoint || '');
}

export function getBatchEndpoint() {
    var cfg = getConfig();
    if (!cfg) return '';
    return getBaseUrl(batchBaseMap[cfg.trackingMethod]) + (cfg.batchEndpoint || '');
}
