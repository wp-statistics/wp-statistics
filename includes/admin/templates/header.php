<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_STATISTICS\User;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;
use WP_Statistics\Service\Admin\ModalHandler\Modal;
use WP_Statistics\Service\Admin\PrivacyAudit\PrivacyAuditDataProvider;
use WP_Statistics\Service\Admin\Notification\NotificationFactory;
use WP_Statistics\Service\Admin\MarketingCampaign\MarketingCampaignFactory;

$userOnline              = new \WP_STATISTICS\UserOnline();
$isPremium               = LicenseHelper::isPremiumLicenseAvailable() ? true : false;
$hasUpdatedNotifications = NotificationFactory::hasUpdatedNotifications();
$displayNotifications    = WP_STATISTICS\Option::get('display_notifications') ? true : false;
$promoBanner             = MarketingCampaignFactory::getLatestMarketingCampaignByType('promo_banner');
$manageCap               = User::ExistCapability(Option::get('manage_capability', 'manage_options'));
$hasManageCap            = $manageCap && current_user_can($manageCap);

?>

    <div class="wps-adminHeader <?php echo $isPremium ? 'wps-adminHeader__premium' : '' ?>">
        <div class="wps-adminHeader__logo--container">
            <img class="wps-adminHeader__logo <?php echo $isPremium ? 'wps-adminHeader__logo--premium' : '' ?>" aria-label="VeronaLabs logo" alt="VeronaLabs logo"
                 src="<?php echo esc_url(apply_filters('wp_statistics_header_url', WP_STATISTICS_URL . 'assets/images/' . ($isPremium ? 'wp-statistics-premium.svg' : 'white-header-logo.svg'))); ?>"/>
        </div>
        <div class="wps-adminHeader__menu">
            <?php
            echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_overview_page', 'link_text' => __('Overview', 'wp-statistics'), 'icon_class' => 'overview', 'badge_count' => null], true);
            if ($userOnline::active()) {
                echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_visitors_page&tab=online', 'link_text' => __('Online Visitors', 'wp-statistics'), 'icon_class' => 'online-users', 'badge_count' => wp_statistics_useronline()], true);
            }
            if (!$isPremium && apply_filters('wp_statistics_enable_header_addons_menu', true)) {
                echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_plugins_page', 'link_text' => __('Add-ons', 'wp-statistics'), 'icon_class' => 'addons', 'badge_count' => null], true);
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
                <?php echo LicenseHelper::renderLicenseStatusLink($isPremium, $promoBanner); ?>
            <?php endif; ?>

            <?php if (Option::get('privacy_audit')) : ?>
                <?php
                $privacyAuditData   = new PrivacyAuditDataProvider();
                $privacyAuditStatus = $privacyAuditData->getComplianceStatus();
                ?>
                <a href="<?php echo esc_url(Menus::admin_url('privacy-audit')); ?>" title="<?php esc_html_e('Privacy Audit', 'wp-statistics'); ?>" class="privacy <?php echo $privacyAuditStatus['percentage_ready'] != 100 ? 'warning' : ''; ?> <?php echo Menus::in_page('privacy-audit') ? 'active' : ''; ?>"></a>
            <?php endif; ?>

            <?php if ($hasManageCap): ?>

            <a href="<?php echo esc_url(admin_url('admin.php?page=wps_settings_page')); ?>" title="<?php esc_html_e('Settings', 'wp-statistics'); ?>" class="settings <?php if (isset($_GET['page']) && $_GET['page'] === 'wps_settings_page') {
                echo 'active';
            } ?>"></a>
            <?php endif; ?>
            <?php if (apply_filters('wp_statistics_enable_help_icon', true) && $hasManageCap) { ?>
                <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/support/?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" target="_blank" title="<?php esc_html_e('Help Center', 'wp-statistics'); ?>" class="support"></a>
            <?php } ?>

            <?php if ($displayNotifications): ?>

                <a href="#" title="<?php esc_html_e('Notifications', 'wp-statistics'); ?>" class="wps-notifications js-wps-open-notification <?php echo $hasUpdatedNotifications ? esc_attr('wps-notifications--has-items') : ''; ?>"></a>
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
                        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_plugins_page', 'link_text' => __('Add-ons', 'wp-statistics'), 'icon_class' => 'addons', 'badge_count' => null], true);
                    }
                    if ($isPremium) {
                        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_pages_page', 'link_text' => __('Top Pages', 'wp-statistics'), 'icon_class' => 'top-pages', 'badge_count' => null], true);
                        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_content-analytics_page', 'link_text' => __('Content Analytics', 'wp-statistics'), 'icon_class' => 'content-analytics', 'badge_count' => null], true);
                        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_author-analytics_page', 'link_text' => __('Author Analytics', 'wp-statistics'), 'icon_class' => 'author-analytics', 'badge_count' => null], true);
                    }
                    if ($hasManageCap) {
                        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_settings_page', 'link_text' => __('Settings', 'wp-statistics'), 'icon_class' => 'settings', 'badge_count' => null], true);
                    }
                    ?>
                    <?php if ($displayNotifications): ?>
                        <div class="wps-admin-header__menu-item">
                            <a class="wps-notifications js-wps-open-notification <?php echo $hasUpdatedNotifications ? esc_attr('wps-notifications--has-items') : ''; ?>">
                                <span class="icon"></span><span><?php esc_html_e('Notifications', 'wp-statistics'); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (apply_filters('wp_statistics_enable_help_icon', true) && $hasManageCap) { ?>
                        <div>
                            <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/support/?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" target="_blank" title="<?php esc_html_e('Help Center', 'wp-statistics'); ?>" class="help">
                                <span class="icon"></span>
                                <?php esc_html_e('Help Center', 'wp-statistics'); ?>
                            </a>
                        </div>
                    <?php }

                    if (apply_filters('wp_statistics_enable_upgrade_to_bundle', true)) {
                        echo '<div class="wps-bundle">' . LicenseHelper::renderLicenseStatusLink($isPremium, $promoBanner, true) . '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php
if ($displayNotifications) {
    View::load("components/notification/side-bar", ['notifications' => NotificationFactory::getAllNotifications()]);
}
Modal::render('introduce-premium'); ?>