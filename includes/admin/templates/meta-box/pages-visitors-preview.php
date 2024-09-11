<div class="wps-admin-pages-component__preview">
    <div class="wps-admin-pages-component__promotion">
        <h3><?php echo sprintf(
            // translators: 1: Link to DataPlus add-on - 2: Name of the add-on.
            __('Latest Visitors is included in <a href="%s" target="_blank">%s</a>', 'wp-statistics'),
            esc_url('https://wp-statistics.com/product/wp-statistics-data-plus?utm_source=wp-statistics&utm_medium=display&utm_campaign=dp-unlock-charts'),
            esc_html__('DataPlus Add-On', 'wp-statistics')
        ); ?></h3>
        <p><?php esc_html_e('Unlock deeper insights into your website\'s performance with WP Statistics Premium.', 'wp-statistics'); ?></p>
        <a href="https://wp-statistics.com/product/wp-statistics-data-plus?utm_source=wp-statistics&utm_medium=display&utm_campaign=dp-unlock-charts" class="button-primary" target="_blank"><?php esc_html_e('Upgrade Now', 'wp-statistics'); ?></a>
    </div>

    <img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/pages-visitors-preview.png'); ?>">
</div>
