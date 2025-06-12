import { dashboardNonce, ajaxUrl } from '@WpStatistics/dashboard';

/** Prefix applied to every admin‑ajax action */
const ACTION_PREFIX = 'wp_statistics_';

/**
 * Send an AJAX POST request to WordPress using fetch
 * @param {string} action - The WordPress AJAX action (without prefix)
 * @param {Object} data   - Key/value pairs to send
 * @param {function} [setLoading] - Optional callback to indicate loading state
 * @returns {Promise<any>}
 * Resolves with <data> on wp_send_json_success
 * Rejects with Error(message) on wp_send_json_error or HTTP failure
 */
export const ajaxPost = async (action, data = {}, setLoading) => {
    if (typeof setLoading === 'function') {
        setLoading(true);
    }
    const prefixedAction = `${ACTION_PREFIX}${action}`;

    const body = new URLSearchParams({
        action: prefixedAction,
        ...data,
    });
    if (dashboardNonce) body.append('_ajax_nonce', dashboardNonce);

    try {
        const response = await fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body,
        });

        if (!response.ok) {
            const message = await response.text();
            throw new Error(`HTTP ${response.status}: ${message}`);
        }
        const json = await response.json();

        if (typeof json.success === 'boolean') {
            if (json.success) {
                return json.data;
            }
            const message = json.data?.message || 'Request failed';
            throw new Error(message);
        }

        return json;
    } finally {
        if (typeof setLoading === 'function') {
            setLoading(false);
        }
    }
};

/**
 * Send an AJAX GET request to WordPress using fetch
 * @param {string} action - The WordPress AJAX action (without prefix)
 * @param {Object} data   - Query parameters
 * @param {function} [setLoading] - Optional callback to indicate loading state
 * @returns {Promise<any>}
 * Resolves with <data> on wp_send_json_success
 * Rejects with Error(message) on wp_send_json_error or HTTP failure
 */
export const ajaxGet = async (action, data = {}, setLoading) => {
    if (typeof setLoading === 'function') {
        setLoading(true);
    }
    const prefixedAction = `${ACTION_PREFIX}${action}`;

    const params = new URLSearchParams({
        action: prefixedAction,
        ...data,
    });
    if (dashboardNonce) params.append('_ajax_nonce', dashboardNonce);

    try {
        const response = await fetch(`${ajaxUrl}?${params.toString()}`, {
            credentials: 'same-origin',
        });

        if (!response.ok) {
            const message = await response.text();
            throw new Error(`HTTP ${response.status}: ${message}`);
        }
        const json = await response.json();

        // WordPress conventions: wp_send_json_success / wp_send_json_error
        if (typeof json.success === 'boolean') {
            if (json.success) {
                return json.data;
            }
            const message = json.data?.message || 'Request failed';
            throw new Error(message);
        }

        return json;
    } finally {
        if (typeof setLoading === 'function') {
            setLoading(false);
        }
    }
};