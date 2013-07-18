<?php
	if ( !wp_next_scheduled('report_hook') && get_option('wps_stats_report') ) {
	
		wp_schedule_event(time(), get_option('wps_time_report'), 'report_hook');
	}
	
	function wp_statistics_send_report() {
	
		$string = get_option('wps_content_report');
		
		$template_vars = array(
			'user_online'		=>	wp_statistics_useronline(),
			'today_visitor'		=>	wp_statistics_visitor('today'),
			'today_visit'		=>	wp_statistics_visit('today'),
			'yesterday_visitor'	=>	wp_statistics_visitor('yesterday'),
			'yesterday_visit'	=>	wp_statistics_visit('yesterday'),
			'total_visitor'		=>	wp_statistics_visitor('total'),
			'total_visit'		=>	wp_statistics_visit('total')
		);

		$final_text_report = preg_replace('/%(.*?)%/ime', "\$template_vars['$1']", $string);
		
		if( get_option('wps_send_report') == 'mail' ) {
		
			$blogname = get_bloginfo('name');
			$blogemail = get_bloginfo('admin_email');
			
			$headers[] = "From: $blogname <$blogemail>";
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=utf-8";
			
			wp_mail( get_bloginfo('admin_email'), __('Statistical reporting', 'wp_statistics'), $final_text_report, $headers );
			
		} else if( get_option('wps_send_report') == 'sms' ) {
		
			global $obj;

			if( class_exists(get_option('wp_webservice')) ) {
			
				$obj->to = array(get_option('wp_admin_mobile'));
				$obj->msg = $final_text_report;
				$obj->send_sms();
			}
			
		}
	}
	add_action('report_hook', 'wp_statistics_send_report');
?>