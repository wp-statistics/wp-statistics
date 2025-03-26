jQuery(document).ready(function ($) {
    handleMigrationNotice();

    function handleMigrationNotice() {
        let migrationNotice = jQuery('#wp-statistics-background-process-notice');

        if (migrationNotice.length) {
            migrationNotice.find('#start-migration-btn').on('click', function (e) {
                window.location.href = jQuery(this).attr('href');
            });
        }

        const status = Wp_Statistics_Background_Process_Data.status;

        if (status === 'progress') {
            migrateData();
        }
    }

    function migrateData() {
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
                        updateToDone();
                    } else {
                        updateProgress(response.data.remains)
                        setTimeout(migrateData, 5000);
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX request error:', status, error);
            }
        });
    }

    function updateProgress(recordsLeft) {
        let migrationNotice = jQuery('#wp-statistics-background-process-notice');

        if (migrationNotice.length) {
            migrationNotice.find('.remain-number').text(recordsLeft);
        }
    }

    function updateToDone() {
        let migrationNotice = jQuery('#wp-statistics-background-process-notice');

        if (migrationNotice.length) {
            migrationNotice.html(`
                <p><strong>WP Statistics: Process Complete</strong></p>
                <p>All records have been successfully migrated. You may now continue using WP Statistics.</p>
            `);
        }
    }
});
