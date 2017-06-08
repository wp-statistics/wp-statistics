<?php
function wp_statistics_export_data() {
	GLOBAL $WP_Statistics, $wpdb; // this is how you get access to the database

	$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) );

	if ( current_user_can( $manage_cap ) ) {
		$table   = $_POST['table-to-export'];
		$type    = $_POST['export-file-type'];
		$headers = $_POST['export-headers'];

		// Validate the table name the user passed to us.
		if ( ! ( $table == "useronline" || $table == "visit" || $table == "visitor" || $table == "exclusions" || $table == "pages" || $table == "search" ) ) {
			$table = false;
		}

		// Validate the file type the user passed to us.
		if ( ! ( $type == "xml" || $type == "csv" || $type == "tsv" ) ) {
			$table = false;
		}

		if ( $table && $type ) {

			require( $WP_Statistics->plugin_dir . '/includes/classes/php-export-data.class.php' );

			$file_name = WPS_EXPORT_FILE_NAME . '-' . $WP_Statistics->Current_Date( 'Y-m-d-H-i' );

			switch ( $type ) {
				case 'xml':
					$exporter = new ExportDataExcel( 'browser', "{$file_name}.xml" );

					break;
				case 'csv':
					$exporter = new ExportDataCSV( 'browser', "{$file_name}.csv" );

					break;
				case 'tsv':
					$exporter = new ExportDataTSV( 'browser', "{$file_name}.tsv" );

					break;
			}

			$exporter->initialize();

			// We need to limit the number of results we retrieve to ensure we don't run out of memory
			$query_base = "SELECT * FROM {$wpdb->prefix}statistics_{$table}";
			$query      = $query_base . ' LIMIT 0,1000';

			$i            = 1;
			$more_results = true;
			$result       = $wpdb->get_results( $query, ARRAY_A );

			// If we didn't get any rows, don't output anything.
			if ( count( $result ) < 1 ) {
				echo "No data in table!";
				exit;
			}

			if ( $headers ) {
				foreach ( $result[0] as $key => $col ) {
					$columns[] = $key;
				}
				$exporter->addRow( $columns );
			}


			while ( $more_results ) {
				foreach ( $result as $row ) {
					$exporter->addRow( $row );

					// Make sure we've flushed the output buffer so we don't run out of memory on large exports.
					ob_flush();
					flush();
				}

				unset( $result );
				$wpdb->flush();

				$query  = $query_base . ' LIMIT ' . ( $i * 1000 ) . ',1000';
				$result = $wpdb->get_results( $query, ARRAY_A );

				if ( count( $result ) == 0 ) {
					$more_results = false;
				}

				$i ++;
			}

			$exporter->finalize();

			exit;
		}
	}
}

?>