/**
 * WP Statistics Tracker — Entry Point
 *
 * Bootstrap + public API surface.
 * Compiled to a single IIFE by Vite/Rollup.
 */

import { addFilter, applyFilters, addAction, doAction, removeFilter, removeAction } from './core/hooks.js';
import { init } from './core/tracker.js';
import { getHitParams, isEventTrackingEnabled } from './core/config.js';
import * as queue from './transport/queue.js';
import wpConsentApiAdapter from './adapters/wp-consent-api.js';
import realCookieBannerAdapter from './adapters/real-cookie-banner.js';
import borlabsCookieAdapter from './adapters/borlabs-cookie.js';

// Initialize consent adapter registry (preserve third-party registrations that loaded before us)
var registry = window.WpStatisticsConsentAdapters = window.WpStatisticsConsentAdapters || {};
if (!registry.wp_consent_api) registry.wp_consent_api = wpConsentApiAdapter;
if (!registry.real_cookie_banner) registry.real_cookie_banner = realCookieBannerAdapter;
if (!registry.borlabs_cookie) registry.borlabs_cookie = borlabsCookieAdapter;

// Public API
window.wp_statistics = {
    // Hook system
    addFilter: addFilter,
    removeFilter: removeFilter,
    applyFilters: applyFilters,
    addAction: addAction,
    removeAction: removeAction,
    doAction: doAction,

    // Event API (used by premium + 3rd parties)
    addEvent: function (type, data) {
        if (!isEventTrackingEnabled()) return;
        queue.add(type, data);
    },

    // Backward compat alias
    event: function (name, data) {
        if (!isEventTrackingEnabled()) return;
        if (!data) data = {};
        data.timestamp = Date.now();

        var hitParams = getHitParams();
        if (!data.resource_id && hitParams.source_id) {
            data.resource_id = hitParams.source_id;
        }

        queue.add('custom_event', {
            event_name: name,
            event_data: JSON.stringify(data),
        });
    },
};

// Legacy global alias
window.wp_statistics_event = window.wp_statistics.event;

// Boot on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {
    init();
});
