let WP_Statistics_CheckTime = WP_Statistics_Tracker_Object.jsCheckTime;

// Check DoNotTrack Settings on User Browser
let WP_Statistics_Dnd_Active = parseInt(navigator.msDoNotTrack || window.doNotTrack || navigator.doNotTrack, 10);

// Prevent init() from running more than once
let hasTrackerInitializedOnce = false;

const referred = encodeURIComponent(document.referrer);

let wpStatisticsUserOnline = {
    hitRequestSuccessful: true, // Flag to track hit request status

    init: function () {
        if (hasTrackerInitializedOnce) {
            return;
        }
        hasTrackerInitializedOnce = true;

        if (typeof WP_Statistics_Tracker_Object == "undefined") {
            console.log('Variable WP_Statistics_Tracker_Object not found on the page source. Please ensure that you have excluded the /wp-content/plugins/wp-statistics/assets/js/tracker.js file from your cache and then clear your cache.');
        } else {
            this.checkHitRequestConditions();

            if (WP_Statistics_Tracker_Object.option.userOnline) {
                this.keepUserOnline();
            }
        }
    },

    // Check Conditions for Sending Hit Request
    checkHitRequestConditions: function () {
        if (WP_Statistics_Tracker_Object.option.dntEnabled) {
            if (WP_Statistics_Dnd_Active !== 1) {
                this.sendHitRequest();
            } else {
                console.log('DNT is active.');
            }
        } else {
            this.sendHitRequest();
        }
    },

    // Sending Hit Request
    sendHitRequest: async function () {
        try {
            let requestUrl = this.getRequestUrl('hit');
            const params   = new URLSearchParams({
                ...WP_Statistics_Tracker_Object.hitParams,
                referred
            }).toString();
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', requestUrl, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(params);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const responseData = JSON.parse(xhr.responseText);

                        if (responseData.status === false) {
                            this.hitRequestSuccessful = false; // Set flag to false if status in response is false
                        }
                    } catch (e) {
                        this.hitRequestSuccessful = false; // Handle JSON parsing error
                    }
                } else {
                    this.hitRequestSuccessful = false; // Set flag to false if status is 403
                }
            }
        } catch (error) {
            this.hitRequestSuccessful = false;
        }
    },

    // Send Request to REST API to Show User Is Online
    sendOnlineUserRequest: async function () {
        if (!this.hitRequestSuccessful) {
            return; // Stop if hit request was not successful
        }

        try {
            let requestUrl = this.getRequestUrl('online');
            const params   = new URLSearchParams({
                ...WP_Statistics_Tracker_Object.onlineParams,
                referred
            }).toString();
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', requestUrl, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(params);
        } catch (error) {

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
                if ((!WP_Statistics_Tracker_Object.option.dntEnabled || (WP_Statistics_Tracker_Object.option.dntEnabled && WP_Statistics_Dnd_Active !== 1)) && this.hitRequestSuccessful) {
                    this.sendOnlineUserRequest();
                }
            }.bind(this), WP_Statistics_CheckTime
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

    getRequestUrl: function(type) {
        let requestUrl = `${WP_Statistics_Tracker_Object.requestUrl}/`;

        if (WP_Statistics_Tracker_Object.option.bypassAdBlockers) {
            requestUrl += 'wp-admin/admin-ajax.php';
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

document.addEventListener('DOMContentLoaded', function () {
    if (WP_Statistics_Tracker_Object.option.consentLevel == 'disabled' || WP_Statistics_Tracker_Object.option.trackAnonymously ||
        !WP_Statistics_Tracker_Object.option.isWpConsentApiActive || wp_has_consent(WP_Statistics_Tracker_Object.option.consentLevel)) {
        wpStatisticsUserOnline.init();
    }

    document.addEventListener("wp_listen_for_consent_change", function (e) {
        const changedConsentCategory = e.detail;
        for (let key in changedConsentCategory) {
            if (changedConsentCategory.hasOwnProperty(key)) {
                if (key === WP_Statistics_Tracker_Object.option.consentLevel && changedConsentCategory[key] === 'allow') {
                    wpStatisticsUserOnline.init();

                    // When trackAnonymously is enabled, the init() call above will get ignored (since it's already initialized before)
                    // So, in this specific case, we can call checkHitRequestConditions() manually
                    // This will insert a new record for the user (who just gave consent to us) and prevent other scripts (e.g. event.js) from malfunctioning
                    if (WP_Statistics_Tracker_Object.option.trackAnonymously) {
                        wpStatisticsUserOnline.checkHitRequestConditions();
                    }
                }
            }
        }
    });
});
