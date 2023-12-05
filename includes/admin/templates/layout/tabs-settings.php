<ul class="tabs">
    <?php if ($wps_admin) { ?>
        <li class="tab-link current" data-tab="general-settings"><?php _e('Basic Tracking Settings', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="ip-configuration-settings"><?php _e('IP Detection Settings', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="privacy-settings"><?php _e('User Data Protection', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="notifications-settings"><?php _e('Admin Notifications', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="overview-display-settings"><?php _e('Dashboard Widgets', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="access-settings"><?php _e('Access Control', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="exclusions-settings"><?php _e('Filtering & Exceptions', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="externals-settings"><?php _e('Externals', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="maintenance-settings"><?php _e('Maintenance', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="reset-settings"><?php _e('Restore Default Settings', 'wp-statistics'); ?></li>
        <li class="tab-link" data-tab="about"><?php _e('WP Statistics - Overview', 'wp-statistics'); ?></li>
    <?php } ?>
</ul>
