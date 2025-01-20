<div id="wps-modal-filter-popup" dir="<?php echo (is_rtl() ? 'rtl' : 'ltr') ?>" style="display:none;">
    <form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="get" id="wps-referrals-filter-form">
        <input type="hidden" name="page" value="<?php echo esc_attr($pageName); ?>">
        <div id="wps-referral-filter-div" class="wps-modal-filter-form">
            <table class="o-table wps-referrals-filter">
                <tr>
                    <td colspan="2" class="wps-referrals-filter-title"><?php esc_html_e('Referrer', 'wp-statistics'); ?></td>
                </tr>
                <tr>
                    <td colspan="2" class="wps-referrals-filter-content">
                        <select name="referrer" class="wps-select2  wps-width-100">
                            <!-- Options will be populated dynamically via JavaScript -->
                        </select>
                    </td>
                </tr>
                <tr class="wps-tb-window-footer">
                    <td><button type="button" class="wps-reset-filter wps-modal-reset-filter"><?php esc_html_e('Reset', 'wp-statistics'); ?></button></td>
                    <td><button type="submit" class="button-primary"><?php esc_html_e('Apply Filter', 'wp-statistics'); ?></button></td>
                </tr>
            </table>
        </div>
    </form>
</div>