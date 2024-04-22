<div class="wrap wps-wrap">
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Resources/Information', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('Current PHP Memory Consumption', 'wp-statistics'); ?>
                </th>
                <td>
                    <strong><?php echo esc_html(size_format(memory_get_usage(), 3)); ?></strong>
                    <p class="description"><?php esc_html_e('Displays the amount of memory currently being used by PHP on your server.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('Maximum Allowed PHP Memory', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo esc_html(ini_get('memory_limit')); ?></strong>
                    <p class="description"><?php esc_html_e('This is the maximum amount of memory PHP can use on your server. Increasing this value might improve performance but ensure you don\'t exceed your server\'s limits.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <?php
            foreach ($result as $table_name => $data) {
                ?>
                <tr valign="top">
                    <th scope="row">
                        <?php echo sprintf(esc_html__('Number of rows in the %s table', 'wp-statistics'), '<code>' . esc_attr($table_name) . '</code>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped   ?>
                    </th>
                    <td>
                        <strong><?php echo esc_html(number_format_i18n($data['rows'])); ?></strong> <?php echo esc_html(_n('Row', 'Rows', number_format_i18n($data['rows']), 'wp-statistics')); ?>
                        <p class="description"><?php echo wp_kses_data($data['desc']) ?></p>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Version Info', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('WP Statistics Version', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo WP_STATISTICS_VERSION; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></strong>
                    <p class="description"><?php esc_html_e('The currently installed and active version of the WP Statistics plugin.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('PHP Version', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo esc_html(phpversion()); ?></strong>
                    <p class="description"><?php esc_html_e('The PHP version currently running on your server. Some features may require specific PHP versions to function correctly.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('PHP Safe Mode Status', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (ini_get('safe_mode')) {
                            esc_html_e('Yes', 'wp-statistics');
                        } else {
                            esc_html_e('No', 'wp-statistics');
                        } ?></strong>

                    <p class="description"><?php esc_html_e('Indicates if PHP Safe Mode is active. Some functions might be restricted when in Safe Mode.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('IPv6 Support in PHP', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (defined('AF_INET6')) {
                            esc_html_e('Yes', 'wp-statistics');
                        } else {
                            esc_html_e('No', 'wp-statistics');
                        } ?></strong>
                    <p class="description"><?php esc_html_e('Indicates if your PHP installation supports IPv6 addresses. This affects how IP addresses are recorded and displayed.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('jQuery Version', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong>
                        <script type="text/javascript">document.write(jQuery().jquery);</script>
                    </strong>

                    <p class="description"><?php esc_html_e('The version of jQuery your website is using. Keeping this up-to-date ensures compatibility and optimized website functionality.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('Installed cURL Version ', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (function_exists('curl_version')) {
                            $curl_ver = curl_version();
                            echo esc_attr($curl_ver['version']);
                        } else {
                            esc_html_e('cURL not installed', 'wp-statistics');
                        } ?></strong>

                    <p class="description"><?php esc_html_e('Indicates the version of cURL installed on your server. cURL is a tool for transferring data and is essential for various web operations.', 'wp-statistics'
                        ); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('zlib Compression Status', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (function_exists('gzopen')) {
                            esc_html_e('Installed', 'wp-statistics');
                        } else {
                            esc_html_e('Not installed', 'wp-statistics');
                        } ?></strong>

                    <p class="description"><?php esc_html_e('zlib is a software library used for data compression. The <code>gopen()</code> function is a requirement for GeoIP database operations.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('GMP Extension Status', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (extension_loaded('gmp')) {
                            esc_html_e('Installed', 'wp-statistics');
                        } else {
                            esc_html_e('Not installed', 'wp-statistics');
                        } ?></strong>

                    <p class="description"><?php esc_html_e('The GNU Multiple Precision (GMP) is a PHP extension used for arithmetic operations. It\'s required for reading the GeoIP database efficiently.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('BCMatH Extension Status', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (extension_loaded('bcmath')) {
                            esc_html_e('Installed', 'wp-statistics');
                        } else {
                            esc_html_e('Not installed', 'wp-statistics');
                        } ?></strong>

                    <p class="description"><?php esc_html_e('The Binary Calculator Mathematics (BCMatH) extension is used for arbitrary precision mathematics in PHP. Like GMP, it\'s also essential for certain operations on the GeoIP database.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('File Info', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('GeoIP Database Size and Date', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php
                        $GeoIP_filename = \WP_STATISTICS\GeoIP::get_geo_ip_path('country');
                        $GeoIP_filedate = @filemtime($GeoIP_filename);

                        if ($GeoIP_filedate === false) {
                            esc_html_e('Database file does not exist.', 'wp-statistics');
                        } else {
                            echo esc_html(size_format(@filesize($GeoIP_filename), 2)) . esc_html__(', created on ',
                                    'wp-statistics') . esc_html(date_i18n(get_option('date_format')) . ' @ ' . esc_html(get_option('time_format')),
                                    esc_html($GeoIP_filedate));
                        } ?></strong>

                    <p class="description"><?php esc_html_e('Displays the size and last updated date of the GeoIP database used for location-based statistics.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Client Info', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('Your IP Address', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo esc_html(\WP_STATISTICS\IP::getIP()); ?></strong>
                    <p class="description"><?php esc_html_e('This is the IP address you\'re currently accessing the website from.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('Your Browser\'s User Agent', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo esc_textarea(\WP_STATISTICS\UserAgent::getHttpUserAgent()); ?></strong>
                    <p class="description"><?php esc_html_e('Displays information about the browser and operating system you\'re using.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('Your Web Browser', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php $agent = \WP_STATISTICS\UserAgent::getUserAgent();
                        echo esc_attr($agent['browser']);
                        ?></strong>

                    <p class="description"><?php esc_html_e('The web browser you are using to access the dashboard.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('Browser Version', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo esc_attr($agent['version']); ?></strong>
                    <p class="description"><?php esc_html_e('The specific version number of the browser you\'re using.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('Operating System', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo esc_attr($agent['platform']); ?></strong>
                    <p class="description"><?php esc_html_e('The operating system of the device you\'re using to access the dashboard.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Server Info', 'wp-statistics'); ?></h3></th>
            </tr>

            <?php
            $list = array(
                'SERVER_SOFTWARE'      => __('The web server software running on your hosting server.', 'wp-statistics'),
                'HTTP_HOST'            => __('The domain name of your website as recognized by the server.', 'wp-statistics'),
                'SERVER_ADDR'          => __('The IP address of the server where your website is hosted.', 'wp-statistics'),
                'HTTP_CLIENT_IP'       => __('Used by some proxies or load balancers to relay the original IP. Enable this if your setup uses the `HTTP_CLIENT_IP` header to identify visitor IPs.', 'wp-statistics'),
                'HTTP_X_FORWARDED_FOR' => __('If your server is behind a proxy, this is the original IP address forwarded by the proxy.', 'wp-statistics'),
                'HTTP_X_FORWARDED'     => __('Another header set by certain proxies or load balancers. If your server uses the `HTTP_X_FORWARDED` header for IP forwarding, activate this.', 'wp-statistics'),
                'HTTP_FORWARDED_FOR'   => __('A common header containing the original IP, often used by multiple proxies in a chain. WP Statistics will extract the real IP from this header when enabled.', 'wp-statistics'),
                'HTTP_FORWARDED'       => __('A standardized header for proxies. Activate if your environment uses the `HTTP_FORWARDED` header to determine visitor IPs.', 'wp-statistics'),
                'HTTP_X_REAL_IP'       => __('Set by services like the Nginx proxy to indicate the true client IP. Turn this on if your server environment uses the <code>HTTP_X_REAL_IP</code> header.', 'wp-statistics'),
            );
            foreach ($list as $server => $desc) {
                if (isset($_SERVER[$server])) {
                    echo '<tr valign="top">
                     <th scope="row">
                    ' . esc_html($server) . '
                    </th>
                    <td>
                        <strong>' . esc_attr($_SERVER[$server]) . '</strong>
                        <p class="description">' . wp_kses_data($desc) . '</p>
                    </td>
                </tr>';
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
