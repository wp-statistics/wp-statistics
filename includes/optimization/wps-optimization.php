<?php
GLOBAL $wpdb, $WP_Statistics;
$wp_prefix = $wpdb->prefix;

if ( ! is_super_admin() ) {
	wp_die( __( 'Access denied!', 'wp-statistics' ) );
}

if ( array_key_exists( 'populate', $_GET ) ) {
	if ( intval( $_GET['populate'] ) == 1 ) {
		require_once( plugin_dir_path( __FILE__ ) . '../functions/geoip-populate.php' );
		echo wp_statistics_populate_geoip_info();
	}
}

if ( array_key_exists( 'hash-ips', $_GET ) ) {
	if ( intval( $_GET['hash-ips'] ) == 1 ) {
		// Generate a random salt
		$characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ( $i = 0; $i < 50; $i ++ ) {
			$randomString .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}

		// Get the rows from the Visitors table.
		$result = $wpdb->get_results( "SELECT DISTINCT ip FROM {$wp_prefix}statistics_visitor" );

		foreach ( $result as $row ) {

			if ( substr( $row->ip, 0, 6 ) != '#hash#' ) {

				$wpdb->update(
					$wp_prefix . "statistics_visitor",
					array(
						'ip' => '#hash#' . sha1( $row->ip . $randomString ),
					),
					array(
						'ip' => $row->ip,
					)
				);
			}
		}

		echo "<div class='updated settings-error'><p><strong>" . __( 'IP Addresses replaced with hash values.', 'wp-statistics' ) . "</strong></p></div>";
	}
}

if ( array_key_exists( 'install', $_GET ) ) {
	if ( intval( $_GET['install'] ) == 1 ) {
		$WPS_Installed = "1.0";
		include( plugin_dir_path( __FILE__ ) . "../../wps-install.php" );
		echo "<div class='updated settings-error'><p><strong>" . __( 'Install routine complete.', 'wp-statistics' ) . "</strong></p></div>";
	}
}

if ( array_key_exists( 'index', $_GET ) ) {
	if ( intval( $_GET['index'] ) == 1 ) {
		// Check the number of index's on the visitors table, if it's only 5 we need to check for duplicate entries and remove them
		$result = $wpdb->query( "SHOW INDEX FROM {$wp_prefix}statistics_visitor WHERE Key_name = 'date_ip'" );

		if ( $result != 5 ) {
			// We have to loop through all the rows in the visitors table to check for duplicates that may have been created in error.
			$result = $wpdb->get_results( "SELECT ID, last_counter, ip FROM {$wp_prefix}statistics_visitor ORDER BY last_counter, ip" );

			// Setup the inital values.
			$lastrow    = array( 'last_counter' => '', 'ip' => '' );
			$deleterows = array();

			// Ok, now iterate over the results.
			foreach ( $result as $row ) {
				// if the last_counter (the date) and IP is the same as the last row, add the row to be deleted.
				if ( $row->last_counter == $lastrow['last_counter'] && $row->ip == $lastrow['ip'] ) {
					$deleterows[] .= $row->ID;
				}

				// Update the lastrow data.
				$lastrow['last_counter'] = $row->last_counter;
				$lastrow['ip']           = $row->ip;
			}

			// Now do the acutal deletions.
			foreach ( $deleterows as $row ) {
				$wpdb->delete( $wp_prefix . 'statistics_visitor', array( 'ID' => $row ) );
			}

			// The table should be ready to be updated now with the new index, so let's do it.
			$result = $wpdb->get_results( "ALTER TABLE " . $wp_prefix . 'statistics_visitor' . " ADD UNIQUE `date_ip_agent` ( `last_counter`, `ip`, `agent` (75), `platform` (75), `version` (75) )" );

			// We might have an old index left over from 7.1-7.3 so lets make sure to delete it.
			$wpdb->query( "DROP INDEX `date_ip` ON {$wp_prefix}statistics_visitor" );

			// Record in the options that we've done this update.
			$dbupdates                  = $WP_Statistics->get_option( 'pending_db_updates' );
			$dbupdates['date_ip_agent'] = false;
			$WP_Statistics->update_option( 'pending_db_updates', $dbupdates );
		}
	}
}

if ( array_key_exists( 'visits', $_GET ) ) {
	if ( intval( $_GET['visits'] ) == 1 ) {
		// Check the number of index's on the visits table, if it's only 5 we need to check for duplicate entries and remove them
		$result = $wpdb->query( "SHOW INDEX FROM {$wp_prefix}statistics_visit WHERE Key_name = 'unique_date'" );

		// Note, the result will be the number of fields contained in the index, so in our case 1.
		if ( $result != 1 ) {
			// We have to loop through all the rows in the visitors table to check for duplicates that may have been created in error.
			$result = $wpdb->get_results( "SELECT ID, last_counter, visit FROM {$wp_prefix}statistics_visit ORDER BY last_counter, visit DESC" );

			// Setup the initial values.
			$lastrow    = array( 'last_counter' => '', 'visit' => 0, 'id' => 0 );
			$deleterows = array();

			// Ok, now iterate over the results.
			foreach ( $result as $row ) {
				// if the last_counter (the date) and IP is the same as the last row, add the row to be deleted.
				if ( $row->last_counter == $lastrow['last_counter'] ) {
					$deleterows[] .= $row->ID;
				}

				// Update the lastrow data.
				$lastrow['last_counter'] = $row->last_counter;
				$lastrow['id']           = $row->ID;
				$lastrow['visit']        = $row->visit;
			}

			// Now do the acutal deletions.
			foreach ( $deleterows as $row ) {
				$wpdb->delete( $wp_prefix . 'statistics_visit', array( 'ID' => $row ) );
			}

			// The table should be ready to be updated now with the new index, so let's do it.
			$result = $wpdb->get_results( "ALTER TABLE " . $wp_prefix . 'statistics_visit' . " ADD UNIQUE `unique_date` ( `last_counter` )" );

			// Record in the options that we've done this update.
			$dbupdates                = $WP_Statistics->get_option( 'pending_db_updates' );
			$dbupdates['unique_date'] = false;
			$WP_Statistics->update_option( 'pending_db_updates', $dbupdates );
		}
	}
}

if ( array_key_exists( 'historical-submit', $_POST ) ) {

	if ( array_key_exists( 'wps_historical_visitors', $_POST ) ) {
		$result = $wpdb->update( $wp_prefix . "statistics_historical", array( 'value' => $_POST['wps_historical_visitors'] ), array( 'category' => 'visitors' ) );

		if ( $result == 0 ) {
			$result = $wpdb->insert( $wp_prefix . "statistics_historical", array(
				'value'    => $_POST['wps_historical_visitors'],
				'category' => 'visitors',
				'page_id'  => - 1,
				'uri'      => '-1'
			) );
		}
	}

	if ( array_key_exists( 'wps_historical_visits', $_POST ) ) {
		$result = $wpdb->update( $wp_prefix . "statistics_historical", array( 'value' => $_POST['wps_historical_visits'] ), array( 'category' => 'visits' ) );

		if ( $result == 0 ) {
			$result = $wpdb->insert( $wp_prefix . "statistics_historical", array(
				'value'    => $_POST['wps_historical_visits'],
				'category' => 'visits',
				'page_id'  => - 2,
				'uri'      => '-2'
			) );
		}
	}

}

if ( array_key_exists( 'search', $_GET ) ) {

	// Make sure we get all the search engines, even the ones the disabled ones.
	$se_list   = wp_statistics_searchengine_list();
	$total     = 0;
	$limitsize = 10000;

	foreach ( $se_list as $key => $se ) {

		$sql      = wp_statistics_searchengine_query( $key );
		$rowcount = $wpdb->get_var( "SELECT count(*) FROM `{$wpdb->prefix}statistics_visitor` WHERE {$sql}" );
		$offset   = 0;

		while ( $rowcount > 0 ) {

			$result = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE {$sql} LIMIT {$offset}, {$limitsize}" );

			foreach ( $result as $row ) {
				$parts = parse_url( $row->referred );

				$data['last_counter'] = $row->last_counter;
				$data['engine']       = $key;
				$data['host']         = $parts['host'];
				$data['words']        = $WP_Statistics->Search_Engine_QueryString( $row->referred );
				$data['visitor']      = $row->ID;

				if ( $data['words'] == 'No search query found!' ) {
					$data['words'] = '';
				}

				$wpdb->insert( $wpdb->prefix . 'statistics_search', $data );
				$total ++;
			}

			$rowcount -= $limitsize;
			$offset   += $limitsize;
		}
	}

	$WP_Statistics->update_option( 'search_converted', 1 );
	echo "<div class='updated settings-error'><p><strong>" . sprintf( __( 'Search table conversion complete, %d rows added.', 'wp-statistics' ), $total ) . "</strong></p></div>";

}

$selected_tab = "";
if ( array_key_exists( 'tab', $_GET ) ) {
	$selected_tab = $_GET['tab'];
}

switch ( $selected_tab ) {
	case 'export':
		$current_tab = 1;
		break;
	case 'purging':
		$current_tab = 2;
		break;
	case 'database':
		$current_tab = 3;
		break;
	case 'updates':
		$current_tab = 4;
		break;
	case 'historical':
		$current_tab = 5;
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
    });
</script>
<div class="wrap wp-statistics-settings">
    <h2><?php _e( 'Optimization', 'wp-statistics' ); ?></h2>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div class="wp-list-table widefat widefat">
                <div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
                    <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
                        <li class="ui-state-default ui-corner-top">
                            <a href="#resources" class="ui-tabs-anchor"><span><?php _e( 'Resources/Information', 'wp-statistics' ); ?></span></a>
                        </li>
                        <li class="ui-state-default ui-corner-top">
                            <a href="#export" class="ui-tabs-anchor"><span><?php _e( 'Export', 'wp-statistics' ); ?></span></a>
                        </li>
                        <li class="ui-state-default ui-corner-top">
                            <a href="#purging" class="ui-tabs-anchor"><span><?php _e( 'Purging', 'wp-statistics' ); ?></span></a>
                        </li>
                        <li class="ui-state-default ui-corner-top">
                            <a href="#database" class="ui-tabs-anchor"><span><?php _e( 'Database', 'wp-statistics' ); ?></span></a>
                        </li>
                        <li class="ui-state-default ui-corner-top">
                            <a href="#updates" class="ui-tabs-anchor"><span><?php _e( 'Updates', 'wp-statistics' ); ?></span></a>
                        </li>
                        <li class="ui-state-default ui-corner-top">
                            <a href="#historical" class="ui-tabs-anchor"><span><?php _e( 'Historical', 'wp-statistics' ); ?></span></a>
                        </li>
                    </ul>

                    <div id="resources">
						<?php include( dirname( __FILE__ ) . '/tabs/wps-optimization-resources.php' ); ?>
                    </div>

                    <div id="export">
						<?php include( dirname( __FILE__ ) . '/tabs/wps-optimization-export.php' ); ?>
                    </div>

                    <div id="purging">
						<?php include( dirname( __FILE__ ) . '/tabs/wps-optimization-purging.php' ); ?>
                    </div>

                    <div id="database">
						<?php include( dirname( __FILE__ ) . '/tabs/wps-optimization-database.php' ); ?>
                    </div>

                    <div id="updates">
						<?php include( dirname( __FILE__ ) . '/tabs/wps-optimization-updates.php' ); ?>
                    </div>

                    <div id="historical">
						<?php include( dirname( __FILE__ ) . '/tabs/wps-optimization-historical.php' ); ?>
                    </div>

                </div>
            </div>

			<?php include_once dirname( __FILE__ ) . '/../templates/postbox.php'; ?>
        </div>
    </div>
</div>