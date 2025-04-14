<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;
use WP_Statistics\Service\Admin\ModalHandler\Modal;
use WP_Statistics\Service\Admin\PrivacyAudit\PrivacyAuditDataProvider;
use WP_Statistics\Service\Admin\Notification\NotificationFactory;

$userOnline              = new \WP_STATISTICS\UserOnline();
$isPremium               = LicenseHelper::isPremiumLicenseAvailable() ? true : false;
$hasUpdatedNotifications = NotificationFactory::hasUpdatedNotifications();
$displayNotifications    = WP_STATISTICS\Option::get('display_notifications') ? true : false;
?>

    <div class="wps-adminHeader <?php echo $isPremium ? 'wps-adminHeader__premium' : '' ?>">
        <div class="wps-adminHeader__logo--container">
            <?php if ($isPremium): ?>
                <img class="wps-adminHeader__logo wps-adminHeader__logo--premium" src="<?php echo esc_url(apply_filters('wp_statistics_header_url', WP_STATISTICS_URL . 'assets/images/wp-statistics-premium.svg')); ?>"/>
            <?php else: ?>
                <img class="wps-adminHeader__logo" src="<?php echo esc_url(apply_filters('wp_statistics_header_url', WP_STATISTICS_URL . 'assets/images/white-header-logo.svg')); ?>"/>

            <?php endif; ?>
        </div>
        <div class="wps-adminHeader__menu">
            <?php
            echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_overview_page', 'link_text' => __('Overview', 'wp-statistics'), 'icon_class' => 'overview', 'badge_count' => null], true);
            if ($userOnline::active()) {
                echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_visitors_page&tab=online', 'link_text' => __('Online Visitors', 'wp-statistics'), 'icon_class' => 'online-users', 'badge_count' => wp_statistics_useronline()], true);
            }
            if (!$isPremium && apply_filters('wp_statistics_enable_header_addons_menu', true)) {
                echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_plugins_page', 'link_text' => __('Add-Ons', 'wp-statistics'), 'icon_class' => 'addons', 'badge_count' => null], true);
            }
            if ($isPremium) {
                echo Admin_Template::get_template('layout/partials/menu-link', [
                    'slug'        => '',
                    'link_text'   => __('Quick Access', 'wp-statistics'),
                    'icon_class'  => 'quick-access',
                    'badge_count' => null,
                    'sub_menu'    => [
                        [
                            'slug'       => 'wps_pages_page',
                            'link_text'  => __('Top Pages', 'wp-statistics'),
                            'icon_class' => 'top-pages'
                        ],
                        [
                            'slug'       => 'wps_content-analytics_page',
                            'link_text'  => __('Content Analytics', 'wp-statistics'),
                            'icon_class' => 'content-analytics'
                        ],
                        [
                            'slug'       => 'wps_author-analytics_page',
                            'link_text'  => __('Author Analytics', 'wp-statistics'),
                            'icon_class' => 'author-analytics'
                        ]
                    ]
                ], true);
            }

            ?>
        </div>
        <div class="wps-adminHeader__side">
            <?php if (apply_filters('wp_statistics_enable_upgrade_to_bundle', true)) : ?>
                <?php if (!$isPremium && !LicenseHelper::isValidLicenseAvailable()) : ?>
                    <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" target="_blank" class="wps-license-status wps-license-status--free">
                        <?php esc_html_e('Upgrade To Premium', 'wp-statistics'); ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" class="wps-license-status wps-license-status--valid">
                        <span><?php esc_html_e(sprintf('License: %s/%s', count(PluginHelper::getLicensedPlugins()), count(PluginHelper::$plugins)), 'wp-statistics') ?></span> <span><?php esc_html_e('Upgrade', 'wp-statistics'); ?></span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (Option::get('privacy_audit')) : ?>
                <?php
                $privacyAuditData   = new PrivacyAuditDataProvider();
                $privacyAuditStatus = $privacyAuditData->getComplianceStatus();
                ?>
                <a href="<?php echo esc_url(Menus::admin_url('privacy-audit')); ?>" title="<?php esc_html_e('Privacy Audit', 'wp-statistics'); ?>" class="privacy <?php echo $privacyAuditStatus['percentage_ready'] != 100 ? 'warning' : ''; ?> <?php echo Menus::in_page('privacy-audit') ? 'active' : ''; ?>"></a>
            <?php endif; ?>

            <a href="<?php echo esc_url(admin_url('admin.php?page=wps_settings_page')); ?>" title="<?php esc_html_e('Settings', 'wp-statistics'); ?>" class="settings <?php if (isset($_GET['page']) && $_GET['page'] === 'wps_settings_page') {
                echo 'active';
            } ?>"></a>
            <?php if (apply_filters('wp_statistics_enable_help_icon', true)) { ?>
                <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/support?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" target="_blank" title="<?php esc_html_e('Help Center', 'wp-statistics'); ?>" class="support"></a>
            <?php } ?>
            <?php if ($displayNotifications): ?>

                <a title="<?php esc_html_e('Notifications', 'wp-statistics'); ?>" class="wps-notifications js-wps-open-notification <?php echo $hasUpdatedNotifications ? esc_attr('wps-notifications--has-items') : ''; ?>"></a>
            <?php endif; ?>
            <div class="wps-adminHeader__mobileMenu">
                <input type="checkbox" id="wps-menu-toggle" class="hamburger-menu">
                <label for="wps-menu-toggle" class="hamburger-menu-container">
                    <div class="hamburger-menu-bar">
                        <div class="menu-bar"></div>
                        <div class="menu-bar"></div>
                        <div class="menu-bar"></div>
                    </div>
                    <span><?php esc_html_e('Menu', 'wp-statistics'); ?></span>
                </label>
                <div class="wps-mobileMenuContent">
                    <?php
                    if (!$isPremium && apply_filters('wp_statistics_enable_header_addons_menu', true)) {
                        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_plugins_page', 'link_text' => __('Add-Ons', 'wp-statistics'), 'icon_class' => 'addons', 'badge_count' => null], true);
                    }
                    if ($isPremium) {
                        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_pages_page', 'link_text' => __('Top Pages', 'wp-statistics'), 'icon_class' => 'top-pages', 'badge_count' => null], true);
                        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_content-analytics_page', 'link_text' => __('Content Analytics', 'wp-statistics'), 'icon_class' => 'content-analytics', 'badge_count' => null], true);
                        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_author-analytics_page', 'link_text' => __('Author Analytics', 'wp-statistics'), 'icon_class' => 'author-analytics', 'badge_count' => null], true);
                    }
                    echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_settings_page', 'link_text' => __('Settings', 'wp-statistics'), 'icon_class' => 'settings', 'badge_count' => null], true);
                    ?>
                    <?php if ($displayNotifications): ?>
                        <div class="wps-admin-header__menu-item">
                            <a class="wps-notifications js-wps-open-notification <?php echo $hasUpdatedNotifications ? esc_attr('wps-notifications--has-items') : ''; ?>">
                                <span class="icon"></span><span><?php esc_html_e('Notifications', 'wp-statistics'); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (apply_filters('wp_statistics_enable_help_icon', true)) { ?>
                        <div>
                            <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/support?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" target="_blank" title="<?php esc_html_e('Help Center', 'wp-statistics'); ?>" class="help">
                                <span class="icon"></span>
                                <?php esc_html_e('Help Center', 'wp-statistics'); ?>
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (apply_filters('wp_statistics_enable_upgrade_to_bundle', true)) : ?>
                        <div class="wps-bundle">
                            <?php if (!$isPremium && !LicenseHelper::isValidLicenseAvailable()) : ?>
                                <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" target="_blank" class="wps-license-status wps-license-status--free">
                                    <?php esc_html_e('Upgrade To Premium', 'wp-statistics'); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" class="wps-license-status wps-license-status--valid">
                                    <span><?php esc_html_e(sprintf('License: %s/%s', count(PluginHelper::getLicensedPlugins()), count(PluginHelper::$plugins)), 'wp-statistics'); ?></span> <span><?php esc_html_e('Upgrade', 'wp-statistics'); ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php
if ($displayNotifications) {
    View::load("components/notification/side-bar", ['notifications' => NotificationFactory::getAllNotifications()]);
}
?>
<?php Modal::render('introduce-premium'); ?>