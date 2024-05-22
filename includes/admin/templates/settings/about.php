<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <td scope="row" align="center">
                <a href="https://wp-statistics.com?utm_source=wp-statistics&utm_medium=link&utm_campaign=about" target="_blank">
                    <img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/logo-250.png'); ?>">
                </a>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" align="center">
                <h2><?php echo esc_html(sprintf(__('Version %s', 'wp-statistics'), esc_attr(WP_STATISTICS_VERSION))); ?></h2>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" align="center">
                <?php echo sprintf(
                    __('Developed by %1$s, featuring GeoLite2 data by %2$s. Artwork and design contributions by %3$s.', 'wp-statistics'), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	
                    '<a href="https://veronalabs.com/?utm_source=wp-statistics&utm_medium=link&utm_campaign=about" target=_blank>VeronaLabs</a>',
                    '<a href="https://www.maxmind.com" target=_blank>MaxMind</a>',
                    '<a href="https://veronalabs.com/?utm_source=wp-statistics&utm_medium=link&utm_campaign=about" target=_blank>VeronaLabs</a>',
                ); ?>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" align="center">
                <hr/>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php esc_html_e('Support Our Development', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p style="display: inline-block"><?php esc_html_e('Your contributions help us maintain and enhance WP Statistics. Show your support!', 'wp-statistics') ?></p>
                <a href="https://wp-statistics.com/donate/?utm_source=wp-statistics&utm_medium=link&utm_campaign=about" class="button" target="_blank" style="font-size: 12px; margin: 2px 5px 0; padding: 5px 15px; transform: translateY(-3px);"><?php esc_html_e('Donate', 'wp-statistics'); ?></a>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php esc_html_e('Explore Our Website', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p style="display: inline-block"><?php esc_html_e('Discover more features, tutorials, and updates on our official site.', 'wp-statistics') ?></p>
                <a href="https://wp-statistics.com/?utm_source=wp-statistics&utm_medium=link&utm_campaign=about" class="button" target="_blank" style="font-size: 12px; margin: 2px 5px 0; padding: 5px 15px; transform: translateY(-3px);"><?php esc_html_e('Visit Us', 'wp-statistics'); ?></a>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php esc_html_e('Feedback & Reviews', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p style="display: inline-block"><?php esc_html_e('Your feedback is invaluable. Rate and review us on WordPress.org and help others discover WP Statistics.', 'wp-statistics') ?></p>
                <a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post" class="button" target="_blank" style="font-size: 12px; margin: 2px 5px 0; padding: 5px 15px; transform: translateY(-3px);"><?php esc_html_e('Rate Us', 'wp-statistics'); ?></a>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php esc_html_e('Need Help? Check These First', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p><?php esc_html_e("Before contacting us, these resources might address your concerns:", 'wp-statistics'); ?></p>

                <ul style="list-style-type: disc; list-style-position: inside; padding-left: 25px;">
                    <li><?php echo sprintf(
                            esc_html__('%1$sFrequently Asked Questions%2$s', 'wp-statistics'),
                            '<a title="' .
                            esc_html__('FAQs', 'wp-statistics') .
                            '" href="https://wp-statistics.com/category/faq/?utm_source=wp-statistics&utm_medium=link&utm_campaign=about" target="_blank">',
                            '</a>'
                        ); ?></li>
                    <li><?php echo sprintf(
                            esc_html__('%1$sDocumentation%2$s and User Guides', 'wp-statistics'),
                            '<a title="' .
                            esc_html__('Documentation', 'wp-statistics') .
                            '" href="https://wp-statistics.com/category/documentation/?utm_source=wp-statistics&utm_medium=link&utm_campaign=about">',
                            '</a>'
                        ); ?></li>
                    <li><?php echo sprintf(
                            esc_html__('%1$sSupport Forum%2$s for Common Issues', 'wp-statistics'),
                            '<a href="https://wordpress.org/support/plugin/wp-statistics" target="_blank">',
                            '</a>'
                        ); ?></li>
                    <li><?php echo sprintf(
                            esc_html__('%1$sEnhancing Data Accuracy%2$s', 'wp-statistics'),
                            '<a href="https://wp-statistics.com/resources/enhancing-data-accuracy/?utm_source=wp-statistics&utm_medium=link&utm_campaign=about" target="_blank">',
                            '</a>'
                        ); ?></li>
                    <li><?php echo sprintf(
                            esc_html__('%1$sTroubleshoot with Cache Plugins%2$s', 'wp-statistics'),
                            '<a href="https://wp-statistics.com/resources/troubleshoot-with-cache-plugins/?utm_source=wp-statistics&utm_medium=link&utm_campaign=about" target="_blank">',
                            '</a>'
                        ); ?></li>
                    <li><?php echo sprintf(
                            esc_html__('%1$sContact Us%2$s', 'wp-statistics'),
                            '<a title="' .
                            esc_html__('Contact Us', 'wp-statistics') .
                            '" href="https://wp-statistics.com/contact-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=about">',
                            '</a>'
                        ); ?></li>
                </ul>

                <p><?php esc_html_e('Quick Troubleshooting Tips', 'wp-statistics'); ?></p>

                <ol style="padding-left: 15px;">
                    <li><?php _e('Check your PHP <code>memory_limit</code>.', 'wp-statistics'); ?></li>
                    <li><?php esc_html_e('Disable conflicting plugins.', 'wp-statistics'); ?></li>
                    <li><?php esc_html_e('Validate plugin settings.', 'wp-statistics'); ?></li>
                    <li><?php esc_html_e('Clear cache if using caching plugins.', 'wp-statistics'); ?></li>
                    <li><?php esc_html_e('Check your site\'s error logs.', 'wp-statistics'); ?></li>
                </ol>

                <p><?php echo sprintf(__('If your issue persists, open a thread on the %1$sWordPress.org support forum%2$s, and our team will assist you promptly.', 'wp-statistics'), '<a href="https://wordpress.org/support/plugin/wp-statistics" target="_blank">', '</a>');  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  ?></p>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php esc_html_e('Translations', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p style="display: inline-block"><?php esc_html_e('WP Statistics supports multiple languages. Help us reach more users by contributing to our translations.', 'wp-statistics') ?></p>
                <a href="https://wp-statistics.com/translations/?utm_source=wp-statistics&utm_medium=link&utm_campaign=about" class="button" target="_blank" style="font-size: 12px; margin: 2px 5px 0; padding: 5px 15px; transform: translateY(-3px);"><?php esc_html_e('Contribute to Translations', 'wp-statistics'); ?></a>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php esc_html_e('More from VeronaLabs', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p style="display: inline-block"><?php esc_html_e('Explore other plugins and tools designed to enhance your WordPress experience.', 'wp-statistics') ?></p>
                <a href="https://veronalabs.com/?utm_source=wp-statistics&utm_medium=link&utm_campaign=about" class="button" target="_blank" style="font-size: 12px; margin: 2px 5px 0; padding: 5px 15px; transform: translateY(-3px);"><?php esc_html_e('VeronaLabs Products/Plugins', 'wp-statistics'); ?></a>
            </td>
        </tr>
        </tbody>
    </table>
</div>
