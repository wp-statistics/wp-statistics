/**
 * WP Statistics Hook/Filter System
 *
 * Lightweight WordPress-style hooks for JS.
 * Supports prioritized callbacks for both filters (transform values)
 * and actions (side effects).
 */

const hooks = {};

function addHook(type, name, callback, priority) {
    const key = type + ':' + name;
    if (!hooks[key]) {
        hooks[key] = [];
    }
    hooks[key].push({ callback, priority });
    hooks[key].sort((a, b) => a.priority - b.priority);
}

function removeHook(type, name, callback) {
    const key = type + ':' + name;
    if (!hooks[key]) return;
    hooks[key] = hooks[key].filter(h => h.callback !== callback);
}

export function addFilter(name, callback, priority = 10) {
    addHook('filter', name, callback, priority);
}

export function removeFilter(name, callback) {
    removeHook('filter', name, callback);
}

export function applyFilters(name, value, ...args) {
    const key = 'filter:' + name;
    if (!hooks[key]) return value;
    for (const hook of hooks[key]) {
        try {
            value = hook.callback(value, ...args);
        } catch (e) {
            console.error('WP Statistics: filter "' + name + '" threw:', e);
        }
    }
    return value;
}

export function addAction(name, callback, priority = 10) {
    addHook('action', name, callback, priority);
}

export function removeAction(name, callback) {
    removeHook('action', name, callback);
}

export function doAction(name, ...args) {
    const key = 'action:' + name;
    if (!hooks[key]) return;
    for (const hook of hooks[key]) {
        try {
            hook.callback(...args);
        } catch (e) {
            console.error('WP Statistics: action "' + name + '" threw:', e);
        }
    }
}
