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

        // Stop future requests when consent is withdrawn
        isTrackingEnabled: false,

        // Record the latest URL change that happened while tracking was disabled
        pendingHit: null,

        // Wait for the current SPA page identity before restoring a pending hit
        isRestoringPendingHit: false,
        pendingUrlChangeGenerations: {},

        // Prevent trackUrlChange() from running more than once
        hasUrlChangeTrackerInitialized: false,

        // Coalesce only URL changes that come right after each other (a pushState then a
        // replaceState for the same navigation). Separate navigations each send a hit.
        urlChangeGeneration: 0,
        supersededUrlChangeGenerations: {},
        lastUrlChangeAt: 0,

        // Flag to track hit request status
        hitRequestSuccessful: true,

        init: function () {
            const wasTrackingEnabled = this.isTrackingEnabled;
            this.isTrackingEnabled = true;

            if (this.hasTrackerInitializedOnce) {
                if (!wasTrackingEnabled && (this.pendingHit || Object.keys(this.pendingUrlChangeGenerations).length > 0)) {
                    this.isRestoringPendingHit = true;

                    if (Object.keys(this.pendingUrlChangeGenerations).length === 0) {
                        this.flushPendingHit();
                    }
                }

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

            this.trackUrlChange();
        },

        stop: function () {
            this.isTrackingEnabled = false;
        },

        // Method to Base64 encode a string using modern approach
        base64Encode: function (str) {
            const encoder = new TextEncoder();
            const data = encoder.encode(str);
            return btoa(String.fromCharCode.apply(null, data));
        },

        // Extract Path and Query String from Current URL and Base64 encode it
        getPathAndQueryString: function (pathname = window.location.pathname, queryString = window.location.search) {
            const fullPath = pathname + queryString;
            return this.base64Encode(fullPath);
        },

        // Get Referred URL and Base64 encode it
        getReferred: function () {
            return this.base64Encode(document.referrer);
        },

        // Check Conditions for Sending Hit Request
        checkHitRequestConditions: function (trackerObject = WP_Statistics_Tracker_Object, pageUri = this.getPathAndQueryString()) {
            if (!this.isTrackingEnabled) {
                return;
            }

            if (trackerObject.option.dntEnabled) {
                if (this.isDndActive !== 1) {
                    this.sendHitRequest(trackerObject, pageUri);
                } else {
                    console.log('WP Statistics: Do Not Track (DNT) is enabled. Hit request not sent.');
                }
            } else {
                this.sendHitRequest(trackerObject, pageUri);
            }
        },

        flushPendingHit: function () {
            if (!this.pendingHit) {
                this.isRestoringPendingHit = false;
                return;
            }

            const pendingHit = this.pendingHit;
            this.pendingHit = null;
            this.isRestoringPendingHit = false;
            this.checkHitRequestConditions(pendingHit.trackerObject, pendingHit.pageUri);
        },

        // Sending Hit Request
        sendHitRequest: async function (trackerObject = WP_Statistics_Tracker_Object, pageUri = this.getPathAndQueryString()) {
            try {
                let requestUrl = this.getRequestUrl(trackerObject);
                const params = new URLSearchParams({
                    ...trackerObject.hitParams,
                    referred: this.getReferred(), // Use the getReferred method
                    page_uri: pageUri // Use the path and query string captured for this navigation
                }).toString();

                const xhr = new XMLHttpRequest();
                xhr.open('POST', requestUrl, true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send(params);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            const responseData = this.parseHitResponse(xhr.responseText);
                            this.hitRequestSuccessful = responseData !== null
                                && typeof responseData === 'object'
                                && !Array.isArray(responseData)
                                && responseData.status === true;

                            if (!this.hitRequestSuccessful) {
                                const responseError = responseData && (typeof responseData.data === 'string' ? responseData.data : responseData.message);
                                console.warn('WP Statistics: Hit request failed with status ' + xhr.status + (responseError ? ': ' + responseError : ''));
                            }
                        } else {
                            this.hitRequestSuccessful = false;
                            // An empty or HTML body (blocked request, error page) is not a PHP notice.
                            const responseData = this.parseHitResponse(xhr.responseText, true) || {};
                            const responseError = typeof responseData.data === 'string' ? responseData.data : responseData.message;
                            console.warn('WP Statistics: Hit request failed with status ' + xhr.status + (responseError ? ': ' + responseError : ''));
                        }
                    }
                }.bind(this);
            } catch (error) {
                this.hitRequestSuccessful = false;
                console.error('WP Statistics: Error sending hit request:', error);
            }
        },

        // Read the hit response. Another plugin or the theme can print a PHP notice before
        // the JSON when display_errors is on; the visit is already saved by then, so read the
        // JSON after the notice instead of failing on it (#1170).
        parseHitResponse: function (responseText, quiet) {
            try {
                return JSON.parse(responseText);
            } catch (error) {
                const start = responseText.indexOf('{"');

                if (start > -1) {
                    try {
                        return JSON.parse(responseText.slice(start));
                    } catch (innerError) {
                        // Fall through to the warning below.
                    }
                }

                if (!quiet) {
                    console.warn('WP Statistics: The hit response was not valid JSON. Another plugin or the theme may be printing PHP notices.');
                }

                return {status: false};
            }
        },

        getRequestUrl: function (trackerObject = WP_Statistics_Tracker_Object) {
            let requestUrl = `${trackerObject.requestUrl}/`;

            if (trackerObject.option.bypassAdBlockers) {
                requestUrl = trackerObject.ajaxUrl;
            } else {
                requestUrl += trackerObject.hitParams.endpoint;
            }

            return requestUrl;
        },

        // Function to update the WP_Statistics_Tracker_Object when URL changes
        // Uses polling to handle delayed DOM updates in SPAs
        updateTrackerObject: function (callback) {
            let callbackCalled = false;
            let attempts = 0;
            const maxAttempts = 20; // Maximum 2 seconds (20 * 100ms)

            const finish = function (trackerObject) {
                if (!callbackCalled && callback) {
                    callbackCalled = true;
                    callback(trackerObject);
                }
            };

            const tryUpdate = function () {
                // Re-query the script tag each time (it may be replaced by Interactivity API)
                const scriptTag = document.getElementById("wp-statistics-tracker-js-extra");

                if (!scriptTag) {
                    attempts++;
                    if (attempts < maxAttempts) {
                        setTimeout(tryUpdate, 100);
                    } else {
                        finish(WP_Statistics_Tracker_Object);
                    }
                    return;
                }

                try {
                    const match = scriptTag.innerHTML.match(/var\s+WP_Statistics_Tracker_Object\s*=\s*(\{[\s\S]*?\});/);
                    if (match && match[1]) {
                        const newData = JSON.parse(match[1]);

                        // Check if data has actually changed
                        const dataChanged = !WP_Statistics_Tracker_Object.hitParams ||
                            JSON.stringify(WP_Statistics_Tracker_Object.hitParams) !== JSON.stringify(newData.hitParams);

                        if (dataChanged) {
                            WP_Statistics_Tracker_Object = newData;
                            finish(newData);
                        } else {
                            // Data hasn't changed yet, try again
                            attempts++;
                            if (attempts < maxAttempts) {
                                setTimeout(tryUpdate, 100);
                            } else {
                                finish(WP_Statistics_Tracker_Object);
                            }
                        }
                    } else {
                        attempts++;
                        if (attempts < maxAttempts) {
                            setTimeout(tryUpdate, 100);
                        } else {
                            finish(WP_Statistics_Tracker_Object);
                        }
                    }
                } catch (error) {
                    console.error("WP Statistics: Error parsing WP_Statistics_Tracker_Object", error);
                    finish(WP_Statistics_Tracker_Object);
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

        // Handles URL changes in an SPA environment (WordPress Interactivity API, etc.)
        handleUrlChange: function () {
            const self = this;

            if (window.location.href !== this.lastUrl) {
                this.lastUrl = window.location.href;

                const now = Date.now();
                if (now - this.lastUrlChangeAt < 100) {
                    this.supersededUrlChangeGenerations[this.urlChangeGeneration] = true;
                    delete this.pendingUrlChangeGenerations[this.urlChangeGeneration];
                }
                this.lastUrlChangeAt = now;

                const generation = ++this.urlChangeGeneration;
                const pageUri = this.getPathAndQueryString(window.location.pathname, window.location.search);
                this.pendingUrlChangeGenerations[generation] = true;

                this.updateTrackerObject(function (trackerObject = WP_Statistics_Tracker_Object) {
                    delete self.pendingUrlChangeGenerations[generation];

                    if (self.supersededUrlChangeGenerations[generation]) {
                        delete self.supersededUrlChangeGenerations[generation];
                        return;
                    }

                    const pendingHit = {generation, trackerObject, pageUri};

                    if (!self.isTrackingEnabled) {
                        if (!self.pendingHit || generation > self.pendingHit.generation) {
                            self.pendingHit = pendingHit;
                        }
                        return;
                    }

                    if (self.isRestoringPendingHit) {
                        if (!self.pendingHit || generation > self.pendingHit.generation) {
                            self.pendingHit = pendingHit;
                        }

                        if (Object.keys(self.pendingUrlChangeGenerations).length === 0) {
                            self.flushPendingHit();
                        }
                        return;
                    }

                    self.checkHitRequestConditions(trackerObject, pageUri);
                });
            }
        }
    };
}