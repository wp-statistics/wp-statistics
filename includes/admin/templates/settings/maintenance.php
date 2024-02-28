<script type="text/javascript">
    function DBMaintWarning() {
        var checkbox = jQuery('#wps_schedule_dbmaint');
        if (checkbox.attr('checked') == 'checked') {
            if (!confirm('<?php _e('This will permanently delete data from the database each day, are you sure you want to enable this option?', 'wp-statistics'); ?>'))
                checkbox.attr('checked', false);
        }
    }
</script>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Purge Old Data Daily', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wps_schedule_dbmaint"><?php _e('Automatic Cleanup', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="wps_schedule_dbmaint" type="checkbox" name="wps_schedule_dbmaint" <?php echo WP_STATISTICS\Option::get('schedule_dbmaint') == true ? "checked='checked'" : ''; ?> onclick='DBMaintWarning();'>
                <label for="wps_schedule_dbmaint"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Automatic deletion of data entries that are more than a specified number of days old to keep the database optimized. The process runs the following day.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wps_schedule_dbmaint_days"><?php _e('Purge Data Older Than', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input type="text" class="small-text code" id="wps_schedule_dbmaint_days" name="wps_schedule_dbmaint_days" value="<?php echo esc_attr(WP_STATISTICS\Option::get('schedule_dbmaint_days', "365")); ?>"/>
                <?php _e('Days', 'wp-statistics'); ?>
                <p class="description"><?php echo __('Sets the age threshold for deleting data entries. Data exceeding the specified age in days will be removed. The minimum setting is 30 days.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Purge High Hit Count Visitors Daily', 'wp-statistics'); ?></h3>
            </th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wps_schedule_dbmaint_visitor"><?php _e('Automatic Cleanup', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="wps_schedule_dbmaint_visitor" type="checkbox" name="wps_schedule_dbmaint_visitor" <?php echo WP_STATISTICS\Option::get('schedule_dbmaint_visitor') == true ? "checked='checked'" : ''; ?> onclick='DBMaintWarning();'>
                <label for="wps_schedule_dbmaint_visitor"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Removes user statistics data daily for users with an abnormally high number of visits, indicating potential bot activity.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wps_schedule_dbmaint_visitor_hits"><?php _e('Purge Visitors More Than', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input type="text" class="small-text code" id="wps_schedule_dbmaint_visitor_hits" name="wps_schedule_dbmaint_visitor_hits" value="<?php echo esc_attr(WP_STATISTICS\Option::get('schedule_dbmaint_visitor_hits', '50')); ?>"/>
                <?php _e('Visits', 'wp-statistics'); ?>
                <p class="description"><?php echo __('Establishes a daily visit limit. Users with visit counts above this limit are considered for removal, with the minimum set threshold being 10 visits.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='maintenance-settings'")); ?>
