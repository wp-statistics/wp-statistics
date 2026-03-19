/**
 * Transport layer: sendBeacon → fetch(keepalive) → XHR fallback.
 *
 * Provides two send strategies:
 * - sendXhr: synchronous-style XHR for page hits (needs response)
 * - sendBeaconOrFetch: fire-and-forget for batch/engagement data
 */

/**
 * Send a POST request via XHR (for page hit — needs response).
 * Returns a Promise that resolves with parsed JSON response.
 */
export function sendXhr(url, params) {
    return new Promise(function (resolve, reject) {
        try {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            resolve(JSON.parse(xhr.responseText));
                        } catch (e) {
                            console.warn('WP Statistics: Hit response was not valid JSON.');
                            resolve({ status: false });
                        }
                    } else {
                        reject(new Error('Hit request failed with status ' + xhr.status));
                    }
                }
            };
            xhr.send(params);
        } catch (error) {
            reject(error);
        }
    });
}

function buildFormData(dataString) {
    var formData = new FormData();
    formData.append('batch_data', dataString);
    return formData;
}

/**
 * Send data using sendBeacon with FormData (fire-and-forget).
 * Falls back to fetch(keepalive) if sendBeacon fails.
 */
export function sendBeaconOrFetch(url, jsonData) {
    var dataString = typeof jsonData === 'string' ? jsonData : JSON.stringify(jsonData);

    if (navigator.sendBeacon) {
        if (navigator.sendBeacon(url, buildFormData(dataString))) {
            return;
        }
    }

    // Fallback to fetch with keepalive
    try {
        fetch(url, {
            method: 'POST',
            body: buildFormData(dataString),
            keepalive: true,
        }).catch(function (err) {
            console.warn('WP Statistics: Batch send failed:', err.message || err);
        });
    } catch (e) {
        console.warn('WP Statistics: Batch transport error:', e.message || e);
    }
}
