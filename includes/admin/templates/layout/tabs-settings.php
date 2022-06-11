<ul class="tabs">
    <?php if ($wps_admin) { ?>
        <li class="tab-link current" data-tab="general-settings"><?php _e('General', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="visitor-ip-settings"><?php _e('Visitor IP', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="privacy-settings"><?php _e('Privacy', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="notifications-settings"><?php _e('Notifications', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="overview-display-settings"><?php _e('Dashboard', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="access-settings"><?php _e('Roles', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="exclusions-settings"><?php _e('Exclusions', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="externals-settings"><?php _e('Externals', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="maintenance-settings"><?php _e('Maintenance', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="reset-settings"><?php _e('Reset', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="about"><?php _e('About', 'wp-statistics'); ?></li>
    <?php } ?>
</ul>
