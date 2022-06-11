<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
        <div class="wp-list-table widefat widefat">
            <form id="wp-statistics-settings-form" method="post">
                <?php wp_nonce_field('update-options', 'wp-statistics-nonce'); ?>

                <div class="wp-statistics-container">
                    <?php if ($wps_admin) { ?>
                        <div id="general-settings" class="tab-content current">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/general.php'; ?>
                        </div>
                        <div id="visitor-ip-settings" class="tab-content">
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
                    <?php } ?>
                    <div id="about" class="tab-content">
                        <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/about.php'; ?>
                    </div>
                </div><!-- container -->

                <input type="hidden" name="tab" id="wps_current_tab" value=""/>
            </form>
        </div>
        <?php include WP_STATISTICS_DIR . 'includes/admin/templates/postbox.php'; ?>
    </div>
</div>
