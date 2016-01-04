<?php
	function wp_statistics_generate_pages_postbox_content($total, $uris) {
	
		echo "<div class='log-latest'>";
		
		$i = 0;
		$site_url = site_url();
		
		foreach($uris as $uri) {
			$i++;
			echo "<div class='log-item'>";

			if( empty($uri[3]) ) { $uri[3] = '[' . __('No page title found', 'wp_statistics') . ']'; }
			
			echo "<div class='log-page-title'>{$i} - {$uri[3]}</div>";
			echo "<div class='right-div'>".__('Visits', 'wp_statistics').": <a href='?page=" . WP_STATISTICS_PAGES_PAGE . "&page-uri={$uri[0]}'>" . number_format_i18n($uri[1]) . "</a></div>";
			echo "<div class='left-div'><a dir='ltr' href='{$site_url}{$uri[0]}'>".htmlentities(urldecode($uri[0]),ENT_QUOTES)."</a></div>";
			echo "</div>";
			
			if( $i > 9 ) { break; }
		}
		
		echo "</div>";
	}
