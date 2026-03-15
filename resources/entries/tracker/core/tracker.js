/**
 * WP Statistics Tracker Orchestrator
 *
 * Wires all modules together: consent check → hit → engagement → SPA lifecycle.
 */

import { applyFilters, doAction, addAction } from './hooks.js';
import { getConfig, isPreview, getTrackingLevels } from './config.js';
import { registerConsentAdapter } from './consent.js';
import * as hit from '../trackers/hit.js';
import * as engagement from '../trackers/engagement.js';
import * as queue from '../transport/queue.js';
import * as navigation from '../spa/navigation.js';

var hasInitialized = false;

export function init() {
    var config = getConfig();
    if (!config || !config.option) {
        console.error('WP Statistics: Tracker configuration (WP_Statistics_Tracker_Object) is missing. Tracking disabled.');
        return;
    }

    if (isPreview()) {
        return;
    }

    // Register consent adapters (they add trackingLevel filters)
    registerConsentAdapter();

    // Check tracking level
    var levels = getTrackingLevels();
    var trackingLevel = applyFilters('trackingLevel', levels.full);

    if (trackingLevel === levels.none) {
        // Wait for consent to be granted
        addAction('consentChanged', onConsentChanged);
        return;
    }

    // Consent is available — start tracking
    startTracking(trackingLevel);
}

function onConsentChanged() {
    var levels = getTrackingLevels();
    var trackingLevel = applyFilters('trackingLevel', levels.full);
    if (trackingLevel === levels.none) return;

    startTracking(trackingLevel);
}

function startTracking(trackingLevel) {
    if (hasInitialized) return;
    hasInitialized = true;

    // Send page hit with tracking level
    hit.send(trackingLevel).then(function (success) {
        if (!success) return;

        // Initialize engagement tracking
        initEngagement();

        // Set up SPA navigation
        navigation.init(onSpaNavigation);

        // Notify plugins that tracker is ready
        doAction('trackerInit');
    });
}

function initEngagement() {
    engagement.init();

    queue.init({
        maxQueueSize: 10,
        flushInterval: 60000,
    });

    queue.startPeriodicFlush();
}

function onSpaNavigation() {
    var levels = getTrackingLevels();
    var trackingLevel = applyFilters('trackingLevel', levels.full);
    if (trackingLevel === levels.none) return;

    hit.send(trackingLevel).then(function (success) {
        if (success) {
            doAction('trackerInit');
        }
    });
}
