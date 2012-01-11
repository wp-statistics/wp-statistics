<?php
	require('../../../wp-load.php');
	$increase_value = $_REQUEST['increase_value'];
	$reduction_value = $_REQUEST['reduction_value'];

	if($increase_value) {
		echo __('Sorry! this feature is for Premium version', 'wp_statistics');
	} else if($reduction_value) {
		echo __('Sorry! this feature is for Premium version', 'wp_statistics');
	} else {
		_e('Please Enter value!', 'wp_statistics');
	}
?>