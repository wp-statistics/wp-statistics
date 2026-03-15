/**
 * WP Statistics Engagement Tracker
 *
 * Tracks user engagement time using a running total approach (like Plausible).
 * Only counts active time: page visible + window focused + user activity within timeout.
 */

// State (initialized properly in init())
var isVisible = false;
var isFocused = false;
var isEngaged = false;

// Timing
var engagementStart = 0;
var engagementTotal = 0;
var lastActivityTime = 0;

// Configuration
var activityTimeout = 30 * 1000; // 30 seconds

// Bound handler references for cleanup
var boundHandlers = null;

// Activity throttle (prevent 60Hz mousemove from hammering recordActivity)
var lastActivityRecordTime = 0;
var ACTIVITY_THROTTLE_MS = 1000;

export function init(options) {
    if (options && options.activityTimeout) {
        activityTimeout = options.activityTimeout;
    }

    isVisible = document.visibilityState === 'visible';
    isFocused = document.hasFocus();
    lastActivityTime = Date.now();

    bindEvents();
    updateEngagementState();
}

function bindEvents() {
    // Remove previous listeners if re-initializing
    if (boundHandlers) {
        unbindEvents();
    }

    boundHandlers = {
        visibilityChange: function () {
            isVisible = document.visibilityState === 'visible';
            updateEngagementState();
        },
        focus: function () {
            isFocused = true;
            updateEngagementState();
        },
        blur: function () {
            isFocused = false;
            updateEngagementState();
        },
        pagehide: function () {
            stopEngagement();
        },
        activity: function () {
            var now = Date.now();
            if (now - lastActivityRecordTime < ACTIVITY_THROTTLE_MS) return;
            lastActivityRecordTime = now;
            recordActivity();
        },
    };

    document.addEventListener('visibilitychange', boundHandlers.visibilityChange);
    window.addEventListener('focus', boundHandlers.focus);
    window.addEventListener('blur', boundHandlers.blur);
    window.addEventListener('pagehide', boundHandlers.pagehide);

    var activityEvents = ['click', 'scroll', 'keypress', 'mousemove', 'touchstart'];
    for (var i = 0; i < activityEvents.length; i++) {
        document.addEventListener(activityEvents[i], boundHandlers.activity, { passive: true });
    }
}

function unbindEvents() {
    if (!boundHandlers) return;

    document.removeEventListener('visibilitychange', boundHandlers.visibilityChange);
    window.removeEventListener('focus', boundHandlers.focus);
    window.removeEventListener('blur', boundHandlers.blur);
    window.removeEventListener('pagehide', boundHandlers.pagehide);

    var activityEvents = ['click', 'scroll', 'keypress', 'mousemove', 'touchstart'];
    for (var i = 0; i < activityEvents.length; i++) {
        document.removeEventListener(activityEvents[i], boundHandlers.activity);
    }

    boundHandlers = null;
}

function recordActivity() {
    lastActivityTime = Date.now();
    if (!isEngaged && isVisible && isFocused) {
        updateEngagementState();
    }
}

function isUserActive() {
    return (Date.now() - lastActivityTime) < activityTimeout;
}

function updateEngagementState() {
    var shouldBeEngaged = isVisible && isFocused && isUserActive();

    if (shouldBeEngaged && !isEngaged) {
        startEngagement();
    } else if (!shouldBeEngaged && isEngaged) {
        stopEngagement();
    }
}

function startEngagement() {
    if (isEngaged) return;
    isEngaged = true;
    engagementStart = Date.now();
}

function stopEngagement() {
    if (!isEngaged) return;
    if (engagementStart > 0) {
        engagementTotal += Date.now() - engagementStart;
    }
    isEngaged = false;
    engagementStart = 0;
}

export function getTotalEngagementTime() {
    if (isEngaged && engagementStart > 0) {
        return engagementTotal + (Date.now() - engagementStart);
    }
    return engagementTotal;
}

export function reset() {
    stopEngagement();
    engagementTotal = 0;
    lastActivityTime = Date.now();
    updateEngagementState();
}

export function resetAfterFlush() {
    engagementTotal = 0;
    if (isEngaged) {
        engagementStart = Date.now();
    }
}

export function destroy() {
    stopEngagement();
    unbindEvents();
    engagementTotal = 0;
}
