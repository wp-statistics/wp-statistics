<div class="wps-alert wps-alert--danger wps-alert--setting">
    <h2 class="wps-alert-title"><?php echo esc_html_e('Help Us Improve WP Statistics!', 'wp-statistics') ?></h2>
    <p class="wps-alert-content"><?php echo esc_html_e('We’ve added a new Usage Tracking option to help us understand how WP Statistics is used and identify areas for improvement.
By enabling this feature, you’ll help us make the plugin better for everyone. No personal or sensitive data is collected.', 'wp-statistics') ?><a href="" target="_blank"> <?php echo esc_html_e('Learn More', 'wp-statistics') ?></a>.</p>
    <div class="wps-alert-footer">
        <a href="" target="_blank" class="wps-alert--enable-usage"><?php echo esc_html_e('Enable Usage Tracking', 'wp-statistics') ?></a>
        <a href="" class="wps-alert--dismiss"><?php echo esc_html_e('Dismiss', 'wp-statistics') ?></a>
    </div>
</div>

<div id="poststuff"  >
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
                        <div id="advanced-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/advanced.php'; ?>
                        </div>
                        <div id="privacy-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/privacy.php'; ?>
                        </div>
                        <div id="display-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/display.php'; ?>
                        </div>
                        <div id="notifications-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/notifications.php'; ?>
                        </div>
                        <div id="access-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/access-level.php'; ?>
                        </div>
                        <div id="exclusions-settings" class="tab-content">
                            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/settings/exclusions.php'; ?>
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
                </div><!-- container -->

                <input type="hidden" name="tab" id="wps_current_tab" value=""/>
            </form>
        </div>
    </div>
</div>
