<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery("span#increase_total_visit").click(function() {
			var total_increase_value = jQuery("input#increase_total_visit").val();
			jQuery("input#increase_total_visit").attr("disabled", "disabled");
			jQuery("span#increase_total_visit").attr("disabled", "disabled");
			jQuery("div#result_increase_total_visit").html("<img src='<?php echo plugins_url('wp-statistics/images/loading.gif');?>'/>");
			jQuery.post("<?php echo plugins_url('wp-statistics/actions.php');?>",{increase_value:total_increase_value},function(result){
			jQuery("div#result_increase_total_visit").html(result);
			jQuery("input#increase_total_visit").removeAttr("disabled");
			jQuery("span#increase_total_visit").removeAttr("disabled");
			});
		});

		jQuery("span#reduction_total_visit").click(function() {
			var total_reduction_value = jQuery("input#reduction_total_visit").val();
			jQuery("input#reduction_total_visit").attr("disabled", "disabled");
			jQuery("span#reduction_total_visit").attr("disabled", "disabled");
			jQuery("div#result_reduction_total_visit").html("<img src='<?php echo plugins_url('wp-statistics/images/loading.gif');?>'/>");
			jQuery.post("<?php echo plugins_url('wp-statistics/actions.php');?>",{reduction_value:total_reduction_value},function(result){
			jQuery("div#result_reduction_total_visit").html(result);
			jQuery("input#reduction_total_visit").removeAttr("disabled");
			jQuery("span#reduction_total_visit").removeAttr("disabled");
			});
		});

		jQuery("span#show_function").click(function() {
			jQuery("div#report_problem").slideUp(1000);
			jQuery("ul#functions_list").slideDown(1000, function() {
				jQuery("ul#functions_list code").fadeIn(1000);
			});
		});
		
		jQuery("span#hide_function").click(function() {
			jQuery("ul#functions_list").slideUp(1000);
		});	

		jQuery("span#hide_report").click(function() {
			jQuery("div#report_problem").slideUp(1000);
		});

		jQuery("span#report_problem").click(function() {
			jQuery("ul#functions_list").slideUp(1000);
			jQuery("div#report_problem").slideDown(1000);
		});

		jQuery("span#send_report").click(function() {
			var your_name = jQuery("input#your_name").val();
			var your_report = jQuery("textarea#your_report").val();
			jQuery("div#result_problem").html("<img src='<?php echo plugins_url('wp-statistics/images/loading.gif');?>'/>");
			jQuery("div#result_problem").load("<?php echo plugins_url('wp-statistics/report-problem.php');?>", {y_name:your_name, d_report:your_report});
		});

		jQuery("span#uninstall").click(function() {
			var uninstall = confirm("<?php _e('Are you sure?', 'wp_statistics'); ?>");

			if(uninstall) {
				jQuery("div#result_uninstall").html("<img src='<?php echo plugins_url('wp-statistics/images/loading.gif');?>'/>");
				jQuery("div#result_uninstall").load('<?php echo plugins_url('wp-statistics/uninstall.php');?>');
			}
		});
	});
</script>

<div class="wrap">
	<h2><img src="<?php echo plugins_url('wp-statistics/images/icon_big.png');?>"/> <?php _e('Configuration', 'wp_statistics'); ?></h2>
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
				<td><span style="font-size:11px;">(<?php echo sprintf(__('Time for the check accurate online user in the site. Default: %s Second', 'wp_statistics'), 5); ?>)</span></td>
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
				<?php echo sprintf(__('This plugin created by %s from %s and %s.', 'wp_statistics'), '<a href="http://design.iran98.org/">Mostafa Soufi</a>', '<a href="http://forum.wp-parsi.com/">WP Parsi</a>', '<a href="http://wpbazar.com/">WP Bazar</a>'); ?>

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
					<li><?php _e('Language', 'wp_statistics'); ?> Russian <?php _e('by', 'wp_statistics'); ?> <a href="http://www.bestplugins.ru/">Oleg Martin</a></li>
					<li><?php _e('Language', 'wp_statistics'); ?> Bengali <?php _e('by', 'wp_statistics'); ?> <a href="http://www.shamokaldarpon.com/">Mehdi Akram</a></li>
					<li><?php _e('Language', 'wp_statistics'); ?> Serbian <?php _e('by', 'wp_statistics'); ?> <a href="http://www.georgijevic.info">Radovan   Georgijevic</a></li>
					<li><?php _e('Language', 'wp_statistics'); ?> Polish <?php _e('by', 'wp_statistics'); ?> Tomasz Stulka</li>
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