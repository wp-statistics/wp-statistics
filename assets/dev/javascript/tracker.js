let WP_Statistics_CheckTime = 30; //sec

// Check DoNotTrack Settings on User Browser
let WP_Statistics_Dnd_Active = parseInt(navigator.msDoNotTrack || window.doNotTrack || navigator.doNotTrack, 10);

let wpStatisticsUserOnline = {
    init: function () {
        this.checkHitRequestConditions();
        this.keepUserOnline();
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
    sendHitRequest: function () {
        var WP_Statistics_http = new XMLHttpRequest();
        WP_Statistics_http.open("GET", WP_Statistics_Tracker_Object.hitRequestUrl + "&referred=" + encodeURIComponent(document.referrer) + "&_=" + Date.now(), true);
        WP_Statistics_http.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        WP_Statistics_http.send(null);
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
            }.bind(this),
            WP_Statistics_CheckTime * 1000
        );
    },
};

wpStatisticsUserOnline.init();
