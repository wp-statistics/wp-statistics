<?php
/*
Plugin Name: WP-Statistics
Plugin URI: http://iran98.org/category/wordpress/wp-statistics/
Description: Summary statistics of blog.
Version: 1.0
Author: Mostafa Soufi
Author URI: http://iran98.org/
License: GPL2
*/
	load_plugin_textdomain('wp-statistics','wp-content/plugins/wp-statistics');
	add_action("plugins_loaded", "statistics_admin_widget");

function statistics_widget() {
	echo "<h2 class='widgettitle'>".__('Statistics', 'wp-statistics')."</h2>";
	echo "<ul>";
		echo "<li>";
		echo __('Total Posts: ', 'wp-statistics');
		statistics_countposts();
		echo "</li>";
		
		echo "<li>";
		echo __('Total Pages: ', 'wp-statistics');
		statistics_countpages();
		echo "</li>";
		
		echo "<li>";
		echo __('Total Comments: ', 'wp-statistics');
		statistics_countcomment();
		echo "</li>";

		echo "<li>";
		echo __('Total Spams: ', 'wp-statistics');
		statistics_countspam();
		echo "</li>";

		echo "<li>";
		echo __('Last Post Date: ', 'wp-statistics');
		statistics_lastpostdate();
		echo "</li>";

		echo "<li>";
		echo __('Total Blog Hits: ', 'wp-statistics');
		statistics_totalhits();
		echo "</li>";	
}
	
function statistics_admin_widget() {
	register_sidebar_widget(__('Summary statistics of blog', 'wp-statistics'), 'statistics_widget'); }

function statistics_countposts($type=publish) {
	$count_posts = wp_count_posts();
	echo $count_posts->$type;
}

function statistics_countpages() {
	$count_pages = wp_count_posts('page');
	echo $count_pages->publish;
}

function statistics_countcomment() {
	global $wpdb;
	$countcomms = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");
	if (0 < $countcomms) $countcomms = number_format($countcomms);
	echo $countcomms;
}

function statistics_countspam() {
	echo number_format_i18n(get_option('akismet_spam_count'));
}

function statistics_countusers() {
	$result = count_users();
	echo $result['total_users'];
}

function statistics_lastpostdate($type=english) {
	global $wpdb;
	$db_date = $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_type='post' AND post_status='publish' ORDER BY ID DESC LIMIT 1");
	$date_format = get_option('date_format');
	if ( $type == 'farsi' ) {
		echo jdate($date_format, strtotime($db_date));
	} else 
		echo date($date_format, strtotime($db_date)); 
}

// Show Count Feedburner Subscribe by Affiliate Marketer
function statistics_countsubscrib($feed_url) {
	$feedcount = get_option("feedrsscount");
	if ($feedcount['lastcheck'] < (mktime()-3600)) {
	$whaturl='https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri='.$feed_url;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $whaturl);
	$data = curl_exec($ch);
	curl_close($ch);
	$xml = new SimpleXMLElement($data);
	$fb = $xml->feed->entry['circulation'];
	$feedcount['count'] = number_format($fb);
	$feedcount['lastcheck'] = mktime();
	update_option("feedrsscount",$feedcount);}
	echo $feedcount['count'];
	}

function statistics_totalhits() {
	$file_counter = ("wp-content/plugins/wp-statistics/wp-statistics.txt");
	$hits = file($file_counter);
	$hits[0] ++;
	$fp = fopen($file_counter , "w");
	fwrite($fp, "$hits[0]");
	fclose($fp);
	echo $hits[0];
}
?>