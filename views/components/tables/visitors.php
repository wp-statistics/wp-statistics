
<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table wps-table-inspect">
                <thead>
                <tr>
                    <th class="wps-pd-l">
                        <span class="wps-order"><?php esc_html_e('Last View', 'wp-statistics') ?></span>
                     </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Visitor Information', 'wp-statistics') ?>
                    </th>
                    <th class="wps-pd-l">
                         <?php esc_html_e('Location', 'wp-statistics') ?>
                    </th>
                    <th class="wps-pd-l">
                         <?php esc_html_e('Referrer', 'wp-statistics') ?>
                    </th>
                    <th class="wps-pd-l">
                         <?php esc_html_e('Total Views', 'wp-statistics') ?>
                    </th>
                    <th class="wps-pd-l">
                         <?php esc_html_e('Latest Page', 'wp-statistics') ?>
                    </th>
                 </tr>
                </thead>

                <tbody>
                <?php foreach ($data as $author) : ?>
                    <tr>
                        <td class="wps-pd-l">10 Mar, 4:15 pm</td>
                        <td class="wps-pd-l">
                            <ul class="wps-browsers__flags">
                                <li class="wps-browsers__flag">

                                    <div class="wps-tooltip" data-tooltip-content="#tooltip_user_id">
                                        <a href=""><img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/user-icon.svg') ?>" alt="user"  width="15" height="15"></a>
                                    </div>
                                    <div class="wps-tooltip_templates">
                                        <div id="tooltip_user_id">
                                           <div><?php esc_html_e('ID', 'wp-statistics') ?>: #2777</div>
                                           <div><?php esc_html_e('Email', 'wp-statistics') ?>: hello@amzconsult.ca</div>
                                           <div><?php esc_html_e('Role', 'wp-statistics') ?>: subscriber</div>
                                        </div>
                                    </div>
                                 </li>
                                <li class="wps-browsers__flag">
                                    <div class="wps-tooltip" title="Incognito">
                                        <a href=""><img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/incognito-user.svg') ?>" alt="Incognito"  width="15" height="15"></a>
                                    </div>
                                </li>
                                <li class="wps-browsers__flag">
                                    <div class="wps-tooltip" title="Chrome">
                                        <a href=""><img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/browser/chrome.svg') ?>" alt="Chrome"  width="15" height="15"></a>
                                    </div>
                                </li>
                                <li class="wps-browsers__flag">
                                    <div class="wps-tooltip" title="Firefox">
                                        <a href=""><img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/browser/firefox.svg') ?>" alt="Firefox"  width="15" height="15"></a>
                                    </div>
                                </li>
                            </ul>
                        </td>
                        <td class="wps-pd-l">
                            <div class="wps-country-flag wps-ellipsis-parent">
                                <img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/flags/ac.svg') ?>" alt="California, Los Angeles"  width="15" height="15">
                                <span class="wps-ellipsis-text">California, Los Angeles</span>
                            </div>
                        </td>
                        <td class="wps-pd-l">
                            <a target="_blank" href="" title="google.com" class="wps-link-arrow">
                                <span>google.com</span>
                            </a>
                        </td>
                        <td class="wps-pd-l">
                            <a href="" title="">1325</a>
                        </td>
                        <td class="wps-pd-l">
                            <a target="_blank" href="" title="Home Page"  class="wps-link-arrow">
                                <span>Home Page</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="o-wrap o-wrap--no-data wps-center">
            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
        </div>
    <?php endif; ?>
</div>
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>