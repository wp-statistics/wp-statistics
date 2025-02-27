const WpStatisticsUserTracker = {
    // Check user activity every x seconds
    checkTime: WP_Statistics_Tracker_Object.jsCheckTime,

    // Check DoNotTrack Settings on User Browser
    isDndActive: parseInt(navigator.msDoNotTrack || window.doNotTrack || navigator.doNotTrack, 10),

    // Prevent init() from running more than once
    hasTrackerInitializedOnce: false,

    // Flag to track hit request status
    hitRequestSuccessful: true,

    init: function () {
        if (this.hasTrackerInitializedOnce) {
            return;
        }
        this.hasTrackerInitializedOnce = true;

        if (WP_Statistics_Tracker_Object.option.isPreview) {
            return;
        }

        if (typeof WP_Statistics_Tracker_Object == "undefined") {
            console.error('WP Statistics: Variable WP_Statistics_Tracker_Object not found. Ensure /wp-content/plugins/wp-statistics/assets/js/tracker.js is either excluded from cache settings or not dequeued by any plugin. Clear your cache if necessary.');
        } else {
            this.checkHitRequestConditions();

            if (WP_Statistics_Tracker_Object.option.userOnline) {
                this.keepUserOnline();
            }
        }
    },

    // Method to Base64 encode a string using modern approach
    base64Encode: function (str) {
        const encoder = new TextEncoder();
        const data = encoder.encode(str);
        return btoa(String.fromCharCode.apply(null, data));
    },

    // Extract Path and Query String from Current URL and Base64 encode it
    getPathAndQueryString: function () {
        const pathname = window.location.pathname;
        const queryString = window.location.search;
        const fullPath = pathname + queryString;
        return this.base64Encode(fullPath);
    },

    // Get Referred URL and Base64 encode it
    getReferred: function () {
        return this.base64Encode(document.referrer);
    },

    // Check Conditions for Sending Hit Request
    checkHitRequestConditions: function () {
        if (WP_Statistics_Tracker_Object.option.dntEnabled) {
            if (this.isDndActive !== 1) {
                this.sendHitRequest();
            } else {
                console.log('WP Statistics: Do Not Track (DNT) is enabled. Hit request not sent.');
            }
        } else {
            this.sendHitRequest();
        }
    },

    // Sending Hit Request
    sendHitRequest: async function () {
        try {
            let requestUrl = this.getRequestUrl('hit');
            const params = new URLSearchParams({
                ...WP_Statistics_Tracker_Object.hitParams,
                referred: this.getReferred(), // Use the getReferred method
                page_uri: this.getPathAndQueryString() // Use the correct key for the path and query string (Base64 encoded)
            }).toString();

            const xhr = new XMLHttpRequest();
            xhr.open('POST', requestUrl, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(params);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        const responseData = JSON.parse(xhr.responseText);
                        this.hitRequestSuccessful = responseData.status !== false;
                    } else {
                        this.hitRequestSuccessful = false;
                        console.warn('WP Statistics: Hit request failed with status ' + xhr.status);
                    }
                }
            }.bind(this);
        } catch (error) {
            this.hitRequestSuccessful = false;
            console.error('WP Statistics: Error sending hit request:', error);
        }
    },

    // Send Request to REST API to Show User Is Online
    sendOnlineUserRequest: async function () {
        if (!this.hitRequestSuccessful) {
            return; // Stop if hit request was not successful
        }

        try {
            let requestUrl = this.getRequestUrl('online');
            const params = new URLSearchParams({
                ...WP_Statistics_Tracker_Object.onlineParams,
                referred: this.getReferred(), // Use the getReferred method
                page_uri: this.getPathAndQueryString() // Use the correct key for the path and query string (Base64 encoded)
            }).toString();

            const xhr = new XMLHttpRequest();
            xhr.open('POST', requestUrl, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(params);
        } catch (error) {
            console.error('WP Statistics: Error sending online user request:', error);
        }
    },

    // Execute Send Online User Request Function Every n Sec
    keepUserOnline: function () {
        let userActivityTimeout;

        if (!WP_Statistics_Tracker_Object.option.userOnline) {
            return; // Stop if userOnline option is false
        }

        const userOnlineInterval = setInterval(
            function () {
                if ((!WP_Statistics_Tracker_Object.option.dntEnabled || (WP_Statistics_Tracker_Object.option.dntEnabled && this.isDndActive !== 1)) && this.hitRequestSuccessful) {
                    this.sendOnlineUserRequest();
                }
            }.bind(this), this.checkTime
        );

        // After 30 mins of inactivity, stop keeping user online
        ['click', 'keypress', 'scroll', 'DOMContentLoaded'].forEach(event => {
            window.addEventListener(event, () => {
                clearTimeout(userActivityTimeout);

                userActivityTimeout = setTimeout(() => {
                    clearInterval(userOnlineInterval);
                }, 30 * 60 * 1000);
            });
        });
    },

    getRequestUrl: function (type) {
        let requestUrl = `${WP_Statistics_Tracker_Object.requestUrl}/`;

        if (WP_Statistics_Tracker_Object.option.bypassAdBlockers) {
            requestUrl = WP_Statistics_Tracker_Object.ajaxUrl;
        } else {
            if (type === 'hit') {
                requestUrl += WP_Statistics_Tracker_Object.hitParams.endpoint;
            } else if (type === 'online') {
                requestUrl += WP_Statistics_Tracker_Object.onlineParams.endpoint;
            }
        }

        return requestUrl;
    },
};