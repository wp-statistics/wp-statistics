<?php
	require('../../../../../wp-load.php');
	
	if( !is_super_admin() )
		wp_die(__('Access denied!', 'wp_statistics'));
		
	$table = $_POST['table-to-export'];
	$type = $_POST['export-file-type'];
	$headers = $_POST['export-headers'];
	
	// Validate the table name the user passed to us.
	if( !( $table == "useronline" || $table == "visit" || $table == "visitor" || $table == "exclusions" || $table == "pages" ) ) { $table = FALSE; } 
	
	// Validate the file type the user passed to us.
	if( !( $type == "excel" || $type == "xml" || $type == "csv" || $type == "tsv" ) ) { $table = FALSE; } 
	
	if($table && $type) {
	
		require('../classes/php-export-data.class.php');
		
		$file_name = WPS_EXPORT_FILE_NAME . '-' . $WP_Statistics->Current_Date('Y-m-d-H-i');
		
		switch($type) {
			case 'excel':
				$exporter = new ExportDataExcel('browser', "{$file_name}.xls");
			break;
			
			case 'xml':
				$exporter = new ExportDataExcel('browser', "{$file_name}.xml");
			break;
			
			case 'csv':
				$exporter = new ExportDataCSV('browser', "{$file_name}.csv");
			break;
			
			case 'tsv':
				$exporter = new ExportDataTSV('browser', "{$file_name}.tsv");
			break;
		}

		$exporter->initialize();
		
		// We need to limit the number of results we retrieve to ensure we don't run out of memory
		$query_base = "SELECT * FROM {$wpdb->prefix}statistics_{$table}";
		$query = $query_base . ' LIMIT 0,1000';

		$i = 1;
		$more_results = true;
		$result = $wpdb->get_results($query, ARRAY_A);

		if( $headers ) {
			foreach( $result[0] as $key => $col ) { $columns[] = $key; }
			$exporter->addRow($columns);
		}
		
		
		while( $more_results ) {
			foreach($result as $row) {
				$exporter->addRow($row);
				
				// Make sure we've flushed the output buffer so we don't run out of memory on large exports.
				ob_flush();
				flush();
			}
			
			unset( $result );
			$wpdb->flush();
			
			$query = $query_base . ' LIMIT ' . ($i * 1000) . ',1000';
			$result = $wpdb->get_results($query, ARRAY_A);
			
			if( count( $result ) == 0 ) { $more_results = false; }
			
			$i++;
		}
		
		$exporter->finalize();
		
	} else {
		wp_die(__('Please select the desired items.', 'wp_statistics'), false, array('back_link' => true));
	}
?>