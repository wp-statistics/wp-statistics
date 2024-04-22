<?php require_once WP_STATISTICS_DIR . "/includes/admin/templates/header.php"; ?>
<div class="wps-wrap__top">
    <h2><?php esc_html_e('Add-Ons', 'wp-statistics'); ?></h2>
    <p><?php esc_html_e('The Add-Ons add more functionality to WP Statistics and unlock the premium features.', 'wp-statistics'); ?></p>
</div>
<div class="wps-wrap__main">
    <div class="wp-header-end"></div>
    <div class="wrap wps-wrap">
        <?php include(WP_STATISTICS_DIR . "includes/admin/templates/add-ons.php"); ?>
    </div>
</div>
