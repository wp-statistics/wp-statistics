<?php
	add_shortcode( 'wpstatistics', 'wp_statistics_shortcodes' );
	add_filter('widget_text', 'do_shortcode');
	
	function wp_statistics_shortcodes($atts) {
		/*
			WP Statitics shortcode is in the format of:
			
				[wpstatistics stat=xxx time=xxxx provider=xxxx format=xxxxxx]
				
			Where:
				stat = the statistic you want.
				time = is the timeframe, strtotime() (http://php.net/manual/en/datetime.formats.php) will be used to calculate it.
				provider = the search provider to get stats on.
				format = i18n, english, none
		*/
		
		$formatnumber = array_key_exists( 'format', $atts );
		
		switch( $atts['stat'] ) {
			case 'usersonline':
				$result = wp_statistics_useronline();
				break;
				
			case 'visits':
				$result = wp_statistics_visit($atts['time']);
				break;
				
			case 'visitors':
				$result = wp_statistics_visitor($atts['time'], null, true);
				break;
				
			case 'pagevisits':
				$result = wp_statistics_pages($atts['time']);
				break;
				
			case 'searches':
				$result = wp_statistics_searchengine($atts['provider']);
				break;
				
			case 'postcount':
				$result = wp_statistics_countposts();
				break;
				
			case 'pagecount':
				$result = wp_statistics_countpages();
				break;
				
			case 'commentcount':
				$result = wp_statistics_countcomment();
				break;
				
			case 'spamcount':
				$result = wp_statistics_countspam();
				break;
				
			case 'usercount':
				$result = wp_statistics_countusers();
				break;
				
			case 'postaverage':
				$result = wp_statistics_average_post();
				break;
				
			case 'commentaverage':
				$result = wp_statistics_average_comment();
				break;
				
			case 'useraverage':
				$result = wp_statistics_average_registeruser();
				break;
				
			case 'lpd':
				$result = wp_statistics_lastpostdate();
				$formatnumber = false;
				break;
			}
			
		if( $formatnumber ) {
			switch( strtolower( $atts['format'] ) )	{
				case 'i18n':
					$result = number_format_i18n( $result );
					
					break;
				case 'english':
					$result = number_format( $result );
					
					break;
			}
		}
			
		return $result;
	}
?>