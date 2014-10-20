<?php
	GLOBAL $wpdb;
	$wp_prefix = $wpdb->prefix;

	if( !is_super_admin() )
		wp_die(__('Access denied!', 'wp_statistics'));
		
	if( array_key_exists( 'populate', $_GET ) ) {
		if( $_GET['populate'] == 1 ) {
			require_once( plugin_dir_path( __FILE__ ) . '../functions/geoip-populate.php' );
			echo wp_statistics_populate_geoip_info();
		}
	}
	
	if( array_key_exists( 'hash-ips', $_GET ) ) {
		if( $_GET['hash-ips'] == 1 ) {
			// Generate a random salt
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randomString = '';
			for ($i = 0; $i < 50; $i++) {
				$randomString .= $characters[rand(0, strlen($characters) - 1)];
			}
			
			// Get the rows from the Visitors table.
			$result = $wpdb->get_results( "SELECT DISTINCT ip FROM {$wp_prefix}statistics_visitor" );
			
			foreach( $result as $row ) {

				if( substr( $row->ip, 0, 6 ) != '#hash#' ) { 
			
					$wpdb->update(
							$wp_prefix . "statistics_visitor",
							array(
								'ip'	=>	'#hash#' . sha1( $row->ip . $randomString ),
							),
							array(
								'ip'	=>	$row->ip,
							)
						);
				}
			}
			
			echo "<div class='updated settings-error'><p><strong>" . __('IP Addresses replaced with hash values.', 'wp_statistics') . "</strong></p></div>";		
		}
	}

	if( array_key_exists( 'install', $_GET ) ) {
		if( $_GET['install'] == 1 ) {
			$WPS_Installed = "1.0";
			include( plugin_dir_path( __FILE__ ) . "../../wps-install.php" );
			echo "<div class='updated settings-error'><p><strong>" . __('Install routine complete.', 'wp_statistics') . "</strong></p></div>";		
		}
	}
	
	if( array_key_exists( 'index', $_GET ) ) {
		if( $_GET['index'] == 1 ) {
			// Check the number of index's on the visitors table, if it's only 5 we need to check for duplicate entries and remove them
			$result = $wpdb->query("SHOW INDEX FROM {$wp_prefix}statistics_visitor WHERE Key_name = 'date_ip'");
			
			if( $result != 2 ) {
				// We have to loop through all the rows in the visitors table to check for duplicates that may have been created in error.
				$result = $wpdb->get_results( "SELECT ID, last_counter, ip FROM {$wp_prefix}statistics_visitor ORDER BY last_counter, ip" );
				
				// Setup the inital values.
				$lastrow = array( 'last_counter' => '', 'ip' => '' );
				$deleterows = array();
				
				// Ok, now iterate over the results.
				foreach( $result as $row ) {
					// if the last_counter (the date) and IP is the same as the last row, add the row to be deleted.
					if( $row->last_counter == $lastrow['last_counter'] && $row->ip == $lastrow['ip'] ) { $deleterows[] .=  $row->ID;}
					
					// Update the lastrow data.
					$lastrow['last_counter'] = $row->last_counter;
					$lastrow['ip'] = $row->ip;
				}
				
				// Now do the acutal deletions.
				foreach( $deleterows as $row ) {
					$wpdb->delete( $wp_prefix . 'statistics_visitor', array( 'ID' => $row ) );
				}
				
				// The table should be ready to be updated now with the new index, so let's do it.
				$result = $wpdb->get_results( "ALTER TABLE " . $wp_prefix . 'statistics_visitor' . " ADD UNIQUE `date_ip_agent` ( `last_counter`, `ip`, `agent` (75), `platform` (75), `version` (75) )" );

				// We might have an old index left over from 7.1-7.3 so lets make sure to delete it.
				$wpdb->query( "DROP INDEX `date_ip` ON {$wp_prefix}statistics_visitor" );
			}
		}
	}

	if( array_key_exists( 'historical-submit', $_POST ) ) {

		if( array_key_exists( 'wps_historical_visitors', $_POST ) )	{
			$result = $wpdb->update( $wp_prefix . "statistics_historical", array( 'value' => $_POST['wps_historical_visitors'] ), array( 'type' => 'visitors' ) );

			if( $result == 0 ) {
				$result = $wpdb->insert( $wp_prefix . "statistics_historical", array( 'value' => $_POST['wps_historical_visitors'], 'type' => 'visitors' ) );
			}
		}
		
		if( array_key_exists( 'wps_historical_visits', $_POST ) )	{
			$result = $wpdb->update( $wp_prefix . "statistics_historical", array( 'value' => $_POST['wps_historical_visits'] ), array( 'type' => 'visits' ) );
			
			if( $result == 0 ) {
				$result = $wpdb->insert( $wp_prefix . "statistics_historical", array( 'value' => $_POST['wps_historical_visits'], 'type' => 'visits' ) );
			}
		}

	}
	
$selected_tab = "";
if( array_key_exists( 'tab', $_GET ) ) { $selected_tab = $_GET['tab']; }

switch( $selected_tab )
	{
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
	jQuery(document).ready(function() {
		jQuery("#tabs").tabs();
		<?php if( $current_tab != 0 ) { echo 'jQuery("#tabs").tabs("option", "active",' . $current_tab. ');' . "\n"; }?>
	} );
</script>
<div class="wrap">
	<div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="#resources" class="ui-tabs-anchor"><span><?php _e('Resources/Information', 'wp_statistics'); ?></span></a></li>
			<li class="ui-state-default ui-corner-top"><a href="#export" class="ui-tabs-anchor"><span><?php _e('Export', 'wp_statistics'); ?></span></a></li>
			<li class="ui-state-default ui-corner-top"><a href="#purging" class="ui-tabs-anchor"><span><?php _e('Purging', 'wp_statistics'); ?></span></a></li>
			<li class="ui-state-default ui-corner-top"><a href="#database" class="ui-tabs-anchor"><span><?php _e('Database', 'wp_statistics'); ?></span></a></li>
			<li class="ui-state-default ui-corner-top"><a href="#updates" class="ui-tabs-anchor"><span><?php _e('Updates', 'wp_statistics'); ?></span></a></li>
			<li class="ui-state-default ui-corner-top"><a href="#historical" class="ui-tabs-anchor"><span><?php _e('Historical', 'wp_statistics'); ?></span></a></li>
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