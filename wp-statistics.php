<?php
/*
Plugin Name: WP-Statistics
Plugin URI: http://iran98.org/category/wordpress/wp-statistics/
Description: Summary statistics of blog.
Version: 2.1.2
Author: Mostafa Soufi
Author URI: http://iran98.org/
License: GPL2
*/

	load_plugin_textdomain('wp_statistics','wp-content/plugins/wp-statistics/langs');
	add_action("plugins_loaded", "wp_statistics_widget");
	add_action('admin_menu', 'wp_statistics_menu');
	register_activation_hook(__FILE__,'wp_statistics_install');

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

		$create_referr_table = ("CREATE TABLE ".$table_referred."
				(referred text,
				ip char(20),
				time DATETIME,
				agent char(255))");

		if($create_visit_table) {
			$primary_visit_value = ("INSERT INTO ".$table_visit."
							(today, yesterday, week, month, year, total, google, yahoo, bing) VALUES
							(0, 0, 0, 0, 0, 0, 0, 0, 0)");

			$primary_date_value = ("INSERT INTO ".$table_dates."
							(last_counter, last_week, last_month, last_year, timestamp, last_visit) VALUES
							('00-00-00', '".$get_week."', '".$get_month."', '".$get_year."', '".$time_1."', '".$get_date."')");
		}					

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		dbDelta($create_visit_table);
		dbDelta($create_dates_table);
		dbDelta($create_users_table);
		dbDelta($create_referr_table);
	
		dbDelta($primary_visit_value);
		dbDelta($primary_date_value);

		add_option('wp_statistics_db_version', 'wp_statistics_db_version');
	}

	function wp_statistics() {
		global $wpdb, $table_prefix, $get_date, $get_now, $get_week, $get_month, $get_year, $get_referred, $get_userip, $get_useragent;

		$get_dates_row = $wpdb->get_row("SELECT * FROM {$table_prefix}statistics_date");

		if( ($get_dates_row->last_visit) != $get_date ) {
			if( ($get_dates_row->last_counter) == $get_now ) {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET today = today+1, total = total+1");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_visit = '".$get_date."'");
			} else {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET yesterday = today, total = total+1");
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET today = 0");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_counter = '".$get_now."', last_visit = '".$get_date."'");
			}
			if( ($get_dates_row->last_week) == $get_week ) {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET week = week+1");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_week = '".$get_week."'");
			} else {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET week = 0");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_week = '".$get_week."'");
			}
			if( ($get_dates_row->last_month) == $get_month ) {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET month = month+1");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_month = '".$get_month."'");
			} else {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET month = 0");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_month = '".$get_month."'");
			}
			if( ($get_dates_row->last_year) == $get_year ) {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET year = year+1");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_year = '".$get_year."'");
			} else {
				$wpdb->query("UPDATE {$table_prefix}statistics_visits SET year = 0");
				$wpdb->query("UPDATE {$table_prefix}statistics_date SET last_year = '".$get_year."'");
			}
		}

		if(strstr($get_referred, 'google.com')) {
			$wpdb->query("UPDATE {$table_prefix}statistics_visits SET google = google+1");
		} else if(strstr($get_referred, 'yahoo.com')) {
			$wpdb->query("UPDATE {$table_prefix}statistics_visits SET yahoo = yahoo+1");
		} else if(strstr($get_referred, 'bing.com')) {
			$wpdb->query("UPDATE {$table_prefix}statistics_visits SET bing = bing+1");
		}

		$get_items_statistics = get_option('items_statistics');
		if(!$get_items_statistics) {
			$get_items_statistics = '5';
		}

		$get_num_reffered = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}statistics_reffered");
		if($get_num_reffered < $get_items_statistics) {
			$get_var_ip = $wpdb->get_var("SELECT ip FROM {$table_prefix}statistics_reffered WHERE ip = '".$get_userip."'");
				if(!$get_var_ip) {
					$wpdb->query("INSERT INTO {$table_prefix}statistics_reffered (referred, ip, time, agent) VALUES ('".$get_referred."', '".$get_userip."', '".$get_date."', '".$get_useragent."')");
				}
		}
	}
	$get_enable_stats = get_option('enable_stats');
	if($get_enable_stats) {
		add_action('wp_head', 'wp_statistics');
	}

	/* Start: functions for user in theme */
	function wp_statistics_useronline() {
		global $wpdb, $table_prefix, $get_date, $get_referred, $get_useragent, $get_userip;
		$timestamp = date("U");

		$get_time_useronline = get_option('time_useronline');

		if(!$get_time_useronline) {
			$get_time_useronline = '1'; // Default value for check accurate user online
		} $get_time_useronline = $get_time_useronline * 60;

		$get_ip = $wpdb->get_var("SELECT * FROM {$table_prefix}statistics_useronline WHERE ip = '".$get_userip."'");
		if($get_ip) {
			$wpdb->query("UPDATE {$table_prefix}statistics_useronline SET timestamp = '".$timestamp."', time = '".$get_date."', referred = '".$get_referred."', agent = '".$get_useragent."' WHERE ip = '".$get_ip."'");
		} else {
			$wpdb->query("INSERT INTO {$table_prefix}statistics_useronline(ip, timestamp, time, referred, agent) VALUES ('".$get_userip."', '".$timestamp."', '".$get_date."', '".$get_referred."', '".$get_useragent."')");
		}

		$time = $timestamp - $get_time_useronline;
		$wpdb->get_var("DELETE FROM {$table_prefix}statistics_useronline WHERE timestamp < '".$time."'");
		
		$get_users = $wpdb->get_var("SELECT COUNT(ip) FROM {$table_prefix}statistics_useronline");
		echo $get_users;
	}

	function wp_statistics_today() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT today FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals')) {
			echo number_format($get_var);
		} else {
			echo $get_var;
		}

		if (!$get_enable_stats) {
			echo "<span style='font-size:10px;color:#FF0000'> ".__('(Disable)', 'wp_statistics')."</span>";
		}
	}

	function wp_statistics_yesterday() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT yesterday FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals')) {
			echo number_format($get_var);
		} else {
			echo $get_var;
		}

		if (!$get_enable_stats) {
			echo "<span style='font-size:10px;color:#FF0000'> ".__('(Disable)', 'wp_statistics')."</span>";
		}
	}

	function wp_statistics_week() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT week FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals')) {
			echo number_format($get_var);
		} else {
			echo $get_var;
		}

		if (!$get_enable_stats) {
			echo "<span style='font-size:10px;color:#FF0000'> ".__('(Disable)', 'wp_statistics')."</span>";
		}
	}

	function wp_statistics_month() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT month FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals')) {
			echo number_format($get_var);
		} else {
			echo $get_var;
		}

		if (!$get_enable_stats) {
			echo "<span style='font-size:10px;color:#FF0000'> ".__('(Disable)', 'wp_statistics')."</span>";
		}
	}

	function wp_statistics_year() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT year FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals')) {
			echo number_format($get_var);
		} else {
			echo $get_var;
		}

		if (!$get_enable_stats) {
			echo "<span style='font-size:10px;color:#FF0000'> ".__('(Disable)', 'wp_statistics')."</span>";
		}
	}

	function wp_statistics_total() {
		global $wpdb, $table_prefix, $get_enable_stats;
		$get_var =  $wpdb->get_var("SELECT total FROM {$table_prefix}statistics_visits");
		
		if(get_option('enable_decimals')) {
			echo number_format($get_var);
		} else {
			echo $get_var;
		}

		if (!$get_enable_stats) {
			echo "<span style='font-size:10px;color:#FF0000'> ".__('(Disable)', 'wp_statistics')."</span>";
		}
	}

	function wp_statistics_searchengine($referred='') {
		global $wpdb, $table_prefix;
		if($referred == 'google') {
			echo $wpdb->get_var("SELECT google FROM {$table_prefix}statistics_visits");
		} else if ($referred == 'yahoo') {
			echo $wpdb->get_var("SELECT yahoo FROM {$table_prefix}statistics_visits");
		} else if ($referred == 'bing') {
			echo $wpdb->get_var("SELECT bing FROM {$table_prefix}statistics_visits");
		} else {
			$total_referred = $wpdb->get_row("SELECT * FROM {$table_prefix}statistics_visits");
			echo $total_referred->google + $total_referred->yahoo + $total_referred->bing;
		}
	}

	function wp_statistics_countposts($type=publish) {
		$count_posts = wp_count_posts();
		echo $count_posts->$type;
	}

	function wp_statistics_countpages() {
		$count_pages = wp_count_posts('page');
		echo $count_pages->publish;
	}

	function wp_statistics_countcomment() {
		global $wpdb;
		$countcomms = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");
		if (0 < $countcomms) $countcomms = number_format($countcomms);
		echo $countcomms;
	}

	function wp_statistics_countspam() {
		echo number_format_i18n(get_option('akismet_spam_count'));
	}

	function wp_statistics_countusers() {
		$result = count_users();
		echo $result['total_users'];
	}

	function wp_statistics_lastpostdate($type=english) {
		global $wpdb;
		$db_date = $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_type='post' AND post_status='publish' ORDER BY ID DESC LIMIT 1");
		$date_format = get_option('date_format');
		if ( $type == 'farsi' ) {
			echo jdate($date_format, strtotime($db_date));
		} else 
			echo date($date_format, strtotime($db_date)); 
	}
	
	function wp_statistics_average_post() {
		global $wpdb;
		$get_first_post = $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY post_date LIMIT 1");
		$get_total_post = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");
		
		$days_spend = intval((time() - strtotime($get_first_post) ) / (60*60*24));
		echo $get_total_post / $days_spend;
	}

	function wp_statistics_average_comment() {
		global $wpdb;
		$get_first_comment = $wpdb->get_var("SELECT comment_date FROM $wpdb->comments ORDER BY comment_date LIMIT 1");
		$get_total_comment = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");

		$days_spend = intval((time() - strtotime($get_first_comment) ) / (60*60*24));
		echo $get_total_comment / $days_spend;
	}

	function wp_statistics_average_registeruser() {
		global $wpdb;
		$get_first_user = $wpdb->get_var("SELECT user_registered FROM $wpdb->users ORDER BY user_registered LIMIT 1");
		$get_total_user = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->users");

		$days_spend = intval((time() - strtotime($get_first_user) ) / (60*60*24));
		echo $get_total_user / $days_spend;
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
		echo $feedcount['count'];
		}

	include("include/google_pagerank.php");
	include("include/alexa_pagerank.php");

	/* End: functions for user in theme */

	function wp_statistics_menu() {
		if (function_exists('add_options_page')) {
		add_menu_page(__('Statistics', 'wp_statistics'), __('Statistics', 'wp_statistics'), 'manage_options', 'wp-statistics', 'wp_statistics_config_permission', plugin_dir_url( __FILE__ ).'/images/icon.png');
		add_submenu_page( 'wp-statistics', __('Stats weblog', 'wp_statistics'), __('Stats weblog', 'wp_statistics'), 'manage_options', 'wp-statistics/stats', 'wp_statistics_stats_permission');
		add_submenu_page( 'wp-statistics', __('User Online', 'wp_statistics'), __('User Online', 'wp_statistics'), 'manage_options', 'wp-statistics/online', 'wp_statistics_online_permission');
		}
	}

	function wp_statistics_config_permission() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.', 'wp_statistics') );

		settings_fields( 'wp_statistics_options' );
		function register_mysettings() {
			register_setting('wp_statistics_options', 'enable_stats');
			register_setting('wp_statistics_options', 'enable_decimals');
			register_setting('wp_statistics_options', 'time_useronline');
			register_setting('wp_statistics_options', 'items_statistics');
			register_setting('wp_statistics_options', 'pagerank_google_url');
			register_setting('wp_statistics_options', 'pagerank_alexa_url');
		}} ?>

	<script type="text/javascript" src="http://code.jquery.com/jquery-1.4.4.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("span#increase_total_visit").click(function(){
				var total_increase_value = $("input#increase_total_visit").val();
				$("input#increase_total_visit").attr("disabled", "disabled");
				$("span#increase_total_visit").attr("disabled", "disabled");
				$("div#result_increase_total_visit").html("<img src='<?php echo plugin_dir_url( __FILE__ ); ?>images/loading.gif'/>");
				$.post("<?php echo plugin_dir_url( __FILE__ );?>/actions.php",{increase_value:total_increase_value},function(result){
				$("div#result_increase_total_visit").html(result);
				$("input#increase_total_visit").removeAttr("disabled");
				$("span#increase_total_visit").removeAttr("disabled");
				});
			});

			$("span#reduction_total_visit").click(function(){
				var total_reduction_value = $("input#reduction_total_visit").val();
				$("input#reduction_total_visit").attr("disabled", "disabled");
				$("span#reduction_total_visit").attr("disabled", "disabled");
				$("div#result_reduction_total_visit").html("<img src='<?php echo plugin_dir_url( __FILE__ ); ?>images/loading.gif'/>");
				$.post("<?php echo plugin_dir_url( __FILE__ );?>/actions.php",{reduction_value:total_reduction_value},function(result){
				$("div#result_reduction_total_visit").html(result);
				$("input#reduction_total_visit").removeAttr("disabled");
				$("span#reduction_total_visit").removeAttr("disabled");
				});
			});

			$("span#show_function").click(function(){
				$("div#report_problem").hide(1000);
				$("ul#show_function").show(1000, function(){
					$("code").delay(1000).fadeIn(1000);
				});
			});
			
			$("span#hide_function").click(function(){
				$("ul#show_function").hide(1000);
			});	

			$("span#hide_report").click(function(){
				$("div#report_problem").hide(1000);
			});

			$("span#report_problem").click(function(){
				$("ul#show_function").hide(1000);
				$("div#report_problem").show(1000);
			});

			$("span#send_report").click(function(){
				var your_name = $("input#your_name").val();
				var your_report = $("textarea#your_report").val();
				$("div#result_problem").html("<img src='<?php echo plugin_dir_url( __FILE__ ); ?>images/loading.gif'/>");
				$("div#result_problem").load("<?php echo plugin_dir_url( __FILE__ );?>/report_problem.php", {y_name:your_name, d_report:your_report});
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
				<span style="font-size:11px; color:#009900;">(<?php _e('Statistics are disabled.', 'wp_statistics'); ?>)</span>
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
			<td><?php _e('Online user check time', 'wp_statistics'); ?>:</td>
			<td>
				<span style="font-size:10px;"><?php _e('Each', 'wp_statistics'); ?></span>
				<input type="text" name="time_useronline" style="direction:ltr; width:60px" maxlength="3" value="<?php echo get_option('time_useronline'); ?>"/>
				<span style="font-size:10px;"><?php _e('Compute min', 'wp_statistics'); ?></span>
			</td>
			<td><span style="font-size:11px;">(<?php _e('Time for the check accurate online user in the site. Default: 5 Minutes', 'wp_statistics'); ?>)</span></td>
		</tr>

		<tr>
			<td><?php _e('Increase value of the total hits', 'wp_statistics'); ?>:</td>
			<td>
				<input type="text" name="increase_total_visit" id="increase_total_visit" style="direction:ltr; width:100px" maxlength="10"/>
				<span class="button" id="increase_total_visit" style="width:50px;"><?php _e('Done', 'wp_statistics'); ?></span>
				<div id="result_increase_total_visit" style="font-size:11px;"></div>
			</td>
			<td><span style="font-size:11px;">(<?php _e('Your total visit sum with this value', 'wp_statistics'); ?>)</span></td>
		</tr>

		<tr>
			<td><?php _e('Reduction value of the total hits', 'wp_statistics'); ?>:</td>
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

		<tr><th><h3><?php _e('Live Statistics configuration', 'wp_statistics'); ?></h4></th></tr>

		<tr>
			<td><?php _e('Database check time', 'wp_statistics'); ?>:</td>
			<td>
				<span style="font-size:10px;"><?php _e('Each', 'wp_statistics'); ?></span>
				<input type="text" style="direction:ltr; width:60px" maxlength="3" disabled="disable"/>
				<span style="font-size:10px;"><?php _e('Minute updates', 'wp_statistics'); ?></span>
			</td>
			<td>
				<span style="font-size:11px; color:#FF0000;"><?php _e('Recommended', 'wp_statistics'); ?></span>
				<span style="font-size:11px;">(<?php _e('Due to pressure on the server, Be set up on time. Default 1 min.', 'wp_statistics'); ?>)</span>
			</td>
		</tr>

		<tr><th><h3><?php _e('Pagerank configuration', 'wp_statistics'); ?></h4></th></tr>

		<tr>
			<td><?php _e('Your url for Google pagerank check', 'wp_statistics'); ?>:</td>
			<td>
				<input type="text" name="pagerank_google_url" style="direction:ltr; width:200px" value="<?php echo get_option('pagerank_google_url'); ?>"/>
			</td>
			<td>
				<span style="font-size:11px;">(<?php _e('If this input is empty, The website url uses', 'wp_statistics'); ?>)</span>
			</td>
		</tr>

		<tr>
			<td><?php _e('Your url for Alexa pagerank check', 'wp_statistics'); ?>:</td>
			<td>
				<input type="text" name="pagerank_alexa_url" style="direction:ltr; width:200px" value="<?php echo get_option('pagerank_alexa_url'); ?>"/>
			</td>
			<td>
				<span style="font-size:11px;">(<?php _e('If this input is empty, The website url uses', 'wp_statistics'); ?>)</span>
			</td>
		</tr>

		<tr>
			<th>
				<h3><?php _e('About plugin', 'wp_statistics'); ?></h4>
				<?php _e('Plugin Version', 'wp_statistics'); ?>: <?php _e('Free!', 'wp_statistics'); ?>
				<a href="http://www.wpbazar.com/products/wp-statistics-premium"><span style="font-size:10px; color:#009900;"><?php _e('Get Premium version', 'wp_statistics'); ?></span></a>
			</th>
		</tr>

		<tr>
		<th colspan="3">
			<?php _e('This plugin created by', 'wp_statistics'); ?> <a href="http://profile.wordpress.org/mostafa.s1990">Mostafa Soufi</a> <?php _e('from', 'wp_statistics'); ?> <a href="http://wpbazar.com">WPBazar</a> <?php _e('group', 'wp_statistics'); ?>.
			<?php _e('for translate language files. please send files for', 'wp_statistics'); ?> <code>mst404@gmail.com</code>
				<p style="padding-top: 5px;">
					<span class="button" id="show_function"><?php _e('Show Functions', 'wp_statistics'); ?></span>
					<span class="button" id="report_problem"><?php _e('Report Problem', 'wp_statistics'); ?></span>
				</p>

			<style>
				a{text-decoration: none}
				ul#show_function code{border-radius:5px; padding: 5px; display: none;}
				ul#show_function{list-style-type: decimal; margin: 20px; display:none;}
				ul#show_function li{line-height: 25px;}
				div#report_problem{display: none;}
			</style>
			<ul id="show_function">
				<li><?php _e('User Online', 'wp_statistics'); ?>			<code>wp_statistics_useronline();</code>
				<li><?php _e('Today Visit', 'wp_statistics'); ?>			<code>wp_statistics_today();</code>
				<li><?php _e('Yesterday visit', 'wp_statistics'); ?>		<code>wp_statistics_yesterday();</code>
				<li><?php _e('Week Visit', 'wp_statistics'); ?>				<code>wp_statistics_week();</code>
				<li><?php _e('Month Visit', 'wp_statistics'); ?>			<code>wp_statistics_month();</code>
				<li><?php _e('Years Visit', 'wp_statistics'); ?>			<code>wp_statistics_year();</code>
				<li><?php _e('Total Visit', 'wp_statistics'); ?>			<code>wp_statistics_total();</code>
				<li><?php _e('Search Engine reffered', 'wp_statistics'); ?>	<code>wp_statistics_searchengine();</code>
				<li><?php _e('User Online Live', 'wp_statistics'); ?>		<code>wp_statistics_useronline_live();</code>
				<li><?php _e('Total Visit Live', 'wp_statistics'); ?>		<code>wp_statistics_total_live();</code>
				<li><?php _e('Total Posts', 'wp_statistics'); ?>			<code>wp_statistics_countposts();</code>
				<li><?php _e('Total Pages', 'wp_statistics'); ?>			<code>wp_statistics_countpages();</code>
				<li><?php _e('Total Comments', 'wp_statistics'); ?>			<code>wp_statistics_countcomment();</code>
				<li><?php _e('Total Spams', 'wp_statistics'); ?>			<code>wp_statistics_countspam();</code>
				<li><?php _e('Total Users', 'wp_statistics'); ?>			<code>wp_statistics_countusers();</code>
				<li><?php _e('Last Post Date', 'wp_statistics'); ?>			<code>wp_statistics_lastpostdate();</code>
				<li><?php _e('Average Posts', 'wp_statistics'); ?>			<code>wp_statistics_average_post();</code>
				<li><?php _e('Average Comments', 'wp_statistics'); ?>		<code>wp_statistics_average_comment();</code>
				<li><?php _e('Average Users', 'wp_statistics'); ?>			<code>wp_statistics_average_registeruser();</code>
				<li><?php _e('Total Feedburner Subscribe', 'wp_statistics'); ?> <code>wp_statistics_countsubscrib();</code>
				<li><?php _e('Google Pagerank', 'wp_statistics'); ?>		<code>wp_statistics_google_page_rank();</code>
				<li><?php _e('Alexa Pagerank', 'wp_statistics'); ?>			<code>wp_statistics_alexaRank();</code>			
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
			<td>
				<p class="submit">
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="enable_stats,enable_decimals,time_useronline,items_statistics,pagerank_google_url,pagerank_alexa_url" />
				<input type="submit" class="button-primary" name="Submit" value="<?php _e('Update', 'wp_statistics'); ?>" />
				</p>
			</td>
		</tr>
	</form>	
	</table>
	</div>

	<?php }
	function wp_statistics_stats_permission() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'wp_statistics') ); } ?>
	<div class="wrap">
		<h2><img src="<?php echo plugin_dir_url( __FILE__ ); ?>/images/icon_big.png"/> <?php _e('Stats weblog', 'wp_statistics'); ?></h2>
		<table class="form-table">
		<?php
			global $wpdb, $table_prefix;
			$get_user_ip = $wpdb->get_col("SELECT ip FROM {$table_prefix}statistics_reffered");
			$get_user_time = $wpdb->get_col("SELECT time FROM {$table_prefix}statistics_reffered");
			$get_user_referred = $wpdb->get_col("SELECT referred FROM {$table_prefix}statistics_reffered");
			$get_user_agent = $wpdb->get_col("SELECT agent FROM {$table_prefix}statistics_reffered");
			$get_total_online = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}statistics_reffered");

				echo "<tr style='background-color:#EEEEEE; border:1px solid #DDDDDD;' align='center'>";
					echo "<td width='5'>".__('No', 'wp_statistics')."</td>";
					echo "<td>".__('IP', 'wp_statistics')."</td>";
					echo "<td>".__('Time', 'wp_statistics')."</td>";
					echo "<td>".__('Referred', 'wp_statistics')."</td>";
					echo "<td>".__('Agent', 'wp_statistics')."</td>";
				echo "</tr>";

			for($i=0; $i<$get_total_online; $i++) {
				$j = $i+1;
				echo "<tr style='border:1px solid #EEEEEE; direction:ltr;'>";
					echo "<td>$j</td>";
					echo "<td>$get_user_ip[$i]</td>";
					echo "<td>$get_user_time[$i]</td>";
					echo "<td><a href='$get_user_referred[$i]' target='_blank'>$get_user_referred[$i]</a></td>";
					echo "<td>$get_user_agent[$i]</td>";
				echo "</tr>";
			}
		?>
		</table>
	</div>
	<?php }
	function wp_statistics_online_permission() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'wp_statistics') ); } ?>
	<div class="wrap">
		<h2><img src="<?php echo plugin_dir_url( __FILE__ ); ?>/images/icon_big.png"/> <?php _e('User Online', 'wp_statistics'); ?></h2>
		<table class="form-table">
		<?php
			global $wpdb, $table_prefix;
			$get_user_ip = $wpdb->get_col("SELECT ip FROM {$table_prefix}statistics_useronline");
			$get_user_time = $wpdb->get_col("SELECT time FROM {$table_prefix}statistics_useronline");
			$get_user_referred = $wpdb->get_col("SELECT referred FROM {$table_prefix}statistics_useronline");
			$get_user_agent = $wpdb->get_col("SELECT agent FROM {$table_prefix}statistics_useronline");
			$get_total_online = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}statistics_useronline");

				echo "<tr style='background-color:#EEEEEE; border:1px solid #DDDDDD;' align='center'>";
					echo "<td width='5'>".__('No', 'wp_statistics')."</td>";
					echo "<td>".__('IP', 'wp_statistics')."</td>";
					echo "<td>".__('Time', 'wp_statistics')."</td>";
					echo "<td>".__('Referred', 'wp_statistics')."</td>";
					echo "<td>".__('Agent', 'wp_statistics')."</td>";
				echo "</tr>";

			for($i=0; $i<$get_total_online; $i++) {
				$j = $i+1;
				echo "<tr style='border:1px solid #EEEEEE; direction:ltr;'>";
					echo "<td>$j</td>";
					echo "<td>$get_user_ip[$i]</td>";
					echo "<td>$get_user_time[$i]</td>";
					echo "<td><a href='$get_user_referred[$i]' target='_blank'>$get_user_referred[$i]</a></td>";
					echo "<td>$get_user_agent[$i]</td>";
				echo "</tr>";
			}
		?>
		</table>
	</div>
	<?php }
function wp_statistics_show_widget() {
	echo "<h3 class='widget-title'>".get_option('wp_statistics_widget_title')."</h3>";
	echo "<ul>";
		echo "<li>";
			echo __('User Online', 'wp_statistics'). ": ";
			wp_statistics_useronline();
		echo "</li>";
		
		echo "<li>";
			echo __('Today Visit', 'wp_statistics'). ": ";
			wp_statistics_today();
		echo "</li>";

		echo "<li>";
			echo __('Yesterday Visit', 'wp_statistics'). ": ";
			wp_statistics_yesterday();
		echo "</li>";

		echo "<li>";
			echo __('Total Visit', 'wp_statistics'). ": ";
			wp_statistics_total();
		echo "</li>";
		
		echo "<li>";
			echo __('Total Posts', 'wp_statistics'). ": ";
			wp_statistics_countposts();
		echo "</li>";

		echo "<li>";
			echo __('Total Comments', 'wp_statistics'). ": ";
			wp_statistics_countcomment();
		echo "</li>";

		echo "<li>";
			echo __('Last Post Date', 'wp_statistics'). ": ";
			if(get_option('wp_statistics_widget_typedate') == 'farsi') {
				wp_statistics_lastpostdate('farsi');
			} else {
				wp_statistics_lastpostdate();
			}
		echo "</li>";
	echo "</ul>";
}

	function wp_statistics_control_widget(){
		if ($_POST['wp_statistics_control_widget_submit']) {
			$get_wp_statistics_widget_title = $_POST['wp_statistics_widget_title'];
			update_option('wp_statistics_widget_title', $get_wp_statistics_widget_title);

			$get_wp_statistics_widget_typedate = $_POST['wp_statistics_widget_typedate'];
			update_option('wp_statistics_widget_typedate', $get_wp_statistics_widget_typedate);
		} ?>

		<p>
			<?php _e('Name', 'wp_statistics'); ?><br />
			<input id="wp_statistics_widget_title" name="wp_statistics_widget_title" type="text" value="<?php echo get_option('wp_statistics_widget_title'); ?>" />
		</p>

		<p>
			<?php _e('Type date for last update', 'wp_statistics'); ?><br />
			<input id="wp_statistics_widget_endate" name="wp_statistics_widget_typedate" value="english" type="radio" <?php checked( 'english', get_option('wp_statistics_widget_typedate') ); ?>/>
			<label for="wp_statistics_widget_endate"><?php _e('English', 'wp_statistics'); ?><br />
			
			<input id="wp_statistics_widget_jdate" name="wp_statistics_widget_typedate" value="farsi" type="radio" <?php checked( 'farsi', get_option('wp_statistics_widget_typedate') ); ?>/>	
			<label for="wp_statistics_widget_jdate"><?php _e('Persian', 'wp_statistics'); ?>
		</p>

		<input type="hidden" id="wp_statistics_control_widget_submit" name="wp_statistics_control_widget_submit" value="1" />
	<?php }

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

	function wp_statistics_widget() {
		wp_register_sidebar_widget('wp_statistics_widget', __('WP-Statistics', 'wp_statistics'), 'wp_statistics_show_widget', array(
			'description'	=>	__('Show site stats in sidebar', 'wp_statistics')));
		wp_register_widget_control('wp_statistics_widget', __('WP-Statistics', 'wp_statistics'), 'wp_statistics_control_widget');
	}
?>