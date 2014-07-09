<?php 
GLOBAL $WP_Statistics;

$widget_options = $WP_Statistics->get_option('widget');

?>
<p>
	<?php _e('Name', 'wp_statistics'); ?>:<br />
	<input id="name_widget" name="name_widget" type="text" value="<?php echo $widget_options['name_widget']; ?>" />
</p>

<?php _e('Items', 'wp_statistics'); ?>:</br />
<ul>
	<li><input type="checkbox" id="useronline_widget" name="useronline_widget" <?php checked('on', $widget_options['useronline_widget']); ?>/>
	<label for="useronline_widget"><?php _e('User Online', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="tvisit_widget" name="tvisit_widget" <?php checked('on', $widget_options['tvisit_widget']); ?>/>
	<label for="tvisit_widget"><?php _e('Today Visit', 'wp_statistics'); ?></label></li>
	
	<li><input type="checkbox" id="tvisitor_widget" name="tvisitor_widget" <?php checked('on', $widget_options['tvisitor_widget']); ?>/>
	<label for="tvisitor_widget"><?php _e('Today Visitor', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="yvisit_widget" name="yvisit_widget" <?php checked('on', $widget_options['yvisit_widget']); ?>/>
	<label for="yvisit_widget"><?php _e('Yesterday visit', 'wp_statistics'); ?></label></li>
	
	<li><input type="checkbox" id="yvisitor_widget" name="yvisitor_widget" <?php checked('on', $widget_options['yvisitor_widget']); ?>/>
	<label for="yvisitor_widget"><?php _e('Yesterday Visitor', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="wvisit_widget" name="wvisit_widget" <?php checked('on', $widget_options['wvisit_widget']); ?>/>
	<label for="wvisit_widget"><?php _e('Week Visit', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="mvisit_widget" name="mvisit_widget" <?php checked('on', $widget_options['mvisit_widget']); ?>/>
	<label for="mvisit_widget"><?php _e('Month Visit', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="ysvisit_widget" name="ysvisit_widget" <?php checked('on', $widget_options['ysvisit_widget']); ?>/>
	<label for="ysvisit_widget"><?php _e('Years Visit', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="ttvisit_widget" name="ttvisit_widget" <?php checked('on', $widget_options['ttvisit_widget']); ?>/>
	<label for="ttvisit_widget"><?php _e('Total Visit', 'wp_statistics'); ?></label></li>
	
	<li><input type="checkbox" id="ttvisitor_widget" name="ttvisitor_widget" <?php checked('on', $widget_options['ttvisitor_widget']); ?>/>
	<label for="ttvisitor_widget"><?php _e('Total Visitor', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="tpviews_widget" name="tpviews_widget" <?php checked('on', $widget_options['tpviews_widget']); ?>/>
	<label for="tpviews_widget"><?php _e('Total Page Views', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="ser_widget" class="ser_widget" name="ser_widget" <?php checked('on', $widget_options['ser_widget']); ?>/>
	<label for="ser_widget"><?php _e('Search Engine Referred', 'wp_statistics'); ?></label></li>

	<p id="ser_option" style="<?php if(!$widget_options['ser_widget']) { echo "display: none;"; } ?>">
		<?php _e('Select type of search engine', 'wp_statistics'); ?>:<br />
<?php
		$search_engines = wp_statistics_searchengine_list();
		
		foreach( $search_engines as $se ) {
			echo '		<input type="radio" id="select_' .$se['tag'] . '" name="select_se" value="' . $se['tag'] . '" ';
			checked($se['tag'], $widget_options['select_se']);
			echo "/>\n";
			echo '		<label for="' . $se['name'] . '">' . __($se['name'], 'wp_statistics') . "</label>\n";
			echo "\n";
		}
?>
		<input type="radio" id="select_all" name="select_se" value="all" <?php checked('all', $widget_options['select_se']); ?>/>
		<label for="select_all"><?php _e('All', 'wp_statistics'); ?></label>
	</p>

	<li><input type="checkbox" id="tp_widget" name="tp_widget" <?php checked('on', $widget_options['tp_widget']); ?>/>
	<label for="tp_widget"><?php _e('Total Posts', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="tpg_widget" name="tpg_widget" <?php checked('on', $widget_options['tpg_widget']); ?>/>
	<label for="tpg_widget"><?php _e('Total Pages', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="tc_widget" name="tc_widget" <?php checked('on', $widget_options['tc_widget']); ?>/>
	<label for="tc_widget"><?php _e('Total Comments', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="ts_widget" name="ts_widget" <?php checked('on', $widget_options['ts_widget']); ?>/>
	<label for="ts_widget"><?php _e('Total Spams', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="tu_widget" name="tu_widget" <?php checked('on', $widget_options['tu_widget']); ?>/>
	<label for="tu_widget"><?php _e('Total Users', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="ap_widget" name="ap_widget" <?php checked('on', $widget_options['ap_widget']); ?>/>
	<label for="ap_widget"><?php _e('Average Posts', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="ac_widget" name="ac_widget" <?php checked('on', $widget_options['ac_widget']); ?>/>
	<label for="ac_widget"><?php _e('Average Comments', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="au_widget" name="au_widget" <?php checked('on', $widget_options['au_widget']); ?>/>
	<label for="au_widget"><?php _e('Average Users', 'wp_statistics'); ?></label></li>

	<li><input type="checkbox" id="lpd_widget" class="lpd_widget" name="lpd_widget" <?php checked('on', $widget_options['lpd_widget']); ?>/>
	<label for="lpd_widget"><?php _e('Last Post Date', 'wp_statistics'); ?></label></li>
</ul>

<input type="hidden" id="wp_statistics_control_widget_submit" name="wp_statistics_control_widget_submit" value="1" />