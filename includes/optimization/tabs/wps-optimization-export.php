<div class="wrap">
	
	<form method="post" action="<?php echo plugins_url('../export.php', __FILE__); ?>">
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('Export', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="table-to-export"><?php _e('Export from', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<select id="table-to-export" name="table-to-export">
						<option value="0"><?php _e('Please select', 'wp_statistics'); ?></option>
						<option value="useronline"><?php echo $table_prefix . 'statistics_useronline'; ?></option>
						<option value="visit"><?php echo $table_prefix . 'statistics_visit'; ?></option>
						<option value="visitor"><?php echo $table_prefix . 'statistics_visitor'; ?></option>
						<option value="exclusions"><?php echo $table_prefix . 'statistics_exclusions'; ?></option>
						<option value="pages"><?php echo $table_prefix . 'statistics_pages'; ?></option>
					</select>
					<p class="description"><?php _e('Select the table for the output file.', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="export-file-type"><?php _e('Export To', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<select id="export-file-type" name="export-file-type">
						<option value="0"><?php _e('Please select', 'wp_statistics'); ?></option>
						<option value="excel">Excel</option>
						<option value="xml">XML</option>
						<option value="csv">CSV</option>
						<option value="tsv">TSV</option>
					</select>
					<p class="description"><?php _e('Select the output file type.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="export-headers"><?php _e('Include Header Row', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<input id="export-headers" type="checkbox" value="1" name="export-headers">
					<p class="description"><?php _e('Include a header row as the first line of the exported file.', 'wp_statistics'); ?></p>
					<?php submit_button(__('Start Now!', 'wp_statistics'), 'primary', 'export-file-submit'); ?>
				</td>
			</tr>

		</tbody>
	</table>
	</form>
</div>