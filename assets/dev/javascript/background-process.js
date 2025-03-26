const WPStatisticsBackgroundMigration = {
    migrationNotice: null,

    /**
     * Initializes the migration process.
     */
    init: function () {
        this.migrationNotice = jQuery('#wp-statistics-background-process-notice');
        this.bindEvents();

        if (Wp_Statistics_Background_Process_Data.status === 'progress') {
            this.startMigration();
        }
    },

    /**
     * Binds event listeners.
     */
    bindEvents: function () {
        if (this.migrationNotice.length) {
            this.migrationNotice.find('#start-migration-btn').on('click', this.handleStartMigration);
        }
    },

    /**
     * Handles the start migration button click.
     * Redirects the user to the migration process URL.
     */
    handleStartMigration: function (e) {
        e.preventDefault();
        window.location.href = jQuery(this).attr('href');
    },

    /**
     * Triggers the AJAX-based migration process.
     */
    startMigration: function () {
        jQuery.ajax({
            url: Wp_Statistics_Background_Process_Data.ajax_url,
            method: 'POST',
            data: {
                action: 'wp_statistics_background_process',
                wps_nonce: Wp_Statistics_Background_Process_Data.rest_api_nonce,
            },
            success: function (response) {
                if (response.success) {
                    if (response.data.completed) {
                        WPStatisticsBackgroundMigration.markAsCompleted();
                    } else {
                        WPStatisticsBackgroundMigration.updateProgress(response.data.remains);
                        setTimeout(WPStatisticsBackgroundMigration.startMigration, 5000);
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX request error:', status, error);
            }
        });
    },

    /**
     * Updates the migration progress within the notice.
     * @param {int} recordsLeft - Number of remaining records.
     */
    updateProgress: function (recordsLeft) {
        if (this.migrationNotice.length) {
            this.migrationNotice.find('.remain-number').text(recordsLeft);
        }
    },

    /**
     * Updates the UI once the migration is completed.
     */
    markAsCompleted: function () {
        if (this.migrationNotice.length) {
            this.migrationNotice.html(`
                <p><strong>WP Statistics: Process Complete</strong></p>
                <p>All records have been successfully migrated. You may now continue using WP Statistics.</p>
            `);
        }
    }
};

// Initialize inside jQuery ready function
jQuery(document).ready(function ($) {
    WPStatisticsBackgroundMigration.init();
});
