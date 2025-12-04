/**
 * WP Statistics Batch Queue
 *
 * Handles batching and reliable delivery of analytics events.
 * Uses sendBeacon for exit events to ensure data is delivered
 * even when the page is being unloaded.
 *
 * Features:
 * - Batches multiple events together to reduce HTTP requests
 * - Automatic flush on page exit (visibilitychange, pagehide)
 * - Periodic flush as backup
 * - Falls back to XHR/fetch if sendBeacon fails
 * - Respects ad-blocker bypass settings
 */
if (!window.WpStatisticsBatchQueue) {
    window.WpStatisticsBatchQueue = {
        // Queue storage
        queue: [],

        // Configuration
        maxQueueSize: 10,           // Flush when queue reaches this size
        flushInterval: 30000,       // Flush every 30 seconds
        maxPayloadSize: 63000,      // sendBeacon limit is ~64KB, keep some margin

        // State
        flushIntervalId: null,
        isInitialized: false,
        lastFlushTime: 0,
        lastSentEngagementTime: 0, // Track last sent engagement to avoid duplicate sends
        minFlushInterval: 3000, // Minimum 3 seconds between flushes (like Plausible)

        // Endpoints
        batchEndpoint: null,
        ajaxUrl: null,
        bypassAdBlockers: false,

        // Session data getter (returns session_id and engagement_time)
        getSessionData: null,

        /**
         * Initialize the batch queue
         * @param {Object} options Configuration options
         */
        init: function(options = {}) {
            if (this.isInitialized) return this;

            // Apply options
            if (options.maxQueueSize) this.maxQueueSize = options.maxQueueSize;
            if (options.flushInterval) this.flushInterval = options.flushInterval;
            if (options.batchEndpoint) this.batchEndpoint = options.batchEndpoint;
            if (options.ajaxUrl) this.ajaxUrl = options.ajaxUrl;
            if (typeof options.bypassAdBlockers !== 'undefined') {
                this.bypassAdBlockers = options.bypassAdBlockers;
            }
            if (options.getSessionData) {
                this.getSessionData = options.getSessionData;
            }

            // Bind exit events for reliable data delivery (only send on page exit)
            this.bindExitEvents();

            this.isInitialized = true;

            return this;
        },

        /**
         * Add an event to the queue
         * @param {string} eventType Type of event (e.g., 'engagement', 'pageview', 'online')
         * @param {Object} data Event data
         */
        add: function(eventType, data) {
            const event = {
                type: eventType,
                data: data,
                timestamp: Date.now(),
                url: window.location.href
            };

            this.queue.push(event);

            // Flush if queue is full
            if (this.queue.length >= this.maxQueueSize) {
                this.flush('queue_full');
            }
        },

        /**
         * Flush the queue (send all batched events)
         * @param {string} reason Why the flush is happening
         */
        flush: function(reason = 'manual') {
            // Get engagement data
            const sessionData = this.getSessionData ? this.getSessionData() : {};
            const currentEngagementTime = sessionData.engagement_time || 0;
            const hasEvents = this.queue.length > 0;
            const now = Date.now();

            // Skip if no engagement time and no events (nothing to send)
            if (currentEngagementTime === 0 && !hasEvents) return;

            // Skip if flushed too recently (prevent rapid-fire on quick tab switches)
            // But always send if there are events in queue
            const timeSinceLastFlush = now - this.lastFlushTime;
            if (timeSinceLastFlush < this.minFlushInterval && !hasEvents) {
                return;
            }

            const events = this.queue.slice(); // Copy queue
            this.queue = []; // Clear queue immediately

            // Build payload - session is identified server-side by IP hash
            const payload = {
                engagement_time: currentEngagementTime,
                events: events
            };

            // Update last sent engagement time
            this.lastSentEngagementTime = currentEngagementTime;
            this.lastFlushTime = now;

            // Reset engagement time after flush (start fresh accumulation)
            if (window.WpStatisticsEngagementTracker && window.WpStatisticsEngagementTracker.resetAfterFlush) {
                window.WpStatisticsEngagementTracker.resetAfterFlush();
            }

            // Always use sendBeacon (simpler, more reliable)
            this.sendBeacon(payload);
        },

        /**
         * Send data using sendBeacon
         */
        sendBeacon: function(payload) {
            const url = this.getEndpointUrl();
            const data = JSON.stringify(payload);

            // Check payload size
            if (data.length > this.maxPayloadSize) {
                this.splitAndSend(payload);
                return;
            }

            // Try sendBeacon first
            if (navigator.sendBeacon) {
                let body;
                if (this.bypassAdBlockers) {
                    // For AJAX, use FormData
                    body = new FormData();
                    body.append('action', 'wp_statistics_batch');
                    body.append('batch_data', data);
                } else {
                    // For REST API, use JSON blob
                    body = new Blob([data], { type: 'application/json' });
                }

                const success = navigator.sendBeacon(url, body);

                if (success) {
                    return;
                }
            }

            // Fallback to fetch with keepalive
            this.sendFetchKeepalive(url, data);
        },

        /**
         * Send data using fetch with keepalive (fallback for sendBeacon)
         */
        sendFetchKeepalive: function(url, jsonData) {
            try {
                let fetchOptions;

                if (this.bypassAdBlockers) {
                    // For AJAX, use FormData
                    const formData = new FormData();
                    formData.append('action', 'wp_statistics_batch');
                    formData.append('batch_data', jsonData);
                    fetchOptions = {
                        method: 'POST',
                        body: formData,
                        keepalive: true
                    };
                } else {
                    // For REST API, use JSON
                    fetchOptions = {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: jsonData,
                        keepalive: true
                    };
                }

                fetch(url, fetchOptions).catch(function(error) {
                });
            } catch (error) {
            }
        },

        /**
         * Split large payload and send in chunks
         */
        splitAndSend: function(payload) {
            const events = payload.events || [];
            const chunkSize = Math.ceil(events.length / 2);

            for (let i = 0; i < events.length; i += chunkSize) {
                const chunk = events.slice(i, i + chunkSize);
                const isFirstChunk = i === 0;
                const chunkPayload = {
                    // Only send engagement_time with first chunk to avoid double-counting
                    engagement_time: isFirstChunk ? payload.engagement_time : 0,
                    events: chunk
                };

                this.sendBeacon(chunkPayload);
            }
        },

        /**
         * Get the appropriate endpoint URL
         */
        getEndpointUrl: function() {
            if (this.bypassAdBlockers && this.ajaxUrl) {
                // Use AJAX endpoint to bypass ad blockers
                // Action is added in the request body, not URL
                return this.ajaxUrl;
            }
            return this.batchEndpoint;
        },

        /**
         * Start periodic flush interval
         */
        startPeriodicFlush: function() {
            const self = this;

            // Clear any existing interval
            this.stopPeriodicFlush();

            this.flushIntervalId = setInterval(function() {
                if (self.queue.length > 0) {
                    self.flush('periodic');
                }
            }, this.flushInterval);
        },

        /**
         * Stop periodic flush interval
         */
        stopPeriodicFlush: function() {
            if (this.flushIntervalId) {
                clearInterval(this.flushIntervalId);
                this.flushIntervalId = null;
            }
        },

        /**
         * Bind exit events for reliable data delivery
         * Uses time threshold instead of flags (simpler, like Plausible)
         */
        bindExitEvents: function() {
            const self = this;

            // Visibility change - flush when page becomes hidden
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'hidden') {
                    self.flush('visibility_hidden');
                }
            });

            // Page hide - most reliable exit event (navigation, tab close)
            window.addEventListener('pagehide', function() {
                self.flush('pagehide');
            });
        },

        /**
         * Get queue statistics
         */
        getStats: function() {
            return {
                queueSize: this.queue.length,
                lastFlushTime: this.lastFlushTime,
                timeSinceLastFlush: Date.now() - this.lastFlushTime
            };
        },

        /**
         * Clear the queue without sending
         */
        clear: function() {
            this.queue = [];
            this.lastSentEngagementTime = 0;
        },

        /**
         * Destroy the batch queue (cleanup)
         */
        destroy: function() {
            this.flush('destroy');
            this.stopPeriodicFlush();
            this.isInitialized = false;
        }
    };
}
