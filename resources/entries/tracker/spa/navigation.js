/**
 * WP Statistics SPA Navigation Handler
 *
 * Patches History API (pushState/replaceState) once at init.
 * On URL change: flush → reset → notify → re-hit.
 */

import { doAction } from '../core/hooks.js';
import { refresh as refreshConfig } from '../core/config.js';
import * as queue from '../transport/queue.js';
import * as engagement from '../trackers/engagement.js';
import * as hit from '../trackers/hit.js';

var lastUrl = '';
var originalPushState = null;
var originalReplaceState = null;
var isPatched = false;
var onNavigationCallback = null;

export function init(onNavigation) {
    if (isPatched) return;
    isPatched = true;
    onNavigationCallback = onNavigation || null;

    lastUrl = window.location.href;
    originalPushState = history.pushState;
    originalReplaceState = history.replaceState;

    history.pushState = function () {
        originalPushState.apply(history, arguments);
        handleUrlChange();
    };

    history.replaceState = function () {
        originalReplaceState.apply(history, arguments);
        handleUrlChange();
    };

    window.addEventListener('popstate', handleUrlChange);
}

function handleUrlChange() {
    if (window.location.href === lastUrl) return;
    lastUrl = window.location.href;

    // 1. Flush current engagement + events
    queue.flush('spa_navigation');

    // 2. Reset engagement timer
    engagement.reset();

    // 3. Clear queue for new page
    queue.clear();

    // 4. Notify premium/plugins
    doAction('spaNavigation');

    // 5. Re-read global config (may have changed via SPA framework)
    refreshConfig();

    // 6. Reset hit state and re-send
    hit.resetState();

    // 7. Notify orchestrator for re-init
    if (onNavigationCallback) {
        onNavigationCallback();
    }
}

export function destroy() {
    if (!isPatched) return;
    history.pushState = originalPushState;
    history.replaceState = originalReplaceState;
    window.removeEventListener('popstate', handleUrlChange);
    isPatched = false;
    onNavigationCallback = null;
}
