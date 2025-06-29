<div class="wps-notice wps-notice--success">
    <div>
        <p class="wps-notice__title"><?php esc_html_e('No WP Statistics License Detected', 'wp-statistics') ?></p>
        <div class="wps-notice__description">
            <?php
            echo wp_kses_post(sprintf(
                __('You haven’t registered a WP Statistics license yet. Having a valid license unlocks premium add-ons and features. <a href="%s" target="_blank">Purchase</a> or <a href="%s">add a license</a> now to get started!.', 'wp-statistics'),
                esc_url('https://wp-statistics.com/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon'),
                esc_url($data['install_addon_link'])
            ));
            ?>
        </div>
    </div>
</div>
