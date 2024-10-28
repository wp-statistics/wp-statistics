<div class="wps-notice-settings wps-notice-settings--warning">
    <div>
        <p class="wps-notice-settings__title"> <?php esc_html_e('Notice:', 'wp-statistics')?></p>
        <div class="wps-notice-settings__desc">
            <?php
            echo wp_kses_post(sprintf(
                __('This add-on does not have an active license, which means it cannot receive updates, including important security updates. For uninterrupted access to updates and to keep your site secure, we strongly recommend activating a license. Activate your license <a href="%s">here</a>.', 'wp-statistics'),
                esc_url(admin_url('admin.php?page=wps_plugins_page'))
            ));
            ?>
        </div>
    </div>
</div>