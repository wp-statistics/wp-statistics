<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#empty-table-submit").click(function(){
		
			var action = jQuery('#empty-table').val();
			
			if(action == 0)
				return false;
				
			var agree = confirm('<?php _e('Are you sure?', 'wp_statistics'); ?>');

			if(!agree)
				return false;
				
			var data = new Array();
			data['table-name'] = jQuery("#empty-table").val();
			
			
			jQuery("#empty-table-submit").attr("disabled", "disabled");
			jQuery("#empty-result").html("<img src='<?php echo plugins_url('wp-statistics'); ?>/images/loading.gif'/>");
			
			jQuery.post("<?php echo plugins_url('empty.php', __FILE__); ?>", {table_name:data['table-name']}, function(result){
				jQuery("#empty-result").html(result);
				jQuery("#empty-table-submit").removeAttr("disabled");
			});
		});
	});
</script>
<div class="wrap">
    <?php screen_icon('options-general'); ?>
    <h2><?php echo get_admin_page_title(); ?></h2>
	<form method="post" action="<?php echo plugins_url('export.php', __FILE__); ?>">
	<table class="form-table">
		<tbody>
			<?php settings_fields('wps_settings'); ?>
			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('Resources', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('Memory usage in PHP', 'wp_statistics'); ?>:
				</th>
				
				<th>
					<strong><?php echo number_format(memory_get_usage()); ?></strong> <?php _e('Byte', 'wp_statistics'); ?>
					<p class="description"><?php _e('Memory usage in PHP', 'wp_statistics'); ?></p>
				</th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php echo sprintf(__('Number of rows in the <code>%sstatistics_useronline</code> table', 'wp_statistics'), $table_prefix); ?>:
				</th>
				
				<th>
					<strong><?php echo $result['useronline']; ?></strong> <?php _e('Row', 'wp_statistics'); ?>
					<p class="description"><?php _e('Number of rows', 'wp_statistics'); ?></p>
				</th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php echo sprintf(__('Number of rows in the <code>%sstatistics_visit</code> table', 'wp_statistics'), $table_prefix); ?>:
				</th>
				
				<th>
					<strong><?php echo $result['visit']; ?></strong> <?php _e('Row', 'wp_statistics'); ?>
					<p class="description"><?php _e('Number of rows', 'wp_statistics'); ?></p>
				</th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php echo sprintf(__('Number of rows in the <code>%sstatistics_visitor</code> table', 'wp_statistics'), $table_prefix); ?>:
				</th>
				
				<th>
					<strong><?php echo $result['visitor']; ?></strong> <?php _e('Row', 'wp_statistics'); ?>
					<p class="description"><?php _e('Number of rows', 'wp_statistics'); ?></p>
				</th>
			</tr>
			
			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('Export', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="table-to-export"><?php _e('Export from', 'wp_statistics'); ?>:</label>
				</th>
				
				<th>
					<select id="table-to-export" name="table-to-export">
						<option value="0"><?php _e('Please select.', 'wp_statistics'); ?></option>
						<option value="useronline"><?php echo $table_prefix . 'statistics_useronline'; ?></option>
						<option value="visit"><?php echo $table_prefix . 'statistics_visit'; ?></option>
						<option value="visitor"><?php echo $table_prefix . 'statistics_visitor'; ?></option>
					</select>
					<p class="description"><?php _e('Select the table for the output file.', 'wp_statistics'); ?></p>
				</th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="export-file-type"><?php _e('Export To', 'wp_statistics'); ?>:</label>
				</th>
				
				<th>
					<select id="export-file-type" name="export-file-type">
						<option value="0"><?php _e('Please select.', 'wp_statistics'); ?></option>
						<option value="excel">Excel</option>
						<option value="xml">XML</option>
						<option value="csv">CSV</option>
						<option value="tsv">TSV</option>
					</select>
					<p class="description"><?php _e('Select the output file type.', 'wp_statistics'); ?></p>
					<?php submit_button(__('Start Now!', 'wp_statistics'), 'primary', 'export-file-submit'); ?>
				</th>
			</tr>
			
			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('Empty', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="empty-table"><?php _e('Empty Table', 'wp_statistics'); ?>:</label>
				</th>
				
				<th>
					<select id="empty-table" name="empty-table">
						<option value="0"><?php _e('Please select.', 'wp_statistics'); ?></option>
						<option value="useronline"><?php echo $table_prefix . 'statistics_useronline'; ?></option>
						<option value="visit"><?php echo $table_prefix . 'statistics_visit'; ?></option>
						<option value="visitor"><?php echo $table_prefix . 'statistics_visitor'; ?></option>
					</select>
					<p class="description"><?php _e('All data table will be lost.', 'wp_statistics'); ?></p>
					<input id="empty-table-submit" class="button button-primary" type="submit" value="<?php _e('Clear now!', 'wp_statistics'); ?>" name="empty-table-submit" Onclick="return false;">
					
					<span id="empty-result"></span>
				</th>
			</tr>
		</tbody>
	</table>
	</form>
</div>