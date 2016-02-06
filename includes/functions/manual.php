<?php
	function wp_statistics_download_manual() {
		GLOBAL $WP_Statistics, $wpdb; // this is how you get access to the database

		$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options') );

		if( current_user_can( $manage_cap ) ) {

			$type = $_GET['type'];
			
			if( $type == 'odt' || $type == 'html' ) {

				$filepath = $WP_Statistics->plugin_dir . '/manual';
				$filename = '';
				$ext = '.' . $type;

				// open this directory 
				$dir = opendir( $filepath );

				// get each entry
				while( $entry = readdir( $dir ) ) {
					if( substr( $entry, -strlen( $ext ) ) == $ext ) {
						$filename = $entry;
					}		
				}

				// close directory
				closedir( $dir );

				if( $filename != '' ) {
					header('Content-Type: application/octet-stream;');
					header('Content-Disposition: attachment; filename="' . $filename . '"');
					
					readfile( $filepath . '/' . $filename );
				}
			}
		}
	}
?>