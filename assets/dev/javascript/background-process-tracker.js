const WPStatisticsAjaxBackgroundProcessTracker = {
    intervalMs: Wp_Statistics_Async_Background_Process_Data.interval || 5000,
    migrationNotice: null,
    processedElement: null,
    precentElement: null,
    timer: null,
    isActive: true,

    init: function () {
        this.migrationNotice = jQuery('#wp-statistics-async-background-process-notice');
        this.bindEvents();
    },

    bindEvents: function () {
        if (!this.migrationNotice || !this.migrationNotice.length) {
            return;
        }

        this.processedElement = this.migrationNotice.find('.processed').first();
        this.precentElement = this.migrationNotice.find('.percentage').first();

        var self = this;

        if (this.timer) {
            clearInterval(this.timer);
        }

        this.timer = setInterval(function () {
            self.updateProgress();
        }, this.intervalMs);
    },

    updateProgress: function () {
        if (!this.isActive) {
            return;
        }

        jQuery.ajax({
            url: Wp_Statistics_Async_Background_Process_Data.ajax_url,
            method: 'GET',
            data: {
                action: 'wp_statistics_async_background_process',
                wps_nonce: Wp_Statistics_Async_Background_Process_Data.rest_api_nonce,
            },
            success: function (response) {
                if (response.data.completed) {
                        WPStatisticsAjaxBackgroundProcessTracker.markAsCompleted();
                    } else {
                        WPStatisticsAjaxBackgroundProcessTracker.updatePercent(response.data.percentage);
                        WPStatisticsAjaxBackgroundProcessTracker.updateProcessed(response.data.percentage);
                    }
            },
            error: function (xhr, status, error) {
                console.error('AJAX request error:', status, error);
            }
        });
    },

    markAsCompleted: function () {
        if (this.migrationNotice.length) {
            this.migrationNotice.closest('.notice').removeClass('notice-info').addClass('notice-success');

            this.migrationNotice.html(`
                <p><strong>WP Statistics: Background process completed successfully.</strong></p>
            `);
        }

        this.isActive = false;
    },

    updatePercent: function (percent) {
        if (this.precentElement && this.precentElement.length) {
            this.precentElement.text(percent + '%');
        }
    },

    updateProcessed: function (processed) {
        if (this.processedElement && this.processedElement.length) {
            this.processedElement.text(processed);
        }
    }
};

// Initialize inside jQuery ready function
jQuery(document).ready(function () {
    WPStatisticsAjaxBackgroundProcessTracker.init();
});
