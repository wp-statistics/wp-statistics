<?php
/**
 * WP Statistics Admin Header (v15 simplified version)
 *
 * Used by legacy PHP pages: Privacy Audit, Help Center, Add-ons
 */

use WP_Statistics\Components\Option;

// TODO: Check premium status from wp-statistics-premium plugin
$isPremium = function_exists('is_plugin_active') && is_plugin_active('wp-statistics-premium/wp-statistics-premium.php');
$logoUrl   = WP_STATISTICS_URL . 'assets/images/' . ($isPremium ? 'wp-statistics-premium.svg' : 'white-header-logo.svg');

?>
<div class="wps-adminHeader <?php echo $isPremium ? 'wps-adminHeader__premium' : '' ?>">
    <div class="wps-adminHeader__logo--container">
        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-statistics')); ?>">
            <img class="wps-adminHeader__logo <?php echo $isPremium ? 'wps-adminHeader__logo--premium' : '' ?>"
                 alt="WP Statistics"
                 src="<?php echo esc_url($logoUrl); ?>"/>
        </a>
    </div>
    <div class="wps-adminHeader__menu">
        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-statistics#/overview')); ?>" class="wps-adminHeader__menu-link">
            <span class="wps-adminHeader__menu-icon wps-adminHeader__menu-icon--overview"></span>
            <?php esc_html_e('Dashboard', 'wp-statistics'); ?>
        </a>
        <?php if (!$isPremium) : ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-statistics#/premium')); ?>" class="wps-adminHeader__menu-link">
            <span class="wps-adminHeader__menu-icon wps-adminHeader__menu-icon--addons"></span>
            <?php esc_html_e('Premium', 'wp-statistics'); ?>
        </a>
        <?php endif; ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-statistics#/settings/general')); ?>" class="wps-adminHeader__menu-link">
            <span class="wps-adminHeader__menu-icon wps-adminHeader__menu-icon--settings"></span>
            <?php esc_html_e('Settings', 'wp-statistics'); ?>
        </a>
    </div>
</div>
