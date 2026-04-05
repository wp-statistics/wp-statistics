/**
 * WP Statistics Tracker — Entry Point
 *
 * Bootstrap + public API surface.
 * Compiled to a single IIFE by Vite/Rollup.
 */

import { addFilter, applyFilters, addAction, doAction, removeFilter, removeAction } from './core/hooks.js';
import { init } from './core/tracker.js';
import { getResource } from './core/config.js';
import * as queue from './transport/queue.js';

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
        queue.add(type, data);
    },

    // Backward compat alias
    event: function (name, data) {
        if (!data) data = {};
        data.timestamp = Date.now();

        if (!data.resource_id) {
            data.resource_id = getResource('resourceId') || '';
        }

        queue.add('custom_event', {
            event_name: name,
            event_data: JSON.stringify(data),
        });
    },
};

// Legacy global alias
window.wp_statistics_event = window.wp_statistics.event;

// Boot when DOM is ready. The readyState check covers consent plugins
// (e.g. Borlabs) that re-execute blocked scripts after page load.
if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
