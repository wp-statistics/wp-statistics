if (!window.WpStatisticsUserTracker) {
    window.WpStatisticsUserTracker = {
        // User online interval id (legacy - kept for backward compatibility)
        userOnlineIntervalId: null,

        // Track URL changes for AJAX in Gutenberg SPA mode using History API
        lastUrl: window.location.href,

        // Save original history methods
        originalPushState: history.pushState,
        originalReplaceState: history.replaceState,

        // Check user activity every x seconds (legacy)
        checkTime: WP_Statistics_Tracker_Object.jsCheckTime,

        // Check DoNotTrack Settings on User Browser
        isDndActive: parseInt(navigator.msDoNotTrack || window.doNotTrack || navigator.doNotTrack, 10),

        // Prevent init() from running more than once
        hasTrackerInitializedOnce: false,

        // Flag to track hit request status
        hitRequestSuccessful: true,

        // Whether to use new engagement tracking
        useEngagementTracking: true,

        init: function () {
            if (this.hasTrackerInitializedOnce) {
                return;
            }
            this.hasTrackerInitializedOnce = true;

            if (WP_Statistics_Tracker_Object.option.isPreview) {
                return;
            }

            if (typeof WP_Statistics_Tracker_Object == "undefined") {
                console.error('WP Statistics: Variable WP_Statistics_Tracker_Object not found. Ensure /wp-content/plugins/wp-statistics/public/frontend/js/tracker.js is either excluded from cache settings or not dequeued by any plugin. Clear your cache if necessary.');
            } else {
                // Send initial hit request (engagement tracking will be initialized after hit succeeds)
                // Session ID will be set from the hit response
                this.checkHitRequestConditions();
                // Only use legacy keepUserOnline if engagement tracking is disabled
                // The new engagement tracking uses batch queue instead
                if (WP_Statistics_Tracker_Object.option.userOnline && !this.useEngagementTracking) {
                    this.keepUserOnline();
                }
            }

            this.trackUrlChange();
        },

        /**
         * Initialize the new engagement tracking system
         */
        initEngagementTracking: function() {
            // Initialize batch queue (always uses AJAX endpoint)
            if (window.WpStatisticsBatchQueue) {
                WpStatisticsBatchQueue.init({
                    ajaxUrl: WP_Statistics_Tracker_Object.ajaxUrl,
                    maxQueueSize: 10,
                    flushInterval: 60000, // 60 seconds (periodic flush interval)
                    // Pass engagement data getter (session is identified server-side)
                    getSessionData: function() {
                        return {
                            engagement_time: window.WpStatisticsEngagementTracker
                                ? WpStatisticsEngagementTracker.getTotalEngagementTime()
                                : 0
                        };
                    }
                });

                // Start periodic flush to prevent data loss during long sessions
                WpStatisticsBatchQueue.startPeriodicFlush();
            }

            // Initialize engagement tracker (no callbacks needed - data is fetched on flush)
            if (window.WpStatisticsEngagementTracker) {
                WpStatisticsEngagementTracker.init({
                    activityTimeout: 30 * 1000 // 30 seconds
                    // No onEngagementChange or onEngagementPulse callbacks
                    // Engagement data is pulled when batch queue flushes
                });
            }
        },

        /**
         * Get common parameters for all requests
         */
        getCommonParams: function() {
            return {
                ...WP_Statistics_Tracker_Object.onlineParams,
                signature: WP_Statistics_Tracker_Object.hitParams?.signature,
                referred: this.getReferred(),
                page_uri: this.getPathAndQueryString()
            };
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

                const visitorLocaleInfo = {
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                    language: navigator.language || navigator.userLanguage,
                    screenWidth: window.screen.width,
                    screenHeight: window.screen.height
                };

                const languageCode = navigator.language.split('-')[0],
                    languageName = (new Intl.DisplayNames(['en'], { type: 'language' })).of(languageCode);

                visitorLocaleInfo.languageFullName = languageName;

                const params = new URLSearchParams({
                    ...WP_Statistics_Tracker_Object.hitParams,
                    resourceUriId: WP_Statistics_Tracker_Object?.resourceUriId,
                    referred: this.getReferred(),
                    resourceUri: this.getPathAndQueryString(),
                    page_uri: this.getPathAndQueryString(),
                    timezone: visitorLocaleInfo.timezone,
                    language: visitorLocaleInfo.language,
                    languageFullName: visitorLocaleInfo.languageFullName,
                    screenWidth: visitorLocaleInfo.screenWidth,
                    screenHeight: visitorLocaleInfo.screenHeight
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

                            // Initialize engagement tracking after hit request succeeds
                            if (this.hitRequestSuccessful) {
                                this.initEngagementTracking();
                            }
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

        // Send Request to REST API to Show User Is Online (legacy method)
        sendOnlineUserRequest: async function () {
            if (!this.hitRequestSuccessful) {
                return;
            }

            try {
                let requestUrl = this.getRequestUrl('online');
                const params = new URLSearchParams({
                    ...WP_Statistics_Tracker_Object.onlineParams,
                    referred: this.getReferred(),
                    page_uri: this.getPathAndQueryString()
                }).toString();

                const xhr = new XMLHttpRequest();
                xhr.open('POST', requestUrl, true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send(params);
            } catch (error) {
                console.error('WP Statistics: Error sending online user request:', error);
            }
        },

        // Execute Send Online User Request Function Every n Sec (legacy method)
        keepUserOnline: function () {
            let userActivityTimeout;

            if (!WP_Statistics_Tracker_Object.option.userOnline) {
                return;
            }

            // Clear any existing interval to avoid duplicates
            if (this.userOnlineIntervalId !== null) {
                clearInterval(this.userOnlineIntervalId);
                this.userOnlineIntervalId = null;
            }

            this.userOnlineIntervalId = setInterval(
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
                        if (this.userOnlineIntervalId !== null) {
                            clearInterval(this.userOnlineIntervalId);
                            this.userOnlineIntervalId = null;
                        }
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
                } else if (type === 'batch') {
                    requestUrl += 'batch'; // New batch endpoint
                }
            }

            return requestUrl;
        },

        // Function to update the WP_Statistics_Tracker_Object when URL changes
        updateTrackerObject: function () {
            const scriptTag = document.getElementById("wp-statistics-tracker-js-extra");

            if (scriptTag) {
                try {
                    const match = scriptTag.innerHTML.match(/var\s+WP_Statistics_Tracker_Object\s*=\s*(\{[\s\S]*?\});/);
                    if (match && match[1]) {
                        WP_Statistics_Tracker_Object = JSON.parse(match[1]);
                    }
                } catch (error) {
                    console.error("WP Statistics: Error parsing WP_Statistics_Tracker_Object", error);
                }
            }
        },

        // Detect URL changes caused by History API (pushState, replaceState) or browser navigation
        trackUrlChange: function () {
            const self = this;

            window.removeEventListener('popstate', self.handleUrlChange);

            history.pushState = function () {
                self.originalPushState.apply(history, arguments);
                self.handleUrlChange();
            };

            history.replaceState = function () {
                self.originalReplaceState.apply(history, arguments);
                self.handleUrlChange();
            };

            window.addEventListener('popstate', function () {
                self.handleUrlChange();
            });
        },

        // Handles URL changes in an SPA environment.
        handleUrlChange: function () {
            if (window.location.href !== this.lastUrl) {
                this.lastUrl = window.location.href;
                this.updateTrackerObject();

                // Flush current engagement data before navigating
                if (window.WpStatisticsBatchQueue) {
                    WpStatisticsBatchQueue.flush('spa_navigation');
                }

                // Reset engagement tracking for new page
                if (window.WpStatisticsEngagementTracker && this.useEngagementTracking) {
                    WpStatisticsEngagementTracker.reset();
                }

                // Clear batch queue state for new page
                if (window.WpStatisticsBatchQueue) {
                    WpStatisticsBatchQueue.clear();
                }

                this.hasTrackerInitializedOnce = false;
                this.init();
            }
        }
    };
}
