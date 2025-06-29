<div class="wps-notice wps-notice--success">
    <div>
        <p class="wps-notice__title"><?php esc_html_e('Upgrade to WP Statistics Premium', 'wp-statistics') ?></p>
        <div class="wps-notice__description">
            <?php
            echo wp_kses_post(sprintf(
                __('Want more powerful analytics? Upgrade to our Premium license to unlock advanced add-ons, enhanced features, and priority support. <br> <a href="%s" target="_blank">Upgrade Now</a> or <a href="%s" target="_blank">Learn More</a>. <br> Have questions? <a href="%s" target="_blank">Contact Support</a>', 'wp-statistics'),
                esc_url("https://wp-statistics.com/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon"),
                esc_url("https://wp-statistics.com/support/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon"),
                esc_url("https://wp-statistics.com/contact-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon")
                    ));
            ?>
        </div>
    </div>
</div>
