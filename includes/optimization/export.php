<?php
	require('../../../../../wp-blog-header.php');
	
	if( !is_super_admin() )
		wp_die(__('Access denied!', 'wp_statistics'));
		
	$table = $_POST['table-to-export'];
	$type = $_POST['export-file-type'];
	
	if($table && $type) {
	
		require('../class/php-export-data.class.php');
		
		$s = new WP_Statistics();
		
		$file_name = WPS_EXPORT_FILE_NAME . '-' . $s->Current_Date('Y-m-d-H:i');
		
		$result = $wpdb->get_results("SELECT * FROM {$table_prefix}statistics_{$table}");
		
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
		
		foreach(objectToArray($result) as $row) {
			$exporter->addRow($row);
		}
		
		$exporter->finalize();
		
	} else {
		wp_die(__('Please select the desired items.', 'wp_statistics'), false, array('back_link' => true));
	}
?>