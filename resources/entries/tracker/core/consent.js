/**
 * WP Statistics Consent Module
 *
 * Filter-based consent using the hook system.
 * Looks up the active consent adapter from the global registry
 * (window.WpStatisticsConsentAdapters) and calls its init() method.
 *
 * Both built-in and third-party adapters register on the same registry,
 * so no code changes are needed to support new consent plugins.
 */

import { addFilter, doAction } from './hooks.js';
import { getConsentConfig, getTrackingLevels, isAnonymousTracking } from './config.js';

export function registerConsentAdapter() {
    var config = getConsentConfig();
    var mode = (config && config.mode) ? config.mode : 'none';

    if (mode === 'none') return;

    var adapters = window.WpStatisticsConsentAdapters || {};
    var adapter = adapters[mode];

    if (!adapter || typeof adapter.init !== 'function') {
        console.warn('WP Statistics: No consent adapter found for mode "' + mode + '". Tracking disabled.');
        return;
    }

    adapter.init({
        config: config,
        levels: getTrackingLevels(),
        addFilter: addFilter,
        doAction: doAction,
        anonymousTracking: isAnonymousTracking()
    });
}
