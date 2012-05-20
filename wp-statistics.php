<?php
/*
Plugin Name: WP-Statistics
Plugin URI: http://iran98.org/category/wordpress/wp-statistics/
Description: Summary statistics of blog.
Version: 2.2.7
Author: Mostafa Soufi
Author URI: http://iran98.org/
License: GPL2
*/
	include_once("widget.php");

	load_plugin_textdomain('wp_statistics','wp-content/plugins/wp-statistics/langs');

	add_action('admin_bar_menu', 'wp_statistics_menubar', 20);
	add_action('admin_menu', 'wp_statistics_menu');
	add_action("plugins_loaded", "wp_statistics_widget");

	register_activation_hook(__FILE__,'wp_statistics_install');
	register_activation_hook(__FILE__,'wp_statistics_options');

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
	if(!$database_checktime)
	{
		$database_checktime = "10";
	}

	/* Coefficient Visitor */
	$get_coefficient = get_option('coefficient_visitor');
	if(!$get_coefficient)
	{
		$get_coefficient = "1";
	}

	function wp_statistics_menubar()
	{
		global $wp_admin_bar;
		if (!is_super_admin() || !is_admin_bar_showing() || !get_option('enable_wps_adminbar'))
		{
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
		}
	}

	function wp_statistics_options()
	{
		update_option('enable_wps_adminbar', true);
	}

	function wp_statistics_install()
	{
		global $wp_statistics_db_version, $table_prefix;
		$table_visit	= $table_prefix."statistics_visits";
		$table_visitors	= $table_prefix."statistics_visitors";
		$table_profile	= $table_prefix."statistics_profile";
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

		$create_referr_table = ("CREATE TABLE ".$table_referred."
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
		dbDelta($create_referr_table);
	
		dbDelta($primary_visit_value);
		dbDelta($primary_date_value);

		add_option('wp_statistics_db_version', 'wp_statistics_db_version');
	}

	function wp_check_spider()
	{
		$spiders = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi",
		"looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
		"Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot",
		"crawler", "www.galaxy.com", "Googlebot", "googlebot", "Scooter", "Slurp",
		"msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz",
		"Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot",
		"Mediapartners-Google", "Sogou web spider", "WebAlta Crawler","TweetmemeBot",
		"Butterfly","Twitturls","Me.dium","Twiceler");

		foreach($spiders as $spider)
		{
			if(strpos($_SERVER['HTTP_USER_AGENT'], $spider) !== false)
			return true;
		}
		return false;
	}

	function wp_statistics()
	{
		global $wpdb, $table_prefix, $get_date, $get_now, $get_week, $get_month, $get_year, $get_referred, $get_userip, $get_useragent, $get_coefficient;

		$get_dates_row = $wpdb->get_row("SELECT * FROM {$table_prefix}statistics_date");

		if( ($get_dates_row->last_visit) != $get_date && !is_admin() && !wp_check_spider())
		{
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

		if(get_option('daily_referer'))
		{
			if( ($get_dates_row->last_counter) == $get_now )
			{
				if(strstr($get_referred, 'google.com'))
				{
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
		if(!$get_items_statistics)
		{
			$get_items_statistics = '5';
		}

		$get_num_reffered = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}statistics_reffered");
		if($get_num_reffered < $get_items_statistics)
		{
			$get_var_ip = $wpdb->get_var("SELECT ip FROM {$table_prefix}statistics_reffered WHERE ip = '".$get_userip."'");
				if(!$get_var_ip)
				{
					$wpdb->query("INSERT INTO {$table_prefix}statistics_reffered (referred, ip, time, agent) VALUES ('".$get_referred."', '".$get_userip."', '".$get_date."', '".$get_useragent."')");
				}
		}
	}

	function wp_statistics_not_enable()
	{
		$get_bloginfo_url = get_admin_url() . "admin.php?page=wp-statistics";
		echo '<div class="error"><p>'.sprintf(__('WP-Statistics not enabled! Please go to <a href="%s">setting page</a> and enable statistics', 'wp_statistics'), $get_bloginfo_url).'</p></div>';
	}

	if(get_option('enable_stats'))
	{
		add_action('wp_head', 'wp_statistics');
	} else {
		add_action('admin_notices', 'wp_statistics_not_enable');
	}

	/* Start: functions for user in theme */
	function wp_statistics_useronline()
	{
		global $wpdb, $table_prefix, $get_date, $get_referred, $get_useragent, $get_userip;
		$timestamp = date("U");

		$get_time_useronline_s = get_option('time_useronline_s');

		if(!$get_time_useronline_s)
		{
			$get_time_useronline_s = '60'; // Default value for check accurate user online
		}

		$get_ip = $wpdb->get_var("SELECT * FROM {$table_prefix}statistics_useronline WHERE ip = '".$get_userip."'");
		if($get_ip)
		{
			$wpdb->query("UPDATE {$table_prefix}statistics_useronline SET timestamp = '".$timestamp."', time = '".$get_date."', referred = '".$get_referred."', agent = '".$get_useragent."' WHERE ip = '".$get_ip."'");
		} else {
			$wpdb->query("INSERT INTO {$table_prefix}statistics_useronline(ip, timestamp, time, referred, agent) VALUES ('".$get_userip."', '".$timestamp."', '".$get_date."', '".$get_referred."', '".$get_useragent."')");
		}

		$time = $timestamp - $get_time_useronline_s;
		$wpdb->get_var("DELETE FROM {$table_prefix}statistics_useronline WHERE timestamp < '".$time."'");
		
		$get_users = $wpdb->get_var("SELECT COUNT(ip) FROM {$table_prefix}statistics_useronline");
		return $get_users;
	}

	function wp_statistics_today()
	{
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT today FROM {$table_prefix}statistics_visits");

		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_yesterday()
	{
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT yesterday FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_week()
	{
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT week FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_month()
	{
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT month FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_year()
	{
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT year FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_total()
	{
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT total FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals'))
		{
			return number_format($get_var);
		} else {
			return $get_var;
		}
	}

	function wp_statistics_searchengine($referred='')
	{
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

	function wp_statistics_countposts()
	{
		$count_posts = wp_count_posts('post');
		return $count_posts->publish;
	}

	function wp_statistics_countpages()
	{
		$count_pages = wp_count_posts('page');
		return $count_pages->publish;
	}

	function wp_statistics_countcomment()
	{
		global $wpdb;
		$countcomms = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");
		if (0 < $countcomms) $countcomms = number_format($countcomms);
		return $countcomms;
	}

	function wp_statistics_countspam()
	{
		return number_format_i18n(get_option('akismet_spam_count'));
	}

	function wp_statistics_countusers()
	{
		$result = count_users();
		return $result['total_users'];
	}

	function wp_statistics_lastpostdate($type=english)
	{
		global $wpdb;
		$db_date = $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_type='post' AND post_status='publish' ORDER BY ID DESC LIMIT 1");
		$date_format = get_option('date_format');
		if ( $type == 'farsi' )
		{
			return jdate($date_format, strtotime($db_date));
		} else {
			return date($date_format, strtotime($db_date));
		}
	}
	
	function wp_statistics_average_post()
	{
		global $wpdb;
		$get_first_post = $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY post_date LIMIT 1");
		$get_total_post = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");
		
		$days_spend = intval((time() - strtotime($get_first_post) ) / (60*60*24));
		return round($get_total_post / $days_spend, 2);
	}

	function wp_statistics_average_comment()
	{
		global $wpdb;
		$get_first_comment = $wpdb->get_var("SELECT comment_date FROM $wpdb->comments ORDER BY comment_date LIMIT 1");
		$get_total_comment = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");

		$days_spend = intval((time() - strtotime($get_first_comment) ) / (60*60*24));
		return round($get_total_comment / $days_spend, 2);
	}

	function wp_statistics_average_registeruser()
	{
		global $wpdb;
		$get_first_user = $wpdb->get_var("SELECT user_registered FROM $wpdb->users ORDER BY user_registered LIMIT 1");
		$get_total_user = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->users");

		$days_spend = intval((time() - strtotime($get_first_user) ) / (60*60*24));
		return round($get_total_user / $days_spend, 2);
	}

	// Show Count Feedburner Subscribe by Affiliate Marketer
	function wp_statistics_countsubscrib($feed_url)
	{
		$feedcount = get_option("feedrsscount");
		if ($feedcount['lastcheck'] < (mktime()-3600))
		{
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

	include("include/google_pagerank.php");
	include("include/alexa_pagerank.php");

	/* End: functions for user in theme */

	function wp_statistics_menu()
	{
		if (function_exists('add_options_page'))
		{
			add_menu_page(__('Statistics', 'wp_statistics'), __('Statistics', 'wp_statistics'), 'manage_options', 'wp-statistics', 'wp_statistics_config_permission', plugin_dir_url( __FILE__ ).'/images/icon.png');
			add_submenu_page( 'wp-statistics', __('Settings', 'wp_statistics'), __('Settings', 'wp_statistics'), 'manage_options', 'wp-statistics', 'wp_statistics_config_permission');
			add_submenu_page( 'wp-statistics', __('Stats Log', 'wp_statistics'), __('Stats Log', 'wp_statistics'), 'manage_options', 'wp-statistics/stats', 'wp_statistics_stats_permission');
			add_submenu_page( 'wp-statistics', __('Users Online', 'wp_statistics'), __('Users Online', 'wp_statistics'), 'manage_options', 'wp-statistics/online', 'wp_statistics_online_permission');
		}
	}

	function wp_statistics_config_permission()
	{
		if (!current_user_can('manage_options'))
		{
			wp_die( __('You do not have sufficient permissions to access this page.', 'wp_statistics') );

			settings_fields( 'wp_statistics_options' );
			function register_mysettings()
			{
				register_setting('wp_statistics_options', 'enable_stats');
				register_setting('wp_statistics_options', 'enable_decimals');
				register_setting('wp_statistics_options', 'enable_wps_adminbar');
				register_setting('wp_statistics_options', 'time_useronline_s');
				register_setting('wp_statistics_options', 'items_statistics');
				register_setting('wp_statistics_options', 'coefficient_visitor');
				register_setting('wp_statistics_options', 'pagerank_google_url');
				register_setting('wp_statistics_options', 'pagerank_alexa_url');
			}
		}?>

		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery("span#increase_total_visit").click(function()
				{
					var total_increase_value = jQuery("input#increase_total_visit").val();
					jQuery("input#increase_total_visit").attr("disabled", "disabled");
					jQuery("span#increase_total_visit").attr("disabled", "disabled");
					jQuery("div#result_increase_total_visit").html("<img src='<?php echo plugin_dir_url( __FILE__ ); ?>images/loading.gif'/>");
					jQuery.post("<?php echo plugin_dir_url( __FILE__ );?>/actions.php",{increase_value:total_increase_value},function(result){
					jQuery("div#result_increase_total_visit").html(result);
					jQuery("input#increase_total_visit").removeAttr("disabled");
					jQuery("span#increase_total_visit").removeAttr("disabled");
					});
				});

				jQuery("span#reduction_total_visit").click(function()
				{
					var total_reduction_value = jQuery("input#reduction_total_visit").val();
					jQuery("input#reduction_total_visit").attr("disabled", "disabled");
					jQuery("span#reduction_total_visit").attr("disabled", "disabled");
					jQuery("div#result_reduction_total_visit").html("<img src='<?php echo plugin_dir_url( __FILE__ ); ?>images/loading.gif'/>");
					jQuery.post("<?php echo plugin_dir_url( __FILE__ );?>/actions.php",{reduction_value:total_reduction_value},function(result){
					jQuery("div#result_reduction_total_visit").html(result);
					jQuery("input#reduction_total_visit").removeAttr("disabled");
					jQuery("span#reduction_total_visit").removeAttr("disabled");
					});
				});

				jQuery("span#show_function").click(function()
				{
					jQuery("div#report_problem").slideUp(1000);
					jQuery("ul#functions_list").slideDown(1000, function()
					{
						jQuery("ul#functions_list code").fadeIn(1000);
					});
				});
				
				jQuery("span#hide_function").click(function()
				{
					jQuery("ul#functions_list").slideUp(1000);
				});	

				jQuery("span#hide_report").click(function()
				{
					jQuery("div#report_problem").slideUp(1000);
				});

				jQuery("span#report_problem").click(function()
				{
					jQuery("ul#functions_list").slideUp(1000);
					jQuery("div#report_problem").slideDown(1000);
				});

				jQuery("span#send_report").click(function()
				{
					var your_name = jQuery("input#your_name").val();
					var your_report = jQuery("textarea#your_report").val();
					jQuery("div#result_problem").html("<img src='<?php echo plugin_dir_url( __FILE__ ); ?>images/loading.gif'/>");
					jQuery("div#result_problem").load("<?php echo plugin_dir_url( __FILE__ );?>/report_problem.php", {y_name:your_name, d_report:your_report});
				});

				jQuery("span#uninstall").click(function()
				{
					var uninstall = confirm("<?php _e('Are you sure?', 'wp_statistics'); ?>");

					if(uninstall)
					{
						jQuery("div#result_uninstall").html("<img src='<?php echo plugin_dir_url( __FILE__ ); ?>images/loading.gif'/>");
						jQuery("div#result_uninstall").load('<?php echo plugin_dir_url(__FILE__); ?>/uninstall.php');
					}
				});
			});
		</script>

		<div class="wrap">
			<h2><img src="<?php echo plugin_dir_url( __FILE__ ); ?>/images/icon_big.png"/> <?php _e('Configuration', 'wp_statistics'); ?></h2>
		<table class="form-table">
		<form method="post" action="options.php">
		<?php wp_nonce_field('update-options');?>
			<tr style="background-color:#EEEEEE; border:1px solid #DDDDDD;">
				<td width="250"><?php _e('Enable Statistics', 'wp_statistics'); ?>:</td>
				<td width="200">
					<?php $get_enable_stats = get_option('enable_stats'); ?>
					<input type="checkbox" name="enable_stats" id="enable_stats" <?php echo $get_enable_stats==true? "checked='checked'" : '';?>/>
					<label for="enable_stats"><?php _e('Yes', 'wp_statistics'); ?></label>
				</td>
				<td>
					<?php if($get_enable_stats) { ?>
					<span style="font-size:11px; color:#009900;">(<?php _e('Statistics are enabled.', 'wp_statistics'); ?>)</span>
					<?php } else { ?>
					<span style="font-size:11px; color:#FF0000;">(<?php _e('Statistics are disabled!', 'wp_statistics'); ?>)</span>
					<?php } ?>
				</td>
			</tr>

			<tr><th><h3><?php _e('General configuration', 'wp_statistics'); ?></h4></th></tr>

			<tr>
				<td><?php _e('Show decimals number', 'wp_statistics'); ?>:</td>	
				<td>
					<?php $get_enable_stats = get_option('enable_decimals'); ?>
					<input type="checkbox" name="enable_decimals" id="enable_decimals" <?php echo $get_enable_stats==true? "checked='checked'" : '';?>/>
					<label for="enable_decimals"><?php _e('Yes', 'wp_statistics'); ?></label>
				</td>
				<td><span style="font-size:11px;">(<?php _e('Show number stats with decimal. For examle: 3,500', 'wp_statistics'); ?>)</span></td>
			</tr>

			<tr>
				<td><?php _e('Show stats in menu bar', 'wp_statistics'); ?>:</td>	
				<td>
					<?php $get_enable_wps_adminbar = get_option('enable_wps_adminbar'); ?>
					<input type="checkbox" name="enable_wps_adminbar" id="enable_wps_adminbar" <?php echo $get_enable_wps_adminbar==true? "checked='checked'" : '';?>/>
					<label for="enable_wps_adminbar"><?php _e('Yes', 'wp_statistics'); ?></label>
				</td>
				<td><span style="font-size:11px;">(<?php _e('Show stats in admin menu bar', 'wp_statistics'); ?>)</span></td>
			</tr>

			<tr>
				<td><?php _e('Daily referer of search engines', 'wp_statistics'); ?>:</td>	
				<td>
					<input type="checkbox" name="daily_referer" id="daily_referer" <?php echo get_option('daily_referer') == true ? "checked='checked'" : '';?>/>
					<label for="daily_referer"><?php _e('Yes', 'wp_statistics'); ?></label>
				</td>
				<td><span style="font-size:11px;">(<?php _e('Can be calculated daily or total search engines', 'wp_statistics'); ?>)</span></td>
			</tr>

			<tr>
				<td><?php _e('Check for online users every', 'wp_statistics'); ?>:</td>
				<td>
					<input type="text" name="time_useronline_s" style="direction:ltr; width:60px" maxlength="3" value="<?php echo get_option('time_useronline_s'); ?>"/>
					<span style="font-size:10px;"><?php _e('Second', 'wp_statistics'); ?></span>
				</td>
				<td><span style="font-size:11px;">(<?php _e('Time for the check accurate online user in the site. Default: 60 Second', 'wp_statistics'); ?>)</span></td>
			</tr>

			<tr>
				<td><?php _e('Increase value of the total hits by', 'wp_statistics'); ?>:</td>
				<td>
					<input type="text" name="increase_total_visit" id="increase_total_visit" style="direction:ltr; width:100px" maxlength="10"/>
					<span class="button" id="increase_total_visit" style="width:50px;"><?php _e('Done', 'wp_statistics'); ?></span>
					<div id="result_increase_total_visit" style="font-size:11px;"></div>
				</td>
				<td><span style="font-size:11px;">(<?php _e('Your total visit sum with this value', 'wp_statistics'); ?>)</span></td>
			</tr>

			<tr>
				<td><?php _e('Reduce value of the total hits by', 'wp_statistics'); ?>:</td>
				<td>
					<input type="text" name="reduction_total_visit" id="reduction_total_visit" style="direction:ltr; width:100px" maxlength="10"/>
					<span class="button" id="reduction_total_visit" style="width:50px;"><?php _e('Done', 'wp_statistics'); ?></span>
					<div id="result_reduction_total_visit" style="font-size:11px;"></div>
				</td>
				<td><span style="font-size:11px;">(<?php _e('Your total visit minus with this value', 'wp_statistics'); ?>)</span></td>
			</tr>

			<tr>
				<td><?php _e('Number item for show Statistics', 'wp_statistics'); ?>:</td>
				<td>
					<input type="text" name="items_statistics" style="direction:ltr; width:70px" maxlength="3" value="<?php echo get_option('items_statistics'); ?>"/>
					<span style="font-size:10px;"><?php _e('Default 5', 'wp_statistics'); ?></span>
				</td>
				<td><span style="font-size:11px;">(<?php _e('Number for submit item in Database and show that', 'wp_statistics'); ?>)</span></td>
			</tr>

			<tr>
				<td><?php _e('Coefficient per visitor', 'wp_statistics'); ?>:</td>
				<td>
					<input type="text" name="coefficient_visitor" style="direction:ltr; width:70px" maxlength="3" value="<?php echo get_option('coefficient_visitor'); ?>"/>
					<span style="font-size:10px;"><?php _e('Default 1', 'wp_statistics'); ?></span>
				</td>
				<td><span style="font-size:11px;">(<?php _e('For each visitor to account for several hits.', 'wp_statistics'); ?>)</span></td>
			</tr>

			<tr><th><h3><?php _e('Live Statistics configuration', 'wp_statistics'); ?></h4></th></tr>

			<tr>
				<td><?php _e('Refresh Stats every', 'wp_statistics'); ?>:</td>
				<td>
					<input type="text" name="database_checktime" style="direction:ltr; width:60px" maxlength="3" value="<?php echo get_option('database_checktime'); ?>"/>
					<span style="font-size:10px;"><?php _e('Second(s)', 'wp_statistics'); ?></span>
				</td>
				<td>
					<span style="font-size:11px; color:#FF0000;"><?php _e('Recommended', 'wp_statistics'); ?></span>
					<span style="font-size:11px;">(<?php _e('To reduce pressure on the server, this defaults to 10 sec', 'wp_statistics'); ?>.)</span>
				</td>
			</tr>

			<tr><th><h3><?php _e('Pagerank configuration', 'wp_statistics'); ?></h4></th></tr>

			<tr>
				<td><?php _e('Your url for Google pagerank check', 'wp_statistics'); ?>:</td>
				<td>
					<input type="text" name="pagerank_google_url" style="direction:ltr; width:200px" value="<?php echo get_option('pagerank_google_url'); ?>"/>
				</td>
				<td>
					<span style="font-size:11px;">(<?php _e('If empty. you website url is used', 'wp_statistics'); ?>)</span>
				</td>
			</tr>

			<tr>
				<td><?php _e('Your url for Alexa pagerank check', 'wp_statistics'); ?>:</td>
				<td>
					<input type="text" name="pagerank_alexa_url" style="direction:ltr; width:200px" value="<?php echo get_option('pagerank_alexa_url'); ?>"/>
				</td>
				<td>
					<span style="font-size:11px;">(<?php _e('If empty. you website url is used', 'wp_statistics'); ?>)</span>
				</td>
			</tr>

			<tr>
				<td>
					<p class="submit">
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="enable_stats,enable_decimals,enable_wps_adminbar,daily_referer,time_useronline_s,items_statistics,coefficient_visitor,database_checktime,pagerank_google_url,pagerank_alexa_url" />
					<input type="submit" class="button-primary" name="Submit" value="<?php _e('Update', 'wp_statistics'); ?>" />
					</p>
				</td>
			</tr>

			<tr>
			<th colspan="3">
				<?php _e('This plugin created by', 'wp_statistics'); ?> <a href="http://profiles.wordpress.org/mostafa.s1990/">Mostafa Soufi</a> <?php _e('from', 'wp_statistics'); ?> <a href="http://www.webstudio.ir">Web Studio</a> & <a href="http://wpbazar.com">WPBazar</a> <?php _e('group', 'wp_statistics'); ?>.

				<h3><?php _e('Plugin translators', 'wp_statistics'); ?></h3>
				<ul>
				
				<ul>
					<li><?php _e('Language', 'wp_statistics'); ?> Portuguese <?php _e('by', 'wp_statistics'); ?><a a href="http://www.musicalmente.info/"> musicalmente</a></li>
					<li><?php _e('Language', 'wp_statistics'); ?> Romanian <?php _e('by', 'wp_statistics'); ?> <a href="http://www.nobelcom.com/">Luke Tyler</a></li>
					<li><?php _e('Language', 'wp_statistics'); ?> French <?php _e('by', 'wp_statistics'); ?> <a href="mailto:gnanice@gmail.com">Anice Gnampa</a></li>
					<li><?php _e('Language', 'wp_statistics'); ?> Russian <?php _e('by', 'wp_statistics'); ?> <a href="http://www.iflexion.com/">Igor Dubilej</a></li>
					<li><?php _e('Language', 'wp_statistics'); ?> Spanish <?php _e('by', 'wp_statistics'); ?> <a href="mailto:joanfusan@gmail.com">jose</a></li>
					<li><?php _e('Language', 'wp_statistics'); ?> Arabic <?php _e('by', 'wp_statistics'); ?> <a href="http://www.facebook.com/aboHatim">Hammad Shammari</a></li>
					<li><?php _e('Language', 'wp_statistics'); ?> German <?php _e('by', 'wp_statistics'); ?> <a href="http://www.andreasmartin.com/">Andreas Martin</a></li>
				</ul>
				<?php _e('for translate language files. please send files for', 'wp_statistics'); ?> <code>mst404@gmail.com</code>

					<p style="padding-top: 5px;">
						<span class="button" id="show_function"><?php _e('Show Functions', 'wp_statistics'); ?></span>
						<span class="button" id="report_problem"><?php _e('Report Problem', 'wp_statistics'); ?></span>
					</p>

				<style>
					a{text-decoration: none}
					ul#functions_list code{border-radius:5px; padding:5px; display:none; width:400px; text-align:left; float:left; direction:ltr;}
					ul#functions_list{list-style-type: decimal; margin: 20px; display:none;}
					ul#functions_list li{line-height: 25px; width: 200px;}
					div#report_problem{display: none;}
				</style>
				<ul id="functions_list">
					<table>
						<tr>
							<td><?php _e('User Online Live', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_useronline_live(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Total Visit Live', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_total_live(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('User Online', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_useronline(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Today Visit', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_today(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Yesterday visit', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_yesterday(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Week Visit', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_week(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Month Visit', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_month(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Years Visit', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_year(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Total Visit', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_total(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Search Engine reffered', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_searchengine(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Total Posts', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_countposts(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Total Pages', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_countpages(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Total Comments', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_countcomment(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Total Spams', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_countspam(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Total Users', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_countusers(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Last Post Date', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_lastpostdate(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Average Posts', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_average_post(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Average Comments', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_average_comment(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Average Users', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_average_registeruser(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Total Feedburner Subscribe', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_countsubscrib("feedburneraddress"); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Google Pagerank', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_google_page_rank(); ?>'); ?></code></td>
						</tr>
						<tr>
							<td><?php _e('Alexa Pagerank', 'wp_statistics'); ?></td>
							<td><code><?php highlight_string('<?php echo wp_statistics_alexaRank(); ?>'); ?></code></td>
						</tr>
					</table>	
					<br /><span class="button" id="hide_function"><?php _e('Hide', 'wp_statistics'); ?></span>
				</ul>
			
				<div id="report_problem">
						<p><?php _e('Your Name', 'wp_statistics'); ?>:<br /><input type="text" name="your_name" id="your_name"/></p>

						<p><?php _e('Description Problem', 'wp_statistics'); ?>:<br /><textarea name="your_report" id="your_report"/></textarea></p>
						<div id="result_problem"></div>
					<br />
					<span class="button" id="send_report"><?php _e('Send Problem', 'wp_statistics'); ?></span>
					<span class="button" id="hide_report"><?php _e('Hide', 'wp_statistics'); ?></span>
				</div>
			</th>
			</tr>

			<tr>
				<th>
					<h3><?php _e('Unistall plugin', 'wp_statistics'); ?></h4>
				</th>
			</tr>

			<tr>
				<th colspan="3">
					<?php _e('Delete all data, including tables and plugin options', 'wp_statistics'); ?>
					<span class="button" id="uninstall"><?php _e('Uninstall', 'wp_statistics'); ?></span>
					<div id="result_uninstall"></div>
				</th>
			</tr>
		</form>	
		</table>
		</div>
		<?php
	}

	function wp_statistics_stats_permission()
	{
		if (!current_user_can('manage_options')) 
		{
			wp_die( __('You do not have sufficient permissions to access this page.', 'wp_statistics') );
		}

		?>
		<div class="wrap">
			<h2><img src="<?php echo plugin_dir_url( __FILE__ ); ?>/images/icon_big.png"/> <?php _e('Stats weblog', 'wp_statistics'); ?></h2>
			<table class="widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th id="cb" scope="col" class="manage-column column-cb check-column"></th>
						<th scope="col" class="manage-column column-name" width="5%"><?php _e('No', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="10%"><?php _e('IP', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="10%"><?php _e('Time', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="40%"><?php _e('Referred', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="30%"><?php _e('Agent', 'wp_statistics'); ?></th>
					</tr>
				</thead>
			
				<tbody>
					<?php
					global $wpdb, $table_prefix;
					$get_result = $wpdb->get_results("SELECT * FROM {$table_prefix}statistics_reffered");

					if(count($get_result ) > 0)
					{
						foreach($get_result as $gets)
						{
							$i++;
					?>
					<tr class="<?php echo $i % 2 == 0 ? 'alternate':'author-self'; ?>" valign="middle" id="link-2">
						<th class="check-column" scope="row"></th>
						<td class="column-name"><?php echo $i; ?></td>
						<td class="column-name"><?php echo $gets->ip; ?></td>
						<td class="column-name"><?php echo $gets->time; ?></td>
						<td class="column-name"><a href="<?php echo $gets->referred; ?>" target="_blank"><?php echo $gets->referred; ?></a></td>
						<td class="column-name"><?php echo $gets->agent; ?></td>
					</tr>
					<?php
						}
					} else { ?>
						<tr>
							<td colspan="6"><?php _e('Not Found!', 'wp_statistics'); ?></td>
						</tr>
					<?php } ?>
				</tbody>

				<tfoot>
					<tr>
						<th id="cb" scope="col" class="manage-column column-cb check-column"></th>
						<th scope="col" class="manage-column column-name" width="5%"><?php _e('No', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="10%"><?php _e('IP', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="10%"><?php _e('Time', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="40%"><?php _e('Referred', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="30%"><?php _e('Agent', 'wp_statistics'); ?></th>
					</tr>
				</tfoot>
			</table>
		</div>
		<?php
	}

	function wp_statistics_online_permission()
	{
		if (!current_user_can('manage_options')) 
		{
			wp_die( __('You do not have sufficient permissions to access this page.', 'wp_statistics') );
		}
		?>
		<div class="wrap">
			<h2><img src="<?php echo plugin_dir_url( __FILE__ ); ?>/images/icon_big.png"/> <?php _e('User Online', 'wp_statistics'); ?></h2>
			<table class="widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th id="cb" scope="col" class="manage-column column-cb check-column"></th>
						<th scope="col" class="manage-column column-name" width="5%"><?php _e('No', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="10%"><?php _e('IP', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="10%"><?php _e('Time', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="40%"><?php _e('Referred', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="30%"><?php _e('Agent', 'wp_statistics'); ?></th>
					</tr>
				</thead>
			
				<tbody>
					<?php
					global $wpdb, $table_prefix;
					$get_result = $wpdb->get_results("SELECT * FROM {$table_prefix}statistics_useronline");

					if(count($get_result ) > 0)
					{
						foreach($get_result as $gets)
						{
							$i++;
					?>
					<tr class="<?php echo $i % 2 == 0 ? 'alternate':'author-self'; ?>" valign="middle" id="link-2">
						<th class="check-column" scope="row"></th>
						<td class="column-name"><?php echo $i; ?></td>
						<td class="column-name"><?php echo $gets->ip; ?></td>
						<td class="column-name"><?php echo $gets->time; ?></td>
						<td class="column-name"><a href="<?php echo $gets->referred; ?>" target="_blank"><?php echo $gets->referred; ?></a></td>
						<td class="column-name"><?php echo $gets->agent; ?></td>
					</tr>
					<?php
						}
					} else { ?>
						<tr>
							<td colspan="6"><?php _e('Not Found!', 'wp_statistics'); ?></td>
						</tr>
					<?php } ?>
				</tbody>

				<tfoot>
					<tr>
						<th id="cb" scope="col" class="manage-column column-cb check-column"></th>
						<th scope="col" class="manage-column column-name" width="5%"><?php _e('No', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="10%"><?php _e('IP', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="10%"><?php _e('Time', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="40%"><?php _e('Referred', 'wp_statistics'); ?></th>
						<th scope="col" class="manage-column column-name" width="30%"><?php _e('Agent', 'wp_statistics'); ?></th>
					</tr>
				</tfoot>
			</table>
		</div>
		<?php
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