<div class="wrap wps-wrap">
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php _e('Resources/Information', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('Current PHP Memory Consumption', 'wp-statistics'); ?>
                </th>
                <td>
                    <strong><?php echo size_format(memory_get_usage(), 3); ?></strong>
                    <p class="description"><?php _e('Displays the amount of memory currently being used by PHP on your server.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('Maximum Allowed PHP Memory', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo ini_get('memory_limit'); ?></strong>
                    <p class="description"><?php _e('This is the maximum amount of memory PHP can use on your server. Increasing this value might improve performance but ensure you don\'t exceed your server\'s limits.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <?php
            foreach ($result as $table_name => $data) {
                ?>
                <tr valign="top">
                    <th scope="row">
                        <?php echo sprintf(__('Number of rows in the %s table', 'wp-statistics'), '<code>' . esc_attr($table_name) . '</code>'); ?>
                    </th>
                    <td>
                        <strong><?php echo number_format_i18n($data['rows']); ?></strong> <?php echo _n('Row', 'Rows', number_format_i18n($data['rows']), 'wp-statistics'); ?>
                        <p class="description"><?php echo $data['desc'] ?></p>
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
                <th scope="row" colspan="2"><h3><?php _e('Version Info', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('WP Statistics Version', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo WP_STATISTICS_VERSION; ?></strong>
                    <p class="description"><?php _e('The currently installed and active version of the WP Statistics plugin.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('PHP Version', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo phpversion(); ?></strong>
                    <p class="description"><?php _e('The PHP version currently running on your server. Some features may require specific PHP versions to function correctly.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('PHP Safe Mode Status', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (ini_get('safe_mode')) {
                            _e('Yes', 'wp-statistics');
                        } else {
                            _e('No', 'wp-statistics');
                        } ?></strong>

                    <p class="description"><?php _e('Indicates if PHP Safe Mode is active. Some functions might be restricted when in Safe Mode.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('IPv6 Support in PHP', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (defined('AF_INET6')) {
                            _e('Yes', 'wp-statistics');
                        } else {
                            _e('No', 'wp-statistics');
                        } ?></strong>
                    <p class="description"><?php _e('Indicates if your PHP installation supports IPv6 addresses. This affects how IP addresses are recorded and displayed.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('jQuery Version', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong>
                        <script type="text/javascript">document.write(jQuery().jquery);</script>
                    </strong>

                    <p class="description"><?php _e('The version of jQuery your website is using. Keeping this up-to-date ensures compatibility and optimized website functionality.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('Installed cURL Version ', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (function_exists('curl_version')) {
                            $curl_ver = curl_version();
                            echo esc_attr($curl_ver['version']);
                        } else {
                            _e('cURL not installed', 'wp-statistics');
                        } ?></strong>

                    <p class="description"><?php _e('Indicates the version of cURL installed on your server. cURL is a tool for transferring data and is essential for various web operations.', 'wp-statistics'
                        ); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('zlib Compression Status', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (function_exists('gzopen')) {
                            _e('Installed', 'wp-statistics');
                        } else {
                            _e('Not installed', 'wp-statistics');
                        } ?></strong>

                    <p class="description"><?php _e('zlib is a software library used for data compression. The <code>gopen()</code> function is a requirement for GeoIP database operations.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('GMP Extension Status', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (extension_loaded('gmp')) {
                            _e('Installed', 'wp-statistics');
                        } else {
                            _e('Not installed', 'wp-statistics');
                        } ?></strong>

                    <p class="description"><?php _e('The GNU Multiple Precision (GMP) is a PHP extension used for arithmetic operations. It\'s required for reading the GeoIP database efficiently.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('BCMatH Extension Status', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php if (extension_loaded('bcmath')) {
                            _e('Installed', 'wp-statistics');
                        } else {
                            _e('Not installed', 'wp-statistics');
                        } ?></strong>

                    <p class="description"><?php _e('The Binary Calculator Mathematics (BCMatH) extension is used for arbitrary precision mathematics in PHP. Like GMP, it\'s also essential for certain operations on the GeoIP database.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php _e('File Info', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('GeoIP Database Size and Date', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php
                        $GeoIP_filename = \WP_STATISTICS\GeoIP::get_geo_ip_path('country');
                        $GeoIP_filedate = @filemtime($GeoIP_filename);

                        if ($GeoIP_filedate === false) {
                            _e('Database file does not exist.', 'wp-statistics');
                        } else {
                            echo size_format(@filesize($GeoIP_filename), 2) . __(', created on ',
                                    'wp-statistics') . date_i18n(get_option('date_format') . ' @ ' . get_option('time_format'),
                                    $GeoIP_filedate);
                        } ?></strong>

                    <p class="description"><?php _e('Displays the size and last updated date of the GeoIP database used for location-based statistics.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php _e('Client Info', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('Your IP Address', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo \WP_STATISTICS\IP::getIP(); ?></strong>
                    <p class="description"><?php _e('This is the IP address you\'re currently accessing the website from.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('Your Browser\'s User Agent', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo esc_textarea(\WP_STATISTICS\UserAgent::getHttpUserAgent()); ?></strong>
                    <p class="description"><?php _e('Displays information about the browser and operating system you\'re using.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('Your Web Browser', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php $agent = \WP_STATISTICS\UserAgent::getUserAgent();
                        echo esc_attr($agent['browser']);
                        ?></strong>

                    <p class="description"><?php _e('The web browser you are using to access the dashboard.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('Browser Version', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo esc_attr($agent['version']); ?></strong>
                    <p class="description"><?php _e('The specific version number of the browser you\'re using.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php _e('Operating System', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo esc_attr($agent['platform']); ?></strong>
                    <p class="description"><?php _e('The operating system of the device you\'re using to access the dashboard.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php _e('Server Info', 'wp-statistics'); ?></h3></th>
            </tr>

            <?php
            $list = array(
                'SERVER_SOFTWARE'      => __('The web server software running on your hosting server.', 'wp-statistics'),
                'HTTP_HOST'            => __('The domain name of your website as recognized by the server.', 'wp-statistics'),
                'REMOTE_ADDR'          => __('The IP address of the server where your website is hosted.', 'wp-statistics'),
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
                    ' . $server . '
                    </th>
                    <td>
                        <strong>' . esc_attr($_SERVER[$server]) . '</strong>
                        <p class="description">' . $desc . '</p>
                    </td>
                </tr>';
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
