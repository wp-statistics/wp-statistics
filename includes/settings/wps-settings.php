<?php
$wps_nonce_valid = false;

if ( array_key_exists( 'wp-statistics-nonce', $_POST ) ) {
	if ( wp_verify_nonce( $_POST['wp-statistics-nonce'], 'update-options' ) ) {
		$wps_nonce_valid = true;
	}
}

$wps_admin = false;

if ( current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) ) ) ) {
	$wps_admin = true;
}

if ( $wps_admin === false ) {
	$wps_admin = 0;
}

$selected_tab = "";
if ( array_key_exists( 'tab', $_GET ) ) {
	$selected_tab = $_GET['tab'];
}

switch ( $selected_tab ) {
	case 'notifications':
		if ( $wps_admin ) {
			$current_tab = 1;
		} else {
			$current_tab = 0;
		}
		break;
	case 'overview':
		if ( $wps_admin ) {
			$current_tab = 2;
		} else {
			$current_tab = 0;
		}
		break;
	case 'access':
		if ( $wps_admin ) {
			$current_tab = 3;
		} else {
			$current_tab = 0;
		}
		break;
	case 'exclusions':
		if ( $wps_admin ) {
			$current_tab = 4;
		} else {
			$current_tab = 0;
		}
		break;
	case 'externals':
		if ( $wps_admin ) {
			$current_tab = 5;
		} else {
			$current_tab = 0;
		}
		break;
	case 'maintenance':
		if ( $wps_admin ) {
			$current_tab = 6;
		} else {
			$current_tab = 0;
		}
		break;
	case 'removal':
		if ( $wps_admin ) {
			$current_tab = 7;
		} else {
			$current_tab = 0;
		}
		break;
	case 'about':
		if ( $wps_admin ) {
			$current_tab = 8;
		} else {
			$current_tab = 1;
		}
		break;
	default:
		$current_tab = 0;

}
?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery("#tabs").tabs();
			<?php if ( $current_tab != 0 ) {
			echo 'jQuery("#tabs").tabs("option", "active",' . $current_tab . ');' . "\n";
		}?>
            jQuery("#wps_update_button").click(function () {
                var wps_admin = <?php echo $wps_admin;?>;
                var tab = '';

                switch (jQuery("#tabs").tabs("option", "active")) {
                    case 0:
                        if (wps_admin == 1) {
                            tab = 'general';
                        } else {
                            tab = 'overview';
                        }
                        break;
                    case 1:
                        if (wps_admin == 1) {
                            tab = 'notifications';
                        } else {
                            tab = 'about';
                        }
                        break;
                    case 2:
                        if (wps_admin == 1) {
                            tab = 'overview';
                        } else {
                            tab = 'about';
                        }
                        break;
                    case 3:
                        if (wps_admin == 1) {
                            tab = 'access';
                        } else {
                            tab = 'about';
                        }
                        break;
                    case 4:
                        if (wps_admin == 1) {
                            tab = 'exclusions';
                        } else {
                            tab = 'about';
                        }
                        break;
                    case 5:
                        if (wps_admin == 1) {
                            tab = 'Externals';
                        } else {
                            tab = 'about';
                        }
                        break;
                    case 6:
                        if (wps_admin == 1) {
                            tab = 'maintenance';
                        } else {
                            tab = 'about';
                        }
                        break;
                    case 7:
                        if (wps_admin == 1) {
                            tab = 'removal';
                        } else {
                            tab = 'about';
                        }
                        break;
                    case 8:
                        tab = 'about';
                        break;
                }

                var clickurl = jQuery(location).attr('href') + '&tab=' + tab;

                jQuery('#wps_settings_form').attr('action', clickurl).submit();
            });
        });
    </script>
    <a name="top"></a>
    <div class="wrap">
        <h2><?php _e( 'Settings', 'wp-statistics' ); ?></h2>
        <form id="wps_settings_form" method="post">
			<?php wp_nonce_field( 'update-options', 'wp-statistics-nonce' ); ?>
            <div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
                <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
					<?php if ( $wps_admin ) { ?>
                        <li class="ui-state-default ui-corner-top">
                        <a class="ui-tabs-anchor" href="#general-settings"><span><?php _e( 'General', 'wp-statistics' ); ?></span></a>
                        </li><?php } ?>
					<?php if ( $wps_admin ) { ?>
                        <li class="ui-state-default ui-corner-top">
                        <a class="ui-tabs-anchor" href="#notifications-settings"><span><?php _e( 'Notifications', 'wp-statistics' ); ?></span></a>
                        </li><?php } ?>
                    <li class="ui-state-default ui-corner-top">
                        <a class="ui-tabs-anchor" href="#overview-display-settings"><span><?php _e( 'Dashboard/Overview', 'wp-statistics' ); ?></span></a>
                    </li>
					<?php if ( $wps_admin ) { ?>
                        <li class="ui-state-default ui-corner-top">
                        <a class="ui-tabs-anchor" href="#access-settings"><span><?php _e( 'Access Levels', 'wp-statistics' ); ?></span></a>
                        </li><?php } ?>
					<?php if ( $wps_admin ) { ?>
                        <li class="ui-state-default ui-corner-top">
                        <a class="ui-tabs-anchor" href="#exclusions-settings"><span><?php _e( 'Exclusions', 'wp-statistics' ); ?></span></a>
                        </li><?php } ?>
					<?php if ( $wps_admin ) { ?>
                        <li class="ui-state-default ui-corner-top"><a class="ui-tabs-anchor" href="#externals-settings"><span><?php _e( 'Externals', 'wp-statistics' ); ?></span></a>
                        </li><?php } ?>
					<?php if ( $wps_admin ) { ?>
                        <li class="ui-state-default ui-corner-top">
                        <a class="ui-tabs-anchor" href="#maintenance-settings"><span><?php _e( 'Maintenance', 'wp-statistics' ); ?></span></a>
                        </li><?php } ?>
					<?php if ( $wps_admin ) { ?>
                        <li class="ui-state-default ui-corner-top">
                        <a class="ui-tabs-anchor" href="#removal-settings"><span><?php _e( 'Removal', 'wp-statistics' ); ?></span></a>
                        </li><?php } ?>
                    <li class="ui-state-default ui-corner-top">
                        <a class="ui-tabs-anchor" href="#about"><span><?php _e( 'About', 'wp-statistics' ); ?></span></a>
                    </li>
                </ul>

                <div id="general-settings">
					<?php if ( $wps_admin ) {
						include( dirname( __FILE__ ) . '/tabs/wps-general.php' );
					} ?>
                </div>

                <div id="notifications-settings">
					<?php if ( $wps_admin ) {
						include( dirname( __FILE__ ) . '/tabs/wps-notifications.php' );
					} ?>
                </div>

                <div id="overview-display-settings">
					<?php include( dirname( __FILE__ ) . '/tabs/wps-overview-display.php' ); ?>
                </div>

                <div id="access-settings">
					<?php if ( $wps_admin ) {
						include( dirname( __FILE__ ) . '/tabs/wps-access-level.php' );
					} ?>
                </div>

                <div id="exclusions-settings">
					<?php if ( $wps_admin ) {
						include( dirname( __FILE__ ) . '/tabs/wps-exclusions.php' );
					} ?>
                </div>

                <div id="externals-settings">
					<?php if ( $wps_admin ) {
						include( dirname( __FILE__ ) . '/tabs/wps-externals.php' );
					} ?>
                </div>

                <div id="maintenance-settings">
					<?php if ( $wps_admin ) {
						include( dirname( __FILE__ ) . '/tabs/wps-maintenance.php' );
					} ?>
                </div>

                <div id="removal-settings">
					<?php if ( $wps_admin ) {
						include( dirname( __FILE__ ) . '/tabs/wps-removal.php' );
					} ?>
                </div>

                <div id="about">
					<?php include( dirname( __FILE__ ) . '/tabs/wps-about.php' ); ?>
                </div>
            </div>
        </form>
    </div>

<?php
if ( $wps_nonce_valid ) {
	if ( $wps_admin ) {
		$WP_Statistics->save_options();
	}
	$WP_Statistics->save_user_options();
}
