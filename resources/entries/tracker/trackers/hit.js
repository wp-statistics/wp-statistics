/**
 * WP Statistics Hit Tracker
 *
 * Sends the initial page hit request via XHR.
 * Needs the response to gate engagement initialization.
 */

import { sendXhr } from '../transport/send.js';
import { getHitEndpoint } from '../transport/endpoint.js';
import { getSignature, getResource, getUserId, getTrackingLevels } from '../core/config.js';
import { applyFilters, doAction } from '../core/hooks.js';
import { base64Encode } from '../utils/base64.js';
import { collectLocaleInfo } from '../utils/locale.js';

var hitRequestSuccessful = false;
var isSpaNavigation = false;

export function wasSuccessful() {
    return hitRequestSuccessful;
}

function getPathAndQueryString() {
    return base64Encode(window.location.pathname + window.location.search);
}

function getReferred() {
    return base64Encode(document.referrer);
}

export function send(trackingLevel) {
    return new Promise(function (resolve) {
        try {
            var url = getHitEndpoint();
            var localeInfo = collectLocaleInfo();

            var data = {
                // Resource (camelCase config -> snake_case HTTP)
                // Force server-side resolution on SPA navigations (stale config)
                resource_uri_id: isSpaNavigation ? 0 : (getResource('resourceUriId') || 0),
                resource_type: isSpaNavigation ? '' : (getResource('resourceType') || ''),
                resource_id: isSpaNavigation ? 0 : (getResource('resourceId') || 0),

                // Auth
                user_id: getUserId(),
                signature: getSignature(),

                // Client-collected
                referrer: getReferred(),
                resource_uri: getPathAndQueryString(),
                tracking_level: trackingLevel || getTrackingLevels().none,
                timezone: localeInfo.timezone,
                language_code: localeInfo.languageCode,
                language_name: localeInfo.languageName,
                screen_width: localeInfo.screenWidth,
                screen_height: localeInfo.screenHeight,
            };

            // Allow plugins to modify hit data before send
            data = applyFilters('hitData', data);

            doAction('beforeHit', data);

            var params = new URLSearchParams(data).toString();

            sendXhr(url, params).then(function (response) {
                hitRequestSuccessful = response.status !== false;
                doAction('afterHit', response, hitRequestSuccessful);
                resolve(hitRequestSuccessful);
            }).catch(function (error) {
                hitRequestSuccessful = false;
                console.warn('WP Statistics: Hit request failed:', error.message);
                doAction('afterHit', null, false);
                resolve(false);
            });
        } catch (error) {
            hitRequestSuccessful = false;
            console.error('WP Statistics: Error sending hit request:', error);
            resolve(false);
        }
    });
}

export function resetState() {
    isSpaNavigation = hitRequestSuccessful;
    hitRequestSuccessful = false;
}
