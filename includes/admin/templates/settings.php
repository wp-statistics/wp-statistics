<div id="poststuff">
    <div id="post-body" class="metabox-holder wps-settingsPageFlex">
        <?php include WP_STATISTICS_DIR . 'includes/admin/templates/layout/menu-settings.php'; ?>

        <div class="wp-list-table widefat wps-settingsBox">
            <form id="wp-statistics-settings-form" method="post">
                <?php wp_nonce_field('update-options', 'wp-statistics-nonce'); ?>

                <div class="wp-statistics-container">
                    <?php if ($wps_admin) { ?>
                        <div id="general-settings" class="tab-content current">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/general.php'; ?>
                        </div>
                        <div id="ip-configuration-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/visitor-ip.php'; ?>
                        </div>
                        <div id="privacy-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/privacy.php'; ?>
                        </div>
                        <div id="notifications-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/notifications.php'; ?>
                        </div>
                        <div id="overview-display-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/overview-display.php'; ?>
                        </div>
                        <div id="access-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/access-level.php'; ?>
                        </div>
                        <div id="exclusions-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/exclusions.php'; ?>
                        </div>
                        <div id="externals-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/externals.php'; ?>
                        </div>
                        <div id="maintenance-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/maintenance.php'; ?>
                        </div>
                        <div id="reset-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/reset.php'; ?>
                        </div>
                        <div id="data-plus-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/add-ons/data-plus.php'; ?>
                        </div>
                        <div id="realtime-stats-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/add-ons/realtime-stats.php'; ?>
                        </div>
                        <div id="customization-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/add-ons/customization.php'; ?>
                        </div>
                        <div id="advanced-reporting-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/add-ons/advanced-reporting.php'; ?>
                        </div>
                        <div id="mini-chart-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/add-ons/mini-chart.php'; ?>
                        </div>
                        <div id="rest-api-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/add-ons/rest-api.php'; ?>
                        </div>
                        <div id="widgets-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/add-ons/widgets.php'; ?>
                        </div>
                    <?php } ?>
                    <div id="about" class="tab-content">
                        <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/about.php'; ?>
                    </div>
                </div><!-- container -->

                <input type="hidden" name="tab" id="wps_current_tab" value=""/>
            </form>
        </div>
    </div>
</div>
