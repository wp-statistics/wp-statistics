const WPStatisticsAjaxBackgroundProcess = {
    migrationNotice: null,

    /**
     * Initializes the AJAX background migration process.
     */
    init: function () {
        this.migrationNotice = jQuery('#wp-statistics-background-process-notice');
        this.bindEvents();

        if (Wp_Statistics_Background_Process_Data.status === 'progress') {
            this.startMigration();
        }
    },

    /**
     * Binds event listeners to UI elements.
     */
    bindEvents: function () {
        if (this.migrationNotice.length) {
            this.migrationNotice.find('#start-migration-btn').on('click', this.handleStartMigration);
        }
    },

    /**
     * Handles the click event of the start migration button.
     * Redirects the user to the migration initiation URL.
     */
    handleStartMigration: function (e) {
        e.preventDefault();
        window.location.href = jQuery(this).attr('href');
    },

    /**
     * Initiates the AJAX migration process.
     */
    startMigration: function (total = 0) {
        jQuery.ajax({
            url: Wp_Statistics_Background_Process_Data.ajax_url,
            method: 'POST',
            data: {
                action: 'wp_statistics_background_process',
                wps_nonce: Wp_Statistics_Background_Process_Data.rest_api_nonce,
                total,
            },
            success: function (response) {
                if (response.success) {
                    if (response.data.completed) {
                        WPStatisticsAjaxBackgroundProcess.markAsCompleted();
                    } else {
                        WPStatisticsAjaxBackgroundProcess.updateProgress(response.data.percentage);

                        total = parseInt(response.data?.total, 10) || 0;
                        WPStatisticsAjaxBackgroundProcess.startMigration(total);
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX request error:', status, error);
            }
        });
    },

    /**
     * Updates the migration progress display in the admin notice.
     * @param {number} recordsLeft - The number of remaining records.
     */
    updateProgress: function (recordsLeft) {
        if (this.migrationNotice.length) {
            this.migrationNotice.find('.remain-percentage').text(recordsLeft);
        }
    },

    /**
     * Marks the migration as completed and updates the UI.
     */
    markAsCompleted: function () {
        if (this.migrationNotice.length) {
            this.migrationNotice.closest('.notice').removeClass('notice-info').addClass('notice-success');

            this.migrationNotice.html(`
                <p><strong>WP Statistics: Migration Complete</strong></p>
                <p>All records have been successfully migrated. WP Statistics is now up-to-date.</p>
            `);
        }
    }
};

// Initialize inside jQuery ready function
jQuery(document).ready(function () {
    WPStatisticsAjaxBackgroundProcess.init();
});
