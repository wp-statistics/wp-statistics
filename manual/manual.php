<?php
	require('../../../../wp-load.php');
	
	if( !is_super_admin() )
		wp_die(__('Access denied!', 'wp_statistics'));
		
	$type = $_GET['type'];
		
	if($type == 'odt' || $type == 'html') {

		$filepath = plugin_dir_path(__FILE__);
		$filename = '';
		$ext = '.' . $type;

		// open this directory 
		$dir = opendir($filepath);

		// get each entry
		while($entry = readdir($dir)) {
			if (substr($entry,-strlen($ext)) == $ext  ) {
				$filename = $entry;
			}		
		}

		// close directory
		closedir($dir);

		if( $filename == '' ) {
			wp_die(sprintf(__('Manual not found: %s', 'wp_statistics'), $filepath.$filename), false, array('back_link' => true));
		}
		
		header("Content-Type: application/octet-stream;");
		header("Content-Disposition: attachment; filename=".$filename);
		
		readfile($filepath.$filename);


	} else {
		wp_die(sprintf(__('Invalid file type selected: %s', 'wp_statistics'), htmlentities($type)), false, array('back_link' => true));
	}
?>