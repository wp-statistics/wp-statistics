<?php
/*
Plugin Name: WP-Statistics
Plugin URI: http://iran98.org/category/wordpress/wp-statistics/
Description: Summary statistics of blog.
Version: 2.3.2
Author: Mostafa Soufi
Author URI: http://iran98.org/
License: GPL2
*/
	define('WP_STATISTICS_VERSION', '2.2.9');

	include_once("widget.php");

	load_plugin_textdomain('wp_statistics','wp-content/plugins/wp-statistics/langs');

	global $wp_statistics_db_version, $wpdb;
	$wp_statistics_db_version = "1.0";

	/* Date And time Varieble */
	$get_date	=	date('Y-m-d H:i:s' ,current_time('timestamp',0));
	$get_now	=	date('Y-m-d' ,current_time('timestamp',0));
	$get_week	=	date('W');
	$get_month	=	date('m');
	$get_year	=	date('Y');

	/* Server Varieble */
	$get_referred	=	$_SERVER['HTTP_REFERER'];
	$get_useragent	=	$_SERVER['HTTP_USER_AGENT'];
	$get_userip		=	$_SERVER['REMOTE_ADDR'];

	/* Live Statistics Varieble */
	$database_checktime = get_option('database_checktime');
	if(!$database_checktime) {
		$database_checktime = "10";
	}

	/* Coefficient Visitor */
	$get_coefficient = get_option('coefficient_visitor');
	if(!$get_coefficient) {
		$get_coefficient = "1";
	}

	function wp_statistics_menubar() {
		global $wp_admin_bar;
		if (!is_super_admin() || !is_admin_bar_showing() || !get_option('enable_wps_adminbar')) {
			return;
		} else {
			$wp_admin_bar->add_menu( array(
				'id'		=> 'wp-statistic_menu',
				'title'		=> '<img src="'.plugin_dir_url(__FILE__).'/images/icon.png"/>'
			));

			$wp_admin_bar->add_menu( array(
				'parent'	=> 'wp-statistic_menu',
				'title'		=> __('User Online', 'wp_statistics').": ".wp_statistics_useronline()
			));

			$wp_admin_bar->add_menu( array(
				'parent'	=> 'wp-statistic_menu',
				'title'		=> __('Today Visit', 'wp_statistics').": ".wp_statistics_today()
			));

			$wp_admin_bar->add_menu( array(
				'parent'	=> 'wp-statistic_menu',
				'title'		=> __('Yesterday visit', 'wp_statistics'). ": ".wp_statistics_yesterday()
			));

			$wp_admin_bar->add_menu( array(
				'parent'	=> 'wp-statistic_menu',
				'title'		=> __('Total Visit', 'wp_statistics'). ": ".wp_statistics_total()
			));

			$wp_admin_bar->add_menu( array(
				'parent'	=> 'wp-statistic_menu',
				'title'		=> __('Plugin home page', 'wp_statistics'),
				'href'		=> 'http://wordpress.org/extend/plugins/wp-statistics/'
			));

			$wp_admin_bar->add_menu( array(
				'parent'	=> 'wp-statistic_menu',
				'title'		=> __('Forums plugins', 'wp_statistics'),
				'href'		=> __('http://wordpress.org/extend/plugins/wp-statistics/', 'wp_statistics')
			));
		}
	}
	add_action('admin_bar_menu', 'wp_statistics_menubar', 20);

	function wp_statistics_options() {
		update_option('enable_wps_adminbar', true);
	}
	register_activation_hook(__FILE__,'wp_statistics_options');

	function wp_statistics_install() {
		global $wp_statistics_db_version, $table_prefix;

		$table_visit	= $table_prefix."statistics_visits";
		$table_dates	= $table_prefix."statistics_date";
		$table_users	= $table_prefix."statistics_useronline";
		$table_referred	= $table_prefix."statistics_reffered";
		$time_1 = date('i');

		$create_visit_table = ("CREATE TABLE ".$table_visit."
			(today int(10),
			yesterday int(10),
			week int(20),
			month int(20),
			year int (20),
			total int(20),
			google int(10),
			yahoo int (10),
			bing int (10)) CHARSET=utf8");

		$create_dates_table = ("CREATE TABLE ".$table_dates."
			(last_counter DATE,
			last_week int(2),
			last_month int(2),
			last_year int(5),
			timestamp int(10),
			last_visit DATETIME) CHARSET=utf8");

		$create_users_table = ("CREATE TABLE ".$table_users."
			(ip char(20),
			timestamp int(10),
			time DATETIME,
			referred text,
			agent char(255))");

		$create_referred_table = ("CREATE TABLE ".$table_referred."
			(referred text,
			ip char(20),
			time DATETIME,
			agent char(255))");

		$primary_visit_value = ("INSERT INTO ".$table_visit."
			(today, yesterday, week, month, year, total, google, yahoo, bing) VALUES
			(0, 0, 0, 0, 0, 0, 0, 0, 0)");

		$primary_date_value = ("INSERT INTO ".$table_dates."
			(last_counter, last_week, last_month, last_year, timestamp, last_visit) VALUES
			('00-00-00', '".$get_week."', '".$get_month."', '".$get_year."', '".$time_1."', '".$get_date."')");

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		dbDelta($create_visit_table);
		dbDelta($create_dates_table);
		dbDelta($create_users_table);
		dbDelta($create_referred_table);
	
		dbDelta($primary_visit_value);
		dbDelta($primary_date_value);

		add_option('wp_statistics_db_version', '2.2.8');
	}
	register_activation_hook(__FILE__,'wp_statistics_install');

	function wp_check_spider() {
		$spiders = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi",
			"looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
			"Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot",
			"crawler", "www.galaxy.com", "Googlebot", "googlebot", "Scooter", "Slurp",
			"msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz",
			"Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot",
			"Mediapartners-Google", "Sogou web spider", "WebAlta Crawler","TweetmemeBot",
			"Butterfly","Twitturls","Me.dium","Twiceler", "Yammybot", "Openbot", "Yahoo",
			"ia_archiver", "Lycos", "AltaVista", "Googlebot-Mobile", "Rambler", "AbachoBOT",
			"accoona", "AcoiRobot", "ASPSeek", "CrocCrawler", "Dumbot", "FAST-WebCrawler",
			"GeonaBot", "MSRBOT", "IDBot", "eStyle", "Scrubby");

		foreach($spiders as $spider) {
			if(strpos($_SERVER['HTTP_USER_AGENT'], $spider) == true)
			return true;
		}
		return false;
	}

	function wp_statistics() {
		global $wpdb, $table_prefix, $get_date, $get_now, $get_week, $get_month, $get_year, $get_referred, $get_userip, $get_useragent, $get_coefficient;

		$get_dates_row = $wpdb->get_row("SELECT * FROM {$table_prefix}statistics_date");

		if( ($get_dates_row->last_visit) != $get_date && !is_admin() && !wp_check_spider()) {
			if( ($get_dates_row->last_counter) == $get_now ) {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET today = today+'".$get_coefficient."', total = total+'".$get_coefficient."'");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_visit = '".$get_date."'");
			} else {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET yesterday = today, total = total+'".$get_coefficient."'");
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET today = 0");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_counter = '".$get_now."', last_visit = '".$get_date."'");
			}
			if( ($get_dates_row->last_week) == $get_week ) {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET week = week+'".$get_coefficient."'");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_week = '".$get_week."'");
			} else {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET week = 0");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_week = '".$get_week."'");
			}
			if( ($get_dates_row->last_month) == $get_month ) {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET month = month+'".$get_coefficient."'");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_month = '".$get_month."'");
			} else {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET month = 0");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_month = '".$get_month."'");
			}
			if( ($get_dates_row->last_year) == $get_year ) {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET year = year+'".$get_coefficient."'");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_year = '".$get_year."'");
			} else {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET year = 0");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_year = '".$get_year."'");
			}
		}

		if(get_option('daily_referer')) {
			if( ($get_dates_row->last_counter) == $get_now ) {
				if(strstr($get_referred, 'google.com')) {
					$wpdb->query("UPDATE {$table_prefix}statistics_visits SET google = google+1");
				} else if(strstr($get_referred, 'yahoo.com')) {
					$wpdb->query("UPDATE {$table_prefix}statistics_visits SET yahoo = yahoo+1");
				} else if(strstr($get_referred, 'bing.com')) {
					$wpdb->query("UPDATE {$table_prefix}statistics_visits SET bing = bing+1");
				}
			} else {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET google = 0");
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET yahoo = 0");
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET bing = 0");
			}
		} else {
			if(strstr($get_referred, 'google.com'))
			{
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET google = google+1");
			} else if(strstr($get_referred, 'yahoo.com')) {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET yahoo = yahoo+1");
			} else if(strstr($get_referred, 'bing.com')) {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET bing = bing+1");
			}
		}

		$get_items_statistics = get_option('items_statistics');
		if(!$get_items_statistics) {
			$get_items_statistics = '5';
		}

		$get_num_reffered = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}statistics_reffered");
		if($get_num_reffered < $get_items_statistics) {
			$get_var_ip = $wpdb->get_var("SELECT ip FROM {$table_prefix}statistics_reffered WHERE ip = '".$get_userip."'");
				if(!$get_var_ip)
				{
					$wpdb->query("INSERT INTO {$table_prefix}statistics_reffered (referred, ip, time, agent) VALUES ('".$get_referred."', '".$get_userip."', '".$get_date."', '".$get_useragent."')");
				}
		}
	}

	function wp_statistics_not_enable() {
		$get_bloginfo_url = get_admin_url() . "admin.php?page=wp-statistics";
		echo '<div class="error"><p>'.sprintf(__('WP-Statistics not enabled! Please go to <a href="%s">setting page</a> and enable statistics', 'wp_statistics'), $get_bloginfo_url).'</p></div>';
	}

	if(get_option('enable_stats')) {
		wp_statistics();
	} else {
		add_action('admin_notices', 'wp_statistics_not_enable');
	}

	function wp_statistics_useronline() {
		global $wpdb, $table_prefix, $get_date, $get_referred, $get_useragent, $get_userip;
		$timestamp = date("U");

		$get_time_useronline_s = get_option('time_useronline_s');

		if(!$get_time_useronline_s) {
			$get_time_useronline_s = '5'; // Default value for check accurate user online
		}

		$get_ip = $wpdb->get_var("SELECT * FROM {$table_prefix}statistics_useronline WHERE ip = '".$get_userip."'");
		if($get_ip) {
			$wpdb->query("UPDATE {$table_prefix}statistics_useronline SET timestamp = '".$timestamp."', time = '".$get_date."', referred = '".$get_referred."', agent = '".$get_useragent."' WHERE ip = '".$get_ip."'");
		} else {
			$wpdb->query("INSERT INTO {$table_prefix}statistics_useronline(ip, timestamp, time, referred, agent) VALUES ('".$get_userip."', '".$timestamp."', '".$get_date."', '".$get_referred."', '".$get_useragent."')");
		}

		$time = $timestamp - $get_time_useronline_s;
		$wpdb->get_var("DELETE FROM {$table_prefix}statistics_useronline WHERE timestamp < '".$time."'");
		
		$get_users = $wpdb->get_var("SELECT COUNT(ip) FROM {$table_prefix}statistics_useronline");
		return $get_users;
	}

	function wp_statistics_today() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT today FROM {$table_prefix}statistics_visits");

		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_yesterday() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT yesterday FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_week() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT week FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_month() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT month FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_year() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT year FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_total() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT total FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_searchengine($referred='') {
		global $wpdb, $table_prefix;
		if($referred == 'google') {
			return $wpdb->get_var("SELECT google FROM {$table_prefix}statistics_visits");
		} else if ($referred == 'yahoo') {
			return $wpdb->get_var("SELECT yahoo FROM {$table_prefix}statistics_visits");
		} else if ($referred == 'bing') {
			return $wpdb->get_var("SELECT bing FROM {$table_prefix}statistics_visits");
		} else {
			$total_referred = $wpdb->get_row("SELECT * FROM {$table_prefix}statistics_visits");
			return $total_referred->google + $total_referred->yahoo + $total_referred->bing;
		}
	}

	function wp_statistics_useronline_live(){ global $database_checktime; ?>
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.4.4.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){
				$("span#show_useronline_live").load("<?php echo plugin_dir_url( __FILE__ );?>/wp-statistics-useronline-live.php");
				setInterval(function(){
					$("span#show_useronline_live").fadeOut(100);
					$("span#show_useronline_live").load("<?php echo plugin_dir_url( __FILE__ );?>/wp-statistics-useronline-live.php");
					$("span#show_useronline_live").fadeIn(100);
				}, <?php echo $database_checktime ?>000);
			});
		</script>
		<span id="show_useronline_live"></span>
	<?php }

	function wp_statistics_total_live(){ global $database_checktime; ?>
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.4.4.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){
				$("span#show_totalvisit_live").load("<?php echo plugin_dir_url( __FILE__ );?>/wp-statistics-totalvisit-live.php");
				setInterval(function(){
					$("span#show_totalvisit_live").fadeOut(100);
					$("span#show_totalvisit_live").load("<?php echo plugin_dir_url( __FILE__ );?>/wp-statistics-totalvisit-live.php");
					$("span#show_totalvisit_live").fadeIn(100);
				}, <?php echo $database_checktime ?>000);
			});
		</script>
		<span id="show_totalvisit_live"></span>
	<?php }

	function wp_statistics_countposts() {
		$count_posts = wp_count_posts('post');
		return $count_posts->publish;
	}

	function wp_statistics_countpages() {
		$count_pages = wp_count_posts('page');
		return $count_pages->publish;
	}

	function wp_statistics_countcomment() {
		global $wpdb;
		$countcomms = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");
		if (0 < $countcomms) $countcomms = number_format($countcomms);
		return $countcomms;
	}

	function wp_statistics_countspam() {
		return number_format_i18n(get_option('akismet_spam_count'));
	}

	function wp_statistics_countusers() {
		$result = count_users();
		return $result['total_users'];
	}

	function wp_statistics_lastpostdate($type=english) {
		global $wpdb;
		$db_date = $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_type='post' AND post_status='publish' ORDER BY ID DESC LIMIT 1");
		$date_format = get_option('date_format');
		if ( $type == 'farsi' ) {
			return jdate($date_format, strtotime($db_date));
		} else {
			return date($date_format, strtotime($db_date));
		}
	}
	
	function wp_statistics_average_post() {
		global $wpdb;
		$get_first_post = $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY post_date LIMIT 1");
		$get_total_post = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");
		
		$days_spend = intval((time() - strtotime($get_first_post) ) / (60*60*24));
		return round($get_total_post / $days_spend, 2);
	}

	function wp_statistics_average_comment() {
		global $wpdb;
		$get_first_comment = $wpdb->get_var("SELECT comment_date FROM $wpdb->comments ORDER BY comment_date LIMIT 1");
		$get_total_comment = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");

		$days_spend = intval((time() - strtotime($get_first_comment) ) / (60*60*24));
		return round($get_total_comment / $days_spend, 2);
	}

	function wp_statistics_average_registeruser() {
		global $wpdb;
		$get_first_user = $wpdb->get_var("SELECT user_registered FROM $wpdb->users ORDER BY user_registered LIMIT 1");
		$get_total_user = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->users");

		$days_spend = intval((time() - strtotime($get_first_user) ) / (60*60*24));
		return round($get_total_user / $days_spend, 2);
	}

	// Show Count Feedburner Subscribe by Affiliate Marketer
	function wp_statistics_countsubscrib($feed_url) {
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
			update_option("feedrsscount",$feedcount);
		}
		return $feedcount['count'];
	}

	include_once("include/google_pagerank.php");
	include_once("include/alexa_pagerank.php");

	function wp_statistics_menu() {
		if (function_exists('add_options_page')) {
			add_menu_page(__('Statistics', 'wp_statistics'), __('Statistics', 'wp_statistics'), 'manage_options', 'wp-statistics', 'wp_statistics_config_permission', plugin_dir_url( __FILE__ ).'/images/icon.png');
			add_submenu_page( 'wp-statistics', __('Settings', 'wp_statistics'), __('Settings', 'wp_statistics'), 'manage_options', 'wp-statistics', 'wp_statistics_config_permission');
			add_submenu_page( 'wp-statistics', __('Stats Log', 'wp_statistics'), __('Stats Log', 'wp_statistics'), 'manage_options', 'wp-statistics/stats', 'wp_statistics_stats_permission');
			add_submenu_page( 'wp-statistics', __('Users Online', 'wp_statistics'), __('Users Online', 'wp_statistics'), 'manage_options', 'wp-statistics/online', 'wp_statistics_online_permission');
		}
	}
	add_action('admin_menu', 'wp_statistics_menu');

	function wp_statistics_config_permission() {
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.', 'wp_statistics') );

			settings_fields( 'wp_statistics_options' );
		}

		include_once('setting/setting.php');
	}

	function wp_statistics_stats_permission() {
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.', 'wp_statistics') );
		}

		include_once('setting/stats.php');
	}

	function wp_statistics_online_permission() {
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.', 'wp_statistics') );
		}

		include_once('setting/useronline.php');
	}

	add_shortcode('useronline',		'wp_statistics_useronline');
	add_shortcode('today',			'wp_statistics_today');
	add_shortcode('yesterday',		'wp_statistics_yesterday');
	add_shortcode('week',			'wp_statistics_week');
	add_shortcode('month',			'wp_statistics_month');
	add_shortcode('year',			'wp_statistics_year');
	add_shortcode('total',			'wp_statistics_total');
	add_shortcode('searchengine',	'wp_statistics_searchengine');
	add_shortcode('countposts',		'wp_statistics_countposts');
	add_shortcode('countpages',		'wp_statistics_countpages');
	add_shortcode('countcomments',	'wp_statistics_countcomment');
	add_shortcode('countspams',		'wp_statistics_countspam');
	add_shortcode('countusers',		'wp_statistics_countusers');
	add_shortcode('lastpostdate',	'wp_statistics_lastpostdate');
	add_shortcode('averagepost',	'wp_statistics_average_post');
	add_shortcode('averagecomment',	'wp_statistics_average_comment');
	add_shortcode('averageusers',	'wp_statistics_average_registeruser');
	add_shortcode('googlepagerank',	'wp_statistics_google_page_rank');
	add_shortcode('alexaRank',		'wp_statistics_alexaRank');

	add_filter('widget_text', 'do_shortcode');
?>