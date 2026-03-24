/**
 * URL resolution for the active tracking method.
 *
 * Concatenates baseUrl + endpoint path.
 * No full URLs are exposed in page source — JS assembles them.
 */

import { getConfig } from '../core/config.js';

export function getHitEndpoint() {
    var cfg = getConfig();
    if (!cfg) return '';
    return (cfg.baseUrl || '') + (cfg.hitEndpoint || '');
}

export function getBatchEndpoint() {
    var cfg = getConfig();
    if (!cfg) return '';
    var ep = cfg.batchEndpoint || '';
    if (ep.indexOf('http://') === 0 || ep.indexOf('https://') === 0 || ep.indexOf('//') === 0) {
        return ep;
    }
    return (cfg.baseUrl || '') + ep;
}
