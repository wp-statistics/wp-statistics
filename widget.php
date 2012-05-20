<?php
function wp_statistics_widget()
{
	wp_register_sidebar_widget('wp_statistics_widget', __('WP-Statistics', 'wp_statistics'), 'wp_statistics_show_widget', array(
		'description'	=>	__('Show site stats in sidebar', 'wp_statistics')));
	wp_register_widget_control('wp_statistics_widget', __('WP-Statistics', 'wp_statistics'), 'wp_statistics_control_widget');
}

function wp_statistics_show_widget($args)
{
	extract($args);
	echo $before_widget;
	echo $before_title . get_option('name_widget') . $after_title ;
		echo "<ul>";
		if(get_option('useronline_widget'))
		{
			echo "<li>";
				echo __('User Online', 'wp_statistics'). ": ";
				echo wp_statistics_useronline();
			echo "</li>";
		}
		
		if(get_option('tvisit_widget'))
		{
			echo "<li>";
				echo __('Today Visit', 'wp_statistics'). ": ";
				echo wp_statistics_today();
			echo "</li>";
		}

		if(get_option('yvisit_widget'))
		{
			echo "<li>";
				echo __('Yesterday Visit', 'wp_statistics'). ": ";
				echo wp_statistics_yesterday();
			echo "</li>";
		}

		if(get_option('wvisit_widget'))
		{
			echo "<li>";
				echo __('Week Visit', 'wp_statistics'). ": ";
				echo wp_statistics_week();
			echo "</li>";
		}

		if(get_option('mvisit_widget'))
		{
			echo "<li>";
				echo __('Month Visit', 'wp_statistics'). ": ";
				echo wp_statistics_month();
			echo "</li>";
		}

		if(get_option('ysvisit_widget'))
		{
			echo "<li>";
				echo __('Years Visit', 'wp_statistics'). ": ";
				echo wp_statistics_year();
			echo "</li>";
		}

		if(get_option('ttvisit_widget'))
		{
			echo "<li>";
				echo __('Total Visit', 'wp_statistics'). ": ";
				echo wp_statistics_total();
			echo "</li>";
		}

		if(get_option('ser_widget'))
		{
			echo "<li>";
				echo __('Search Engine reffered', 'wp_statistics'). ": ";
				if(get_option('select_se') == "google"){
					echo wp_statistics_searchengine("google");
				} else if(get_option('select_se') == "yahoo"){
					echo wp_statistics_searchengine("yahoo");
				} else if(get_option('select_se') == "bing"){
					echo wp_statistics_searchengine("bing");
				} else if(get_option('select_se') == "all"){
					echo wp_statistics_searchengine();
				}
			echo "</li>";
		}
		
		if(get_option('tp_widget'))
		{
			echo "<li>";
				echo __('Total Posts', 'wp_statistics'). ": ";
				echo wp_statistics_countposts();
			echo "</li>";
		}

		if(get_option('tpg_widget'))
		{
			echo "<li>";
				echo __('Total Pages', 'wp_statistics'). ": ";
				echo wp_statistics_countpages();
			echo "</li>";
		}

		if(get_option('tc_widget'))
		{
			echo "<li>";
				echo __('Total Comments', 'wp_statistics'). ": ";
				echo wp_statistics_countcomment();
			echo "</li>";
		}

		if(get_option('ts_widget'))
		{
			echo "<li>";
				echo __('Total Spams', 'wp_statistics'). ": ";
				echo wp_statistics_countspam();
			echo "</li>";
		}

		if(get_option('tu_widget'))
		{
			echo "<li>";
				echo __('Total Users', 'wp_statistics'). ": ";
				echo wp_statistics_countusers();
			echo "</li>";
		}

		if(get_option('ap_widget'))
		{
			echo "<li>";
				echo __('Average Posts', 'wp_statistics'). ": ";
				echo wp_statistics_average_post();
			echo "</li>";
		}

		if(get_option('ac_widget'))
		{
			echo "<li>";
				echo __('Average Comments', 'wp_statistics'). ": ";
				echo wp_statistics_average_comment();
			echo "</li>";
		}

		if(get_option('au_widget'))
		{
			echo "<li>";
				echo __('Average Users', 'wp_statistics'). ": ";
				echo wp_statistics_average_registeruser();
			echo "</li>";
		}

		if(get_option('lpd_widget'))
		{
			echo "<li>";
				echo __('Last Post Date', 'wp_statistics'). ": ";
				if(get_option('select_lps') == "farsi")
				{
					echo wp_statistics_lastpostdate("farsi");
				} else {
					echo wp_statistics_lastpostdate();
				}
			echo "</li>";
		}
		echo "</ul>";
	echo "</div>";
	echo $after_widget;
}

	function wp_statistics_control_widget()
	{
		if ($_POST['wp_statistics_control_widget_submit'])
		{
			update_option('name_widget', $_POST['name_widget']);
			update_option('useronline_widget', $_POST['useronline_widget']);
			update_option('tvisit_widget', $_POST['tvisit_widget']);
			update_option('yvisit_widget', $_POST['yvisit_widget']);
			update_option('wvisit_widget', $_POST['wvisit_widget']);
			update_option('mvisit_widget', $_POST['mvisit_widget']);
			update_option('ysvisit_widget', $_POST['ysvisit_widget']);
			update_option('ttvisit_widget', $_POST['ttvisit_widget']);
			update_option('ser_widget', $_POST['ser_widget']);
			update_option('select_se', $_POST['select_se']);
			update_option('tp_widget', $_POST['tp_widget']);
			update_option('tpg_widget', $_POST['tpg_widget']);
			update_option('tc_widget', $_POST['tc_widget']);
			update_option('ts_widget', $_POST['ts_widget']);
			update_option('tu_widget', $_POST['tu_widget']);
			update_option('ap_widget', $_POST['ap_widget']);
			update_option('ac_widget', $_POST['ac_widget']);
			update_option('au_widget', $_POST['au_widget']);
			update_option('lpd_widget', $_POST['lpd_widget']);
			update_option('select_lps', $_POST['select_lps']);
		} ?>

		<p>
			<?php _e('Name', 'wp_statistics'); ?>:<br />
			<input id="name_widget" name="name_widget" type="text" value="<?php echo get_option('name_widget'); ?>" />
		</p>

		<?php _e('Items', 'wp_statistics'); ?>:</br />
		<ul>
			<li><input type="checkbox" id="useronline_widget" name="useronline_widget" <?php checked('on', get_option('useronline_widget')); ?>/>
			<label for="useronline_widget"><?php _e('User Online', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="tvisit_widget" name="tvisit_widget" <?php checked('on', get_option('tvisit_widget')); ?>/>
			<label for="tvisit_widget"><?php _e('Today Visit', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="yvisit_widget" name="yvisit_widget" <?php checked('on', get_option('yvisit_widget')); ?>/>
			<label for="yvisit_widget"><?php _e('Yesterday visit', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="wvisit_widget" name="wvisit_widget" <?php checked('on', get_option('wvisit_widget')); ?>/>
			<label for="wvisit_widget"><?php _e('Week Visit', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="mvisit_widget" name="mvisit_widget" <?php checked('on', get_option('mvisit_widget')); ?>/>
			<label for="mvisit_widget"><?php _e('Month Visit', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="ysvisit_widget" name="ysvisit_widget" <?php checked('on', get_option('ysvisit_widget')); ?>/>
			<label for="ysvisit_widget"><?php _e('Years Visit', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="ttvisit_widget" name="ttvisit_widget" <?php checked('on', get_option('ttvisit_widget')); ?>/>
			<label for="ttvisit_widget"><?php _e('Total Visit', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="ser_widget" class="ser_widget" name="ser_widget" <?php checked('on', get_option('ser_widget')); ?>/>
			<label for="ser_widget"><?php _e('Search Engine reffered', 'wp_statistics'); ?></label></li>

			<p id="ser_option" style="<?php if(!get_option('ser_widget')) { echo "display: none;"; } ?>">
				<?php _e('Select type of search engine', 'wp_statistics'); ?>:<br />
				<input type="radio" id="select_google" name="select_se" value="google" <?php checked('google', get_option('select_se')); ?>/>
				<label for="select_google"><?php _e('Google', 'wp_statistics'); ?></label>

				<input type="radio" id="select_yahoo" name="select_se" value="yahoo" <?php checked('yahoo', get_option('select_se')); ?>/>
				<label for="select_yahoo"><?php _e('Yahoo!', 'wp_statistics'); ?></label>

				<input type="radio" id="select_bing" name="select_se" value="bing" <?php checked('bing', get_option('select_se')); ?>/>
				<label for="select_bing"><?php _e('Bing', 'wp_statistics'); ?></label>

				<input type="radio" id="select_all" name="select_se" value="all" <?php checked('all', get_option('select_se')); ?>/>
				<label for="select_all"><?php _e('All', 'wp_statistics'); ?></label>
			</p>

			<li><input type="checkbox" id="tp_widget" name="tp_widget" <?php checked('on', get_option('tp_widget')); ?>/>
			<label for="tp_widget"><?php _e('Total Posts', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="tpg_widget" name="tpg_widget" <?php checked('on', get_option('tpg_widget')); ?>/>
			<label for="tpg_widget"><?php _e('Total Pages', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="tc_widget" name="tc_widget" <?php checked('on', get_option('tc_widget')); ?>/>
			<label for="tc_widget"><?php _e('Total Comments', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="ts_widget" name="ts_widget" <?php checked('on', get_option('ts_widget')); ?>/>
			<label for="ts_widget"><?php _e('Total Spams', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="tu_widget" name="tu_widget" <?php checked('on', get_option('tu_widget')); ?>/>
			<label for="tu_widget"><?php _e('Total Users', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="ap_widget" name="ap_widget" <?php checked('on', get_option('ap_widget')); ?>/>
			<label for="ap_widget"><?php _e('Average Posts', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="ac_widget" name="ac_widget" <?php checked('on', get_option('ac_widget')); ?>/>
			<label for="ac_widget"><?php _e('Average Comments', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="au_widget" name="au_widget" <?php checked('on', get_option('au_widget')); ?>/>
			<label for="au_widget"><?php _e('Average Users', 'wp_statistics'); ?></label></li>

			<li><input type="checkbox" id="lpd_widget" class="lpd_widget" name="lpd_widget" <?php checked('on', get_option('lpd_widget')); ?>/>
			<label for="lpd_widget"><?php _e('Last Post Date', 'wp_statistics'); ?></label></li>

			<p id="lpd_option" style="<?php if(!get_option('lpd_widget')) { echo "display: none;"; } ?>">
				<?php _e('Type date for last update', 'wp_statistics'); ?>:<br />
				<input id="wp_statistics_widget_endate" name="select_lps" value="english" type="radio" <?php checked( 'english', get_option('select_lps') ); ?>/>
				<label for="wp_statistics_widget_endate"><?php _e('English', 'wp_statistics'); ?></label>
					
				<input id="wp_statistics_widget_jdate" name="select_lps" value="farsi" type="radio" <?php checked( 'farsi', get_option('select_lps') ); ?>/>	
				<label for="wp_statistics_widget_jdate"><?php _e('Persian', 'wp_statistics'); ?></label>
			</p>
		</ul>

		<input type="hidden" id="wp_statistics_control_widget_submit" name="wp_statistics_control_widget_submit" value="1" />
		<?php
	}
?>