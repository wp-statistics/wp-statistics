/**
 * WP Statistics Engagement Tracker
 *
 * Tracks user engagement time using a simple running total approach (like Plausible).
 * Monitors page visibility and focus to only count active time.
 */
if (!window.WpStatisticsEngagementTracker) {
    window.WpStatisticsEngagementTracker = {
        // State
        isVisible: document.visibilityState === 'visible',
        isFocused: document.hasFocus(),
        isEngaged: false,

        // Timing (running total approach like Plausible)
        engagementStart: 0,      // When current engagement session started
        engagementTotal: 0,      // Accumulated engagement time
        lastActivityTime: Date.now(),

        // Configuration
        activityTimeout: 30 * 1000, // 30 seconds of inactivity = not engaged

        /**
         * Initialize the engagement tracker
         * @param {Object} options Configuration options
         */
        init: function(options = {}) {
            if (options.activityTimeout) this.activityTimeout = options.activityTimeout;

            // Set initial state
            this.isVisible = document.visibilityState === 'visible';
            this.isFocused = document.hasFocus();
            this.lastActivityTime = Date.now();

            // Bind event handlers
            this.bindEvents();

            // Check initial engagement state
            this.updateEngagementState();

            return this;
        },

        /**
         * Bind all event listeners
         */
        bindEvents: function() {
            const self = this;

            // Visibility change (tab switch, minimize)
            document.addEventListener('visibilitychange', function() {
                self.isVisible = document.visibilityState === 'visible';
                self.updateEngagementState();
            });

            // Focus/blur (window focus)
            window.addEventListener('focus', function() {
                self.isFocused = true;
                self.updateEngagementState();
            });

            window.addEventListener('blur', function() {
                self.isFocused = false;
                self.updateEngagementState();
            });

            // Page hide (navigation, tab close) - stop engagement tracking
            window.addEventListener('pagehide', function() {
                self.stopEngagement();
            });

            // User activity events
            const activityEvents = ['click', 'scroll', 'keypress', 'mousemove', 'touchstart'];
            activityEvents.forEach(function(eventType) {
                // Use passive listeners for performance
                document.addEventListener(eventType, function() {
                    self.recordActivity();
                }, { passive: true });
            });
        },

        /**
         * Record user activity
         */
        recordActivity: function() {
            this.lastActivityTime = Date.now();

            // If we weren't engaged due to inactivity, check if we should re-engage
            if (!this.isEngaged && this.isVisible && this.isFocused) {
                this.updateEngagementState();
            }
        },

        /**
         * Check if user is currently active (not idle)
         */
        isUserActive: function() {
            return (Date.now() - this.lastActivityTime) < this.activityTimeout;
        },

        /**
         * Update engagement state based on visibility, focus, and activity
         */
        updateEngagementState: function() {
            const shouldBeEngaged = this.isVisible && this.isFocused && this.isUserActive();

            if (shouldBeEngaged && !this.isEngaged) {
                this.startEngagement();
            } else if (!shouldBeEngaged && this.isEngaged) {
                this.stopEngagement();
            }
        },

        /**
         * Start tracking engagement time
         */
        startEngagement: function() {
            if (this.isEngaged) return;
            this.isEngaged = true;
            this.engagementStart = Date.now();
        },

        /**
         * Stop tracking engagement time
         */
        stopEngagement: function() {
            if (!this.isEngaged) return;

            // Add session time to total
            if (this.engagementStart > 0) {
                this.engagementTotal += Date.now() - this.engagementStart;
            }

            this.isEngaged = false;
            this.engagementStart = 0;
        },

        /**
         * Get total engagement time including current session (in ms)
         */
        getTotalEngagementTime: function() {
            if (this.isEngaged && this.engagementStart > 0) {
                return this.engagementTotal + (Date.now() - this.engagementStart);
            }
            return this.engagementTotal;
        },

        /**
         * Reset engagement tracking (for SPA navigation)
         */
        reset: function() {
            this.stopEngagement();
            this.engagementTotal = 0;
            this.lastActivityTime = Date.now();
            this.updateEngagementState();
        },

        /**
         * Reset engagement time after flush (keeps tracking active)
         * Called after data is sent to start fresh accumulation
         */
        resetAfterFlush: function() {
            // Reset total to 0
            this.engagementTotal = 0;

            // If currently engaged, restart the timer from now
            if (this.isEngaged) {
                this.engagementStart = Date.now();
            }
        }
    };
}
