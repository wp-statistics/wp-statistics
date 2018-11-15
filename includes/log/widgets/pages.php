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
	echo "<table width=\"100%\" class=\"widefat table-stats\" id=\"last-referrer\">
		  <tr>";
	echo "<td width='10%'>" . __( 'ID', 'wp-statistics' ) . "</td>";
	echo "<td width='40%'>" . __( 'Title', 'wp-statistics' ) . "</td>";
	echo "<td width='40%'>" . __( 'Link', 'wp-statistics' ) . "</td>";
	echo "<td width='10%'>" . __( 'Visits', 'wp-statistics' ) . "</td>";
	echo "</tr>";

	foreach ( $result as $item ) {
		$counter += 1;
		// Lookup the post title.
		$post = get_post( $item->id );
		if ( is_object( $post ) ) {
			$title = $post->post_title;
		} else {
			if ( $item->uri == '/' ) {
				$title = get_bloginfo();
			} else {
				$title = __( 'No page title found', 'wp-statistics' );
			}
		}
		echo "<tr>";
		echo "<td style=\"text-align: left\">" . $counter . "</td>";
		echo "<td style=\"text-align: left\">" . $title . "</td>";
		echo '<td style="text-align: left"><a href="' .
		     htmlentities( $site_url . $item->uri, ENT_QUOTES ) .
		     '">' .
		     htmlentities( urldecode( $item->uri ), ENT_QUOTES ) .
		     '</a></td>';
		echo '<td style="text-align: left"><a href="?page=' .
		     WP_Statistics::$page['pages'] .
		     '&page-uri=' .
		     htmlentities( $item->uri, ENT_QUOTES ) .
		     '">' .
		     number_format_i18n( $item->count_sum ) .
		     '</a></td>';
		echo '</tr>';

	}
	echo '</table>';
}
