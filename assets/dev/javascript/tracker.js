let checkTime = 30; //sec

// Check DoNotTrack Settings on User Browser
let WP_Statistics_Dnd_Active = parseInt(navigator.msDoNotTrack || window.doNotTrack || navigator.doNotTrack, 10);

let wpStatisticsUserOnline = {
    init: function () {
        this.hitRequest();
        this.keepUserOnline();
    },

    hitRequest: function () {
        if (jsArgs.dntEnabled && jsArgs.cacheCompatibility) {
            if (WP_Statistics_Dnd_Active !== 1) {
                var WP_Statistics_http = new XMLHttpRequest();
                WP_Statistics_http.open("GET", jsArgs.hitRequestUrl + "&referred=" + encodeURIComponent(document.referrer) + "&_=" + Date.now(), true);
                WP_Statistics_http.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                WP_Statistics_http.send(null);
            }
        }
    },

    // Send Request to REST API to Show User Is Online
    sendOnlineUserRequest: function () {
        var WP_Statistics_http = new XMLHttpRequest();
        WP_Statistics_http.open("GET", jsArgs.keepOnlineRequestUrl);
        WP_Statistics_http.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        WP_Statistics_http.send(null);
    },

    // Execute Send Online User Request Function Every n Sec
    keepUserOnline: function () {
        setInterval(
            function () {
                if (!document.hidden) {
                    if (jsArgs.dntEnabled) {
                        if (WP_Statistics_Dnd_Active !== 1) {
                            this.sendOnlineUserRequest();
                        }
                    } else {
                        this.sendOnlineUserRequest();
                    }
                }
            }.bind(this),
            checkTime * 1000
        );
    },
};

wpStatisticsUserOnline.init();
