<?php

use WP_Statistics\Service\Admin\NoticeHandler\Notice;

if (!WP_STATISTICS\Option::get('enable_usage_tracking')) {
    $notice = [
        'title'   => __('Help Us Improve WP Statistics!', 'wp-statistics'),
        'content' => __('We’ve added a new Usage Tracking option to help us understand how WP Statistics is used and identify areas for improvement. By <a href="#" class="js-wps-notice-data" data-option="enable_usage_tracking" data-value="true">enabling</a> this feature, you’ll help us make the plugin better for everyone. No personal or sensitive data is collected.', 'wp-statistics'),
        'links'   => [
            'learn_more'      => [
                'text' => __('Learn More', 'wp-statistics'),
                'url'  => '#',
            ],
            'enable_tracking' => [
                'text'  => __('Enable Usage Tracking', 'wp-statistics'),
                'url'   => '#',
                'class' => 'wps-notice-action__ajax-handler notice--enable-usage',
            ]
        ]
    ];
    Notice::renderNotice($notice, 'enable_usage_tracking', 'setting', 'action');
}
?>
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
