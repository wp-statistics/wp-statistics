<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <td scope="row" align="center">
                <a href="https://wp-statistics.com" target="_blank">
                    <img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/logo-250.png'); ?>">
                </a>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" align="center">
                <h2><?php echo sprintf(__('Version %s', 'wp-statistics'), esc_attr(WP_STATISTICS_VERSION)); ?></h2>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" align="center">
                <?php echo sprintf(
                    __('Developed by %s, featuring GeoLite2 data by %s. Artwork and design contributions by %s.', 'wp-statistics'),
                    '<a href="https://veronalabs.com" target=_blank>VeronaLabs</a>',
                    '<a href="https://www.maxmind.com" target=_blank>MaxMind</a>',
                    '<a href="https://veronalabs.com" target=_blank>VeronaLabs</a>',
                ); ?>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" align="center">
                <hr/>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php _e('Support Our Development', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p style="display: inline-block"><?php _e('Your contributions help us maintain and enhance WP Statistics. Show your support!', 'wp-statistics') ?></p>
                <a href="https://wp-statistics.com/donate" class="button" target="_blank" style="font-size: 12px; margin: 2px 5px 0; padding: 5px 15px; transform: translateY(-3px);"><?php _e('Donate', 'wp-statistics'); ?></a>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php _e('Explore Our Website', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p style="display: inline-block"><?php _e('Discover more features, tutorials, and updates on our official site.', 'wp-statistics') ?></p>
                <a href="https://wp-statistics.com/" class="button" target="_blank" style="font-size: 12px; margin: 2px 5px 0; padding: 5px 15px; transform: translateY(-3px);"><?php _e('Visit Us', 'wp-statistics'); ?></a>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php _e('Feedback & Reviews', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p style="display: inline-block"><?php _e('Your feedback is invaluable. Rate and review us on WordPress.org and help others discover WP Statistics.', 'wp-statistics') ?></p>
                <a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post" class="button" target="_blank" style="font-size: 12px; margin: 2px 5px 0; padding: 5px 15px; transform: translateY(-3px);"><?php _e('Rate Us', 'wp-statistics'); ?></a>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php _e('Need Help? Check These First', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p><?php _e("Before contacting us, these resources might address your concerns:", 'wp-statistics'); ?></p>

                <ul style="list-style-type: disc; list-style-position: inside; padding-left: 25px;">
                    <li><?php echo sprintf(
                            __('Frequently Asked Questions (%sFAQs%s)', 'wp-statistics'),
                            '<a title="' .
                            __('FAQs', 'wp-statistics') .
                            '" href="https://wp-statistics.com/category/faq/" target="_blank">',
                            '</a>'
                        ); ?></li>
                    <li><?php echo sprintf(
                            __('%sDocumentation%s and User Guides', 'wp-statistics'),
                            '<a title="' .
                            __('Documentation', 'wp-statistics') .
                            '" href="https://wp-statistics.com/category/documentation/">',
                            '</a>'
                        ); ?></li>
                    <li><?php echo sprintf(
                            __('%sSupport Forum%s for Common Issues', 'wp-statistics'),
                            '<a href="https://wordpress.org/support/plugin/wp-statistics" target="_blank">',
                            '</a>'
                        ); ?></li>
                    <li><?php _e('Ensure access to your PHP error logs', 'wp-statistics'); ?></li>
                    <li><?php echo sprintf(
                            __('%sContact Us%s', 'wp-statistics'),
                            '<a title="' .
                            __('Contact Us', 'wp-statistics') .
                            '" href="https://wp-statistics.com/contact-us/">',
                            '</a>'
                        ); ?></li>
                </ul>

                <p><?php _e('Quick Troubleshooting Tips', 'wp-statistics'); ?></p>

                <ol style="padding-left: 15px;">
                    <li><?php _e('Check your PHP <code>memory_limit</code>.', 'wp-statistics'); ?></li>
                    <li><?php _e('Disable conflicting plugins.', 'wp-statistics'); ?></li>
                    <li><?php _e('Validate plugin settings.', 'wp-statistics'); ?></li>
                    <li><?php _e('Clear cache if using caching plugins.', 'wp-statistics'); ?></li>
                    <li><?php _e('Check your site\'s error logs.', 'wp-statistics'); ?></li>
                </ol>

                <p><?php echo sprintf(__('If your issue persists, open a thread on the %sWordPress.org support forum%s, and our team will assist you promptly.', 'wp-statistics'), '<a href="https://wordpress.org/support/plugin/wp-statistics" target="_blank">', '</a>'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php _e('Translations', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p style="display: inline-block"><?php _e('WP Statistics supports multiple languages. Help us reach more users by contributing to our translations.', 'wp-statistics') ?></p>
                <a href="https://wp-statistics.com/translations/" class="button" target="_blank" style="font-size: 12px; margin: 2px 5px 0; padding: 5px 15px; transform: translateY(-3px);"><?php _e('Contribute to Translations', 'wp-statistics'); ?></a>
            </td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><h2><?php _e('More from VeronaLabs', 'wp-statistics'); ?></h2></td>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2">
                <p style="display: inline-block"><?php _e('Explore other plugins and tools designed to enhance your WordPress experience.', 'wp-statistics') ?></p>
                <a href="https://veronalabs.com/#product" class="button" target="_blank" style="font-size: 12px; margin: 2px 5px 0; padding: 5px 15px; transform: translateY(-3px);"><?php _e('VeronaLabs Products/Plugins', 'wp-statistics'); ?></a>
            </td>
        </tr>
        </tbody>
    </table>
</div>
