<div id="wps-modal-filter-popup" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" style="display:none;">
    <form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="get" id="wp_statistics_visitors_filter_form">
        <input type="hidden" name="page" value="<?php echo esc_attr($pageName); ?>">
        <div class="wps-modal-filter-form">
            <table class="o-table">
                <!-- Dropdown Filters -->
                <tr>
                    <td><?php esc_html_e('Author', 'wp-statistics'); ?></td>
                </tr>
                <tr>
                    <td>
                        <select name="author_id" class="select2 wps-width-100 filter-select" data-type-show="select2" data-type="users">
                            <!-- Options will be populated dynamically via JavaScript -->
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><?php esc_html_e('URL', 'wp-statistics'); ?></td>
                </tr>
                <tr>
                    <td>
                        <select name="url" class="wps-width-100 wps-select2 wps-width-100">
                            <!-- Options will be populated dynamically via JavaScript -->
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="wps-tb-window-footer">
                            <button type="button" class="wps-reset-filter wps-modal-reset-filter"><?php esc_html_e('Reset', 'wp-statistics'); ?></button>
                            <button type="submit" class="button-primary"><?php esc_html_e('Apply filter', 'wp-statistics'); ?></button>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>