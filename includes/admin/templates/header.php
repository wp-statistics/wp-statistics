<?php 
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_STATISTICS\UserOnline;
use WP_STATISTICS\Option;
use WP_Statistics\Service\PrivacyAudit\PrivacyAuditCheck;

?>

<div class="wps-adminHeader">
    <img class="wps-adminHeader__logo" src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/white-header-logo.svg'); ?>"/>
    <div class="wps-adminHeader__menu">
        <?php
        //Get Total User Online
        $total_user_online = UserOnline::get(array('fields' => 'count'));
        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_overview_page', 'link_text' => __('Overview', 'wp-statistics'), 'icon_class' => 'overview', 'badge_count' => null], true);
        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_online_page', 'link_text' => __('Online Users', 'wp-statistics'), 'icon_class' => 'online-users', 'badge_count' => $total_user_online], true);
        echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_plugins_page', 'link_text' => __('Add-Ons', 'wp-statistics'), 'icon_class' => 'addons', 'badge_count' => null], true);
        ?>
    </div>
    <div class="wps-adminHeader__side">
        <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/product/add-ons-bundle?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" target="_blank" class="wps-adminHeader__bundle">
            <?php esc_html_e('Upgrade to Bundle', 'wp-statistics'); ?>
        </a>

        <?php if (Option::get('privacy_audit')) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wps_privacy-audit_page')); ?>" title="<?php esc_html_e('Privacy Audit', 'wp-statistics'); ?>" class="privacy <?php echo PrivacyAuditCheck::complianceStatus()['percentage_ready'] != 100 ? 'warning' : ''; ?> <?php echo Menus::in_page('privacy-audit') ? 'active' : ''; ?>"></a>
        <?php endif; ?>

        <a href="<?php echo esc_url(admin_url('admin.php?page=wps_optimization_page')); ?>" title="<?php esc_html_e('Optimization', 'wp-statistics'); ?>" class="optimization <?php if (isset($_GET['page']) && $_GET['page'] === 'wps_optimization_page') {
            echo 'active';
        } ?>"></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wps_settings_page')); ?>" title="<?php esc_html_e('Settings', 'wp-statistics'); ?>" class="settings <?php if (isset($_GET['page']) && $_GET['page'] === 'wps_settings_page') {
            echo 'active';
        } ?>"></a>
        <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/support?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" target="_blank" title="<?php esc_html_e('Help Center', 'wp-statistics'); ?>" class="support"></a>

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
                echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_plugins_page', 'link_text' => __('Add-Ons', 'wp-statistics'), 'icon_class' => 'addons', 'badge_count' => null], true);
                echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_settings_page', 'link_text' => __('Settings', 'wp-statistics'), 'icon_class' => 'settings', 'badge_count' => null], true);
                echo Admin_Template::get_template('layout/partials/menu-link', ['slug' => 'wps_optimization_page', 'link_text' => __('Optimization', 'wp-statistics'), 'icon_class' => 'optimization', 'badge_count' => null], true);
                ?>
                <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/support?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" target="_blank" title="<?php esc_html_e('Help Center', 'wp-statistics'); ?>" class="help">
                    <span class="icon"></span>
                    <?php esc_html_e('Help Center', 'wp-statistics'); ?>
                </a>
                <div class="wps-bundle">
                    <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/product/add-ons-bundle?utm_source=wp-statistics&utm_medium=link&utm_campaign=header'); ?>" target="_blank" class="wps-adminHeader__bundle">
                        <?php esc_html_e('Upgrade to Bundle', 'wp-statistics'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>