/**
 * URL resolution per delivery mode.
 *
 * Resolves the correct endpoint URL based on tracking configuration:
 * - mu-plugin proxy → muPluginUrl (SHORTINIT direct endpoint)
 * - bypass mode     → ajaxUrl (admin-ajax.php)
 * - default         → requestUrl + '/hit' or '/batch' (REST API)
 */

import { getRequestUrl, getAjaxUrl, getMuPluginUrl, getBatchUrl, isBypassAdBlockers, getHitParams } from '../core/config.js';

export function getHitEndpoint() {
    const muPluginUrl = getMuPluginUrl();
    if (muPluginUrl) {
        return muPluginUrl;
    }

    if (isBypassAdBlockers()) {
        return getAjaxUrl();
    }

    const hitParams = getHitParams();
    return getRequestUrl() + '/' + (hitParams.endpoint || 'hit');
}

export function getBatchEndpoint() {
    const batchUrl = getBatchUrl();
    if (batchUrl) {
        return batchUrl;
    }

    // Batch always uses AJAX when in bypass mode
    if (isBypassAdBlockers()) {
        return getAjaxUrl();
    }

    return getRequestUrl() + '/batch';
}
