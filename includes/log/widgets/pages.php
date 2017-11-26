<?php
function wp_statistics_generate_pages_postbox_content() {
	global $wpdb;
	$result   = $wpdb->get_results(
		"SELECT
				`pages`.`uri`,
				`pages`.`id`,
				SUM(`pages`.`count`) + IFNULL(`historical`.`value`, 0) AS `count_sum`
			FROM `{$wpdb->prefix}statistics_pages` `pages`
			LEFT JOIN `{$wpdb->prefix}statistics_historical` `historical`
				ON `pages`.`uri`=`historical`.`uri` AND `historical`.`category`='uri'
			GROUP BY `uri`
			ORDER BY `count_sum` DESC
			LIMIT 10
			"
	);
	$site_url = site_url();
	$counter  = 0;
	echo '<div class="log-latest">';
	foreach ( $result as $item ) {
		$counter += 1;
		echo '<div class="log-item">';
		// Lookup the post title.
		$post = get_post($item->id);
		if ( is_object($post) ) {
			$title = $post->post_title;
		} else {
			if ( $item->uri == '/' ) {
				$title = get_bloginfo();
			} else {
				$title = '[' . __('No page title found', 'wp-statistics') . ']';
			}
		}
		echo "<div class=\"log-page-title\">{$counter} - {$title}</div>";
		echo '<div class="right-div">' .
		     __('Visits', 'wp-statistics') .
		     ': <a href="?page=' .
		     WP_Statistics::$page['pages'] .
		     '&page-uri=' .
		     htmlentities($item->uri, ENT_QUOTES) .
		     '">' .
		     number_format_i18n($item->count_sum) .
		     '</a></div>';
		echo '<div><a href="' .
		     htmlentities($site_url . $item->uri, ENT_QUOTES) .
		     '">' .
		     htmlentities(urldecode($item->uri), ENT_QUOTES) .
		     '</a></div>';
		echo '</div>';

	}
	echo '</div>';
}
