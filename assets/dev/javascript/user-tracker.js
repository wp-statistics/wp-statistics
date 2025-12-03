if (!window.WpStatisticsUserTracker) {
    window.WpStatisticsUserTracker = {
        // Track URL changes for AJAX in Gutenberg SPA mode using History API
        lastUrl: window.location.href,

        // Save original history methods
        originalPushState: history.pushState,
        originalReplaceState: history.replaceState,

        // Check DoNotTrack Settings on User Browser
        isDndActive: parseInt(navigator.msDoNotTrack || window.doNotTrack || navigator.doNotTrack, 10),

        // Prevent init() from running more than once
        hasTrackerInitializedOnce: false,

        // Prevent trackUrlChange() from running more than once
        hasUrlChangeTrackerInitialized: false,

        // Flag to track hit request status
        hitRequestSuccessful: true,

        // Flag to detect if barba.js is being used
        barbaInitialized: false,

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
            }

            this.initBarbaSupport();

            if (!this.barbaInitialized) {
                this.trackUrlChange();
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
                let requestUrl = this.getRequestUrl();
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

        getRequestUrl: function () {
            let requestUrl = `${WP_Statistics_Tracker_Object.requestUrl}/`;

            if (WP_Statistics_Tracker_Object.option.bypassAdBlockers) {
                requestUrl = WP_Statistics_Tracker_Object.ajaxUrl;
            } else {
                requestUrl += WP_Statistics_Tracker_Object.hitParams.endpoint;
            }

            return requestUrl;
        },

        // Extract WP_Statistics_Tracker_Object from HTML string
        extractTrackerObjectFromHTML: function (html) {
            try {
                const match = html.match(/var\s+WP_Statistics_Tracker_Object\s*=\s*(\{[\s\S]*?\});/);
                if (match && match[1]) {
                    return JSON.parse(match[1]);
                }
            } catch (error) {
                console.error("WP Statistics: Error extracting WP_Statistics_Tracker_Object from HTML", error);
            }
            return null;
        },

        // Flag to prevent History API hooks from firing during barba transitions
        barbaTransitioning: false,

        // Initialize barba.js support if barba is detected
        initBarbaSupport: function () {
            const self = this;

            // Check if barba.js is loaded
            if (typeof barba === 'undefined') {
                return;
            }

            if (this.barbaInitialized) {
                return;
            }

            this.barbaInitialized = true;
            console.log('WP Statistics: Barba.js detected, initializing support');

            // Hook into barba.js lifecycle - use beforeEnter to get data before DOM update
            barba.hooks.beforeEnter(async (data) => {
                console.log('WP Statistics: Barba beforeEnter hook fired');
                self.barbaTransitioning = true;
                self.lastUrl = window.location.href; // Update immediately to block History API handler

                let newData = null;

                // Try to get HTML from barba's transition data first
                if (data && data.next && data.next.html) {
                    console.log('WP Statistics: Extracting from barba data.next.html');
                    newData = self.extractTrackerObjectFromHTML(data.next.html);
                }

                // If that didn't work, fetch the current URL ourselves
                if (!newData) {
                    console.log('WP Statistics: Fetching current URL to get tracker data:', window.location.href);
                    try {
                        const response = await fetch(window.location.href);
                        const html = await response.text();
                        newData = self.extractTrackerObjectFromHTML(html);
                    } catch (e) {
                        console.error('WP Statistics: Error fetching page data', e);
                    }
                }

                console.log('WP Statistics: New data:', newData);
                console.log('WP Statistics: Current data:', WP_Statistics_Tracker_Object);

                // Only send hit if we actually got new data that's different
                if (newData && JSON.stringify(newData.hitParams) !== JSON.stringify(WP_Statistics_Tracker_Object.hitParams)) {
                    console.log('WP Statistics: Data changed, updating and sending hit');
                    WP_Statistics_Tracker_Object = newData;
                    self.checkHitRequestConditions();
                } else {
                    console.log('WP Statistics: No new data found or data unchanged, skipping hit request');
                }

                // Keep flag set for a bit longer to ensure History API events are blocked
                setTimeout(function () {
                    self.barbaTransitioning = false;
                    console.log('WP Statistics: Barba transition complete, flag reset');
                }, 200);
            });
        },

        // Function to update the WP_Statistics_Tracker_Object when URL changes
        updateTrackerObject: function (callback) {
            const self = this;
            let callbackCalled = false;
            let attempts = 0;
            const maxAttempts = 20; // Maximum 2 seconds (20 * 100ms)

            // Function to attempt to parse and update the tracker object
            const tryUpdate = function () {
                // Re-query the script tag each time (it may be replaced by Interactivity API)
                const scriptTag = document.getElementById("wp-statistics-tracker-js-extra");

                if (!scriptTag) {
                    attempts++;
                    if (attempts < maxAttempts) {
                        setTimeout(tryUpdate, 100);
                    } else if (!callbackCalled && callback) {
                        callbackCalled = true;
                        callback();
                    }
                    return;
                }

                try {
                    const match = scriptTag.innerHTML.match(/var\s+WP_Statistics_Tracker_Object\s*=\s*(\{[\s\S]*?\});/);
                    if (match && match[1]) {
                        const newData = JSON.parse(match[1]);

                        // Check if data has actually changed by comparing a key property
                        // Use page_id or any unique identifier from hitParams
                        const dataChanged = !WP_Statistics_Tracker_Object.hitParams ||
                            JSON.stringify(WP_Statistics_Tracker_Object.hitParams) !== JSON.stringify(newData.hitParams);

                        if (dataChanged) {
                            WP_Statistics_Tracker_Object = newData;
                            if (!callbackCalled && callback) {
                                callbackCalled = true;
                                callback();
                            }
                        } else {
                            // Data hasn't changed yet, try again
                            attempts++;
                            if (attempts < maxAttempts) {
                                setTimeout(tryUpdate, 100);
                            } else if (!callbackCalled && callback) {
                                callbackCalled = true;
                                callback();
                            }
                        }
                    } else {
                        attempts++;
                        if (attempts < maxAttempts) {
                            setTimeout(tryUpdate, 100);
                        } else if (!callbackCalled && callback) {
                            callbackCalled = true;
                            callback();
                        }
                    }
                } catch (error) {
                    console.error("WP Statistics: Error parsing WP_Statistics_Tracker_Object", error);
                    if (!callbackCalled && callback) {
                        callbackCalled = true;
                        callback();
                    }
                }
            };

            // Start trying to update with a small initial delay to let DOM settle
            setTimeout(tryUpdate, 50);
        },

        // Detect URL changes caused by History API (pushState, replaceState) or browser navigation
        trackUrlChange: function () {
            // Only set up History API wrappers once
            if (this.hasUrlChangeTrackerInitialized) {
                return;
            }
            this.hasUrlChangeTrackerInitialized = true;

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
            const self = this;

            console.log('WP Statistics: handleUrlChange called, barbaTransitioning:', this.barbaTransitioning);

            // Skip if barba.js is handling the transition
            if (this.barbaTransitioning) {
                console.log('WP Statistics: Skipping handleUrlChange - barba is handling it');
                return;
            }

            if (window.location.href !== this.lastUrl) {
                console.log('WP Statistics: URL changed from', this.lastUrl, 'to', window.location.href);
                this.lastUrl = window.location.href;
                this.updateTrackerObject(function () {
                    // Don't re-initialize, just send the hit request
                    self.checkHitRequestConditions();
                });
            }
        }
    };
}