<?php

//Check Form Security nonce
$wps_nonce_valid = false;
if ( array_key_exists( 'wp-statistics-nonce', $_POST ) ) {
	if ( wp_verify_nonce( $_POST['wp-statistics-nonce'], 'update-options' ) ) {
		$wps_nonce_valid = true;
	}
}

//Check User Access To Save Setting
$wps_admin = false;
if ( current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) ) ) ) {
	$wps_admin = true;
}

if ( $wps_admin === false ) {
	$wps_admin = 0;
}

?>
    <div class="wrap wps-wrap wp-statistics-settings">
		<?php WP_Statistics_Admin_Pages::show_page_title( __( 'Settings', 'wp-statistics' ) ); ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div class="wp-list-table widefat widefat">
                    <form id="wp-statistics-settings-form" method="post">
						<?php wp_nonce_field( 'update-options', 'wp-statistics-nonce' ); ?>
                        <div class="wp-statistics-container">
                            <ul class="tabs">
								<?php if ( $wps_admin ) { ?>
                                    <li class="tab-link current" data-tab="general-settings"><?php _e( 'General', 'wp-statistics' ); ?></li>
                                    <li class="tab-link" data-tab="visitor-ip-settings"><?php _e( 'Visitor IP', 'wp-statistics' ); ?></li>
                                    <li class="tab-link" data-tab="privacy-settings"><?php _e( 'Privacy', 'wp-statistics' ); ?></li>
                                    <li class="tab-link" data-tab="notifications-settings"><?php _e( 'Notifications', 'wp-statistics' ); ?></li>
                                    <li class="tab-link" data-tab="overview-display-settings"><?php _e( 'Dashboard', 'wp-statistics' ); ?></li>
                                    <li class="tab-link" data-tab="access-settings"><?php _e( 'Access Levels', 'wp-statistics' ); ?></li>
                                    <li class="tab-link" data-tab="exclusions-settings"><?php _e( 'Exclusions', 'wp-statistics' ); ?></li>
                                    <li class="tab-link" data-tab="externals-settings"><?php _e( 'Externals', 'wp-statistics' ); ?></li>
                                    <li class="tab-link" data-tab="maintenance-settings"><?php _e( 'Maintenance', 'wp-statistics' ); ?></li>
                                    <li class="tab-link" data-tab="removal-settings"><?php _e( 'Removal', 'wp-statistics' ); ?></li>
								<?php } ?>
                                <li class="tab-link" data-tab="about"><?php _e( 'About', 'wp-statistics' ); ?></li>
                            </ul>

							<?php if ( $wps_admin ) { ?>
                                <div id="general-settings" class="tab-content current">
									<?php include( WP_Statistics::$reg['plugin-dir'] . 'includes/settings/tabs/wps-general.php' ); ?>
                                </div>
                                <div id="visitor-ip-settings" class="tab-content">
									<?php include( WP_Statistics::$reg['plugin-dir'] . 'includes/settings/tabs/wps-visitor-ip.php' ); ?>
                                </div>
                                <div id="privacy-settings" class="tab-content">
									<?php include( WP_Statistics::$reg['plugin-dir'] . 'includes/settings/tabs/wps-privacy.php' ); ?>
                                </div>
                                <div id="notifications-settings" class="tab-content">
									<?php include( WP_Statistics::$reg['plugin-dir'] . 'includes/settings/tabs/wps-notifications.php' ); ?>
                                </div>
                                <div id="overview-display-settings" class="tab-content">
									<?php include( WP_Statistics::$reg['plugin-dir'] . 'includes/settings/tabs/wps-overview-display.php' ); ?>
                                </div>
                                <div id="access-settings" class="tab-content">
									<?php include( WP_Statistics::$reg['plugin-dir'] . 'includes/settings/tabs/wps-access-level.php' ); ?>
                                </div>
                                <div id="exclusions-settings" class="tab-content">
									<?php include( WP_Statistics::$reg['plugin-dir'] . 'includes/settings/tabs/wps-exclusions.php' ); ?>
                                </div>
                                <div id="externals-settings" class="tab-content">
									<?php include( WP_Statistics::$reg['plugin-dir'] . 'includes/settings/tabs/wps-externals.php' ); ?>
                                </div>
                                <div id="maintenance-settings" class="tab-content">
									<?php include( WP_Statistics::$reg['plugin-dir'] . 'includes/settings/tabs/wps-maintenance.php' ); ?>
                                </div>
                                <div id="removal-settings" class="tab-content">
									<?php include( WP_Statistics::$reg['plugin-dir'] . 'includes/settings/tabs/wps-removal.php' ); ?>
                                </div>
							<?php } ?>
                            <div id="about" class="tab-content">
								<?php include( WP_Statistics::$reg['plugin-dir'] . 'includes/settings/tabs/wps-about.php' ); ?>
                            </div>
                        </div><!-- container -->
                    </form>
                </div>
				<?php include WP_Statistics::$reg['plugin-dir'] . 'includes/templates/postbox.php'; ?>
            </div>
        </div>
    </div>

<?php
if ( $wps_nonce_valid ) {
	if ( $wps_admin ) {
		$WP_Statistics->save_options();
	}
	$WP_Statistics->save_user_options();
}