let WP_Statistics_CheckTime = 60000;

// Check DoNotTrack Settings on User Browser
let WP_Statistics_Dnd_Active = parseInt(navigator.msDoNotTrack || window.doNotTrack || navigator.doNotTrack, 10);

let wpStatisticsUserOnline = {
    init: function () {
        if (typeof WP_Statistics_Tracker_Object == "undefined") {
            console.log('Variable WP_Statistics_Tracker_Object not found on the page source. Please ensure that you have excluded the /wp-content/plugins/wp-statistics/assets/js/tracker.js file from your cache and then clear your cache.');
        } else {
            this.checkHitRequestConditions();
            this.keepUserOnline();
        }
    },

    // Check Conditions for Sending Hit Request
    checkHitRequestConditions: function () {
        if (WP_Statistics_Tracker_Object.option.cacheCompatibility) {
            if (WP_Statistics_Tracker_Object.option.dntEnabled) {
                if (WP_Statistics_Dnd_Active !== 1) {
                    this.sendHitRequest();
                }
            } else {
                this.sendHitRequest();
            }
        }
    },

    //Sending Hit Request
    sendHitRequest: async function () {
        try {
            const referred = encodeURIComponent(document.referrer);
            const timestamp = Date.now();
            const requestUrl = `${WP_Statistics_Tracker_Object.hitRequestUrl}&referred=${referred}&_=${timestamp}`;

            const response = await fetch(requestUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                },
            });
            if (!response.ok) {
                console.error('Hit request failed!');
            }
        } catch (error) {
            console.error('An error occurred on sending hit request:', error);
        }
    },

    // Send Request to REST API to Show User Is Online
    sendOnlineUserRequest: function () {
        var WP_Statistics_http = new XMLHttpRequest();
        WP_Statistics_http.open("GET", WP_Statistics_Tracker_Object.keepOnlineRequestUrl);
        WP_Statistics_http.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        WP_Statistics_http.send(null);
    },

    // Execute Send Online User Request Function Every n Sec
    keepUserOnline: function () {
        setInterval(
            function () {
                if (!document.hidden) {
                    if (WP_Statistics_Tracker_Object.option.dntEnabled) {
                        if (WP_Statistics_Dnd_Active !== 1) {
                            this.sendOnlineUserRequest();
                        }
                    } else {
                        this.sendOnlineUserRequest();
                    }
                }
            }.bind(this), WP_Statistics_CheckTime
        );
    },
};

document.addEventListener('DOMContentLoaded', function () {
    wpStatisticsUserOnline.init();
});