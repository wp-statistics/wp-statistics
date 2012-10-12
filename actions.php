<?php
	require('../../../wp-load.php');
	if(is_super_admin()) {
		global $wpdb, $table_prefix;

		$increase_value = $_REQUEST['increase_value'];
		$reduction_value = $_REQUEST['reduction_value'];

		if($increase_value) {
			$result_increase = $wpdb->query("UPDATE {$table_prefix}statistics_visits SET total = total + '".$increase_value."'");
			$count_total = $wpdb->get_var("SELECT total FROM {$table_prefix}statistics_visits");
			if($result_increase) {
				echo __('Added', 'wp_statistics')." ".$increase_value." ".__('value', 'wp_statistics').". ";
				echo __('Total Visit', 'wp_statistics'). " $count_total";
			}
		} else if($reduction_value) {
			$result_reduction = $wpdb->query("UPDATE {$table_prefix}statistics_visits SET total = total - '".$reduction_value."'");
			$count_total = $wpdb->get_var("SELECT total FROM {$table_prefix}statistics_visits");
			if($result_reduction) {
				echo __('Was', 'wp_statistics')." ".$reduction_value." ".__('low value', 'wp_statistics').". ";
				echo __('Total Visit', 'wp_statistics'). " $count_total";
			}
		} else {
			_e('Please Enter value!', 'wp_statistics');
		}
	} else {
		wp_die(__('Access is Denied!', 'wp_statistics'));
	}
?>