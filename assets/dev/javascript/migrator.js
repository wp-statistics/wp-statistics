jQuery(document).ready(function ($) {
    migrateData();

    function migrateData() {
        jQuery.ajax({
            url: Wp_Statistics_Migrator_Data.ajax_url,
            method: 'POST',
            data: {
                action: 'wp_statistics_data_migrate',
                wps_nonce: Wp_Statistics_Migrator_Data.rest_api_nonce,
            },
            success: function (response) {
                if (response.success) {
                    if (response.data.completed) {
                        console.info("Migration completed successfully.");
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
        let migrationNotice = jQuery('#wp-statistics-migration-notice');

        if (migrationNotice.length) {
            migrationNotice.find('.remain-number').text(recordsLeft);
        }
    }
});
