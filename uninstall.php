<?php
	require('../../../wp-load.php');
	if(is_super_admin()) {
		global $wpdb, $table_prefix;

		// Database
		$get_dt[1] = $wpdb->query("DROP TABLE {$table_prefix}statistics_date");
		$get_dt[2] = $wpdb->query("DROP TABLE {$table_prefix}statistics_reffered");
		$get_dt[3] = $wpdb->query("DROP TABLE {$table_prefix}statistics_useronline");
		$get_dt[4] = $wpdb->query("DROP TABLE {$table_prefix}statistics_visits");

		// Options (Setting page)
		$get_do[1] = delete_option('enable_stats');
		$get_do[2] = delete_option('enable_decimals');
		$get_do[3] = delete_option('enable_wps_adminbar');
		$get_do[4] = delete_option('time_useronline_s');
		$get_do[5] = delete_option('database_checktime');
		$get_do[6] = delete_option('items_statistics');
		$get_do[7] = delete_option('pagerank_google_url');
		$get_do[8] = delete_option('pagerank_alexa_url');
		$get_do[9] = delete_option('coefficient_visitor');

		// Options (widget page)
		$get_dw[2] = delete_option('useronline_widget');
		$get_dw[3] = delete_option('tvisit_widget');
		$get_dw[4] = delete_option('yvisit_widget');
		$get_dw[5] = delete_option('wvisit_widget');
		$get_dw[6] = delete_option('mvisit_widget');
		$get_dw[7] = delete_option('ysvisit_widget');
		$get_dw[8] = delete_option('ttvisit_widget');
		$get_dw[9] = delete_option('ser_widget');
		$get_dw[10] = delete_option('select_se');
		$get_dw[11] = delete_option('tp_widget');
		$get_dw[12] = delete_option('tpg_widget');
		$get_dw[13] = delete_option('tc_widget');
		$get_dw[14] = delete_option('ts_widget');
		$get_dw[15] = delete_option('tu_widget');
		$get_dw[16] = delete_option('ap_widget');
		$get_dw[17] = delete_option('ac_widget');
		$get_dw[18] = delete_option('au_widget');
		$get_dw[19] = delete_option('lpd_widget');
		$get_dw[20] = delete_option('select_lps');

		if($get_dt[1]){
			echo $table_prefix."statistics_date ".__('deleted!', 'wp_statistics')."<br />";
		}
		if($get_dt[2]){
			echo $table_prefix."statistics_reffered ".__('deleted!', 'wp_statistics')."<br />";
		}
		if($get_dt[3]){
			echo $table_prefix."statistics_useronline ".__('deleted!', 'wp_statistics')."<br />";
		}	
		if($get_dt[4]){
			echo $table_prefix."statistics_visits ".__('deleted!', 'wp_statistics')."<br />";
		}

		if($get_do[3] == true){
			echo "<p>".__('All plugin data is deleted', 'wp_statistics')."</p>";
			echo "<p><a href='".get_bloginfo('url')."/wp-admin/plugins.php'>".__('Disable plugin', 'wp_statistics')."</a></p>";
		} else {
			echo __('plugin options have been deleted', 'wp_statistics');
		}
	} else {
		wp_die(__('Access is Denied!', 'wp_statistics'));
	}
?>