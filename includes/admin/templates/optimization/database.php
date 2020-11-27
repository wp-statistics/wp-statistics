<div class="wrap wps-wrap">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Database Setup', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="index-submit"><?php _e('Re-run Install:', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="install-submit" class="button button-primary" type="button" value="<?php _e('Install Now!', 'wp-statistics'); ?>" name="install-submit" onclick="location.href=document.URL+'&install=1&tab=database'">
                <p class="description"><?php _e('If for some reason your installation of WP Statistics is missing the database tables or other core items, this will re-execute the install process.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>