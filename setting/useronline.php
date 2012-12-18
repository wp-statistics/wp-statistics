<div class="wrap">
	<h2><img src="<?php echo plugins_url('wp-statistics/images/icon_big.png');?>"/> <?php _e('User Online', 'wp_statistics'); ?></h2>
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