/**
 * WP Statistics Batch Queue
 *
 * Batches engagement time and events into single requests.
 * Uses sendBeacon for reliable delivery on page exit.
 */

import { sendBeaconOrFetch } from './send.js';
import { getBatchEndpoint } from './endpoint.js';
import { applyFilters, doAction } from '../core/hooks.js';
import * as engagement from '../trackers/engagement.js';

// Queue storage
var queue = [];

// Configuration
var maxQueueSize = 10;
var flushInterval = 60000;
var maxPayloadSize = 63000;

// State
var flushIntervalId = null;
var isInitialized = false;
var lastFlushTime = 0;
var minFlushInterval = 3000;

// Named exit event handlers for cleanup
var exitHandlers = null;

export function init(options) {
    if (isInitialized) return;

    if (options && options.maxQueueSize) maxQueueSize = options.maxQueueSize;
    if (options && options.flushInterval) flushInterval = options.flushInterval;

    bindExitEvents();
    isInitialized = true;
}

export function add(eventType, data) {
    var event = {
        type: eventType,
        data: data,
        timestamp: Date.now(),
        url: window.location.href,
    };

    queue.push(event);

    if (queue.length >= maxQueueSize) {
        flush('queue_full');
    }
}

export function flush(reason) {
    var currentEngagementTime = engagement.getTotalEngagementTime();
    var hasEvents = queue.length > 0;
    var now = Date.now();

    if (currentEngagementTime === 0 && !hasEvents) return;

    var timeSinceLastFlush = now - lastFlushTime;
    if (timeSinceLastFlush < minFlushInterval && !hasEvents) return;

    var events = queue.slice();
    queue = [];

    var payload = {
        engagement_time: currentEngagementTime,
        events: events,
    };

    // Allow plugins to modify batch before flush
    payload = applyFilters('batchPayload', payload);

    doAction('beforeFlush', payload, reason);

    lastFlushTime = now;

    engagement.resetAfterFlush();

    sendPayload(payload);
}

function sendPayload(payload) {
    var url = getBatchEndpoint();
    var data = JSON.stringify(payload);

    if (data.length > maxPayloadSize) {
        var events = payload.events || [];
        var chunkSize = Math.ceil(events.length / 2);

        for (var i = 0; i < events.length; i += chunkSize) {
            var chunkPayload = {
                engagement_time: i === 0 ? payload.engagement_time : 0,
                events: events.slice(i, i + chunkSize),
            };
            sendBeaconOrFetch(url, JSON.stringify(chunkPayload));
        }
        return;
    }

    sendBeaconOrFetch(url, data);
}

export function startPeriodicFlush() {
    stopPeriodicFlush();

    flushIntervalId = setInterval(function () {
        var hasEvents = queue.length > 0;
        var hasEngagement = engagement.getTotalEngagementTime() > 0;

        if (hasEvents || hasEngagement) {
            flush('periodic');
        }
    }, flushInterval);
}

export function stopPeriodicFlush() {
    if (flushIntervalId) {
        clearInterval(flushIntervalId);
        flushIntervalId = null;
    }
}

function bindExitEvents() {
    exitHandlers = {
        visibilityChange: function () {
            if (document.visibilityState === 'hidden') {
                flush('visibility_hidden');
            }
        },
        pagehide: function () {
            flush('pagehide');
        },
    };

    document.addEventListener('visibilitychange', exitHandlers.visibilityChange);
    window.addEventListener('pagehide', exitHandlers.pagehide);
}

function unbindExitEvents() {
    if (!exitHandlers) return;
    document.removeEventListener('visibilitychange', exitHandlers.visibilityChange);
    window.removeEventListener('pagehide', exitHandlers.pagehide);
    exitHandlers = null;
}

export function clear() {
    queue = [];
}

export function destroy() {
    flush('destroy');
    stopPeriodicFlush();
    unbindExitEvents();
    isInitialized = false;
}
