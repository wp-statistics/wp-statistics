<div id="poststuff">
    <div id="post-body" class="metabox-holder wps-optimizationPageFlex">
        <?php include WP_STATISTICS_DIR . 'includes/admin/templates/layout/menu-optimization.php'; ?>

        <div class="wp-list-table widefat wps-optimizationBox">
            <div class="wp-statistics-container">
                <div id="resources" class="tab-content current">
                    <?php include(WP_STATISTICS_DIR . 'includes/admin/templates/optimization/resources.php'); ?>
                </div>
                <div id="export" class="tab-content">
                    <?php include(WP_STATISTICS_DIR . 'includes/admin/templates/optimization/export.php'); ?>
                </div>
                <div id="purging" class="tab-content">
                    <?php include(WP_STATISTICS_DIR . 'includes/admin/templates/optimization/purging.php'); ?>
                </div>
                <div id="database" class="tab-content">
                    <?php include(WP_STATISTICS_DIR . 'includes/admin/templates/optimization/database.php'); ?>
                </div>
                <div id="updates" class="tab-content">
                    <?php include(WP_STATISTICS_DIR . 'includes/admin/templates/optimization/updates.php'); ?>
                </div>
                <div id="historical" class="tab-content">
                    <?php include(WP_STATISTICS_DIR . 'includes/admin/templates/optimization/historical.php'); ?>
                </div>
            </div>
        </div>
    </div>
</div>