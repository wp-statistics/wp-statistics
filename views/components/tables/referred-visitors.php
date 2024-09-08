<?php
use WP_STATISTICS\Admin_Template;
?>

    <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Last View', 'wp-statistics') ?></span>
                        </th>
                        <th class="wps-pd-l">
                            <span><?php esc_html_e('Referrer', 'wp-statistics') ?></span>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Traffic Category', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Visitor Information', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Location', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php echo esc_html__('Landing Page', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php echo esc_html__('Number of Views', 'wp-statistics'); ?>
                        </th>
                    </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="wps-pd-l">
                                10 Mar, 4:15 pm
                            </td>

                            <td class="wps-pd-l">
                                <a href="" title="google.com" target="_blank" class="wps-link-arrow">
                                    <span>google.com</span>
                                </a>
                            </td>

                            <td class="wps-pd-l">
                                <div class="wps-ellipsis-parent">
                                    <span class="wps-ellipsis-text" title="Organic Search">Organic Search</span>
                                </div>
                            </td>

                            <td class="wps-pd-l">

                                <ul class="wps-browsers__flags">
                                    <li class="wps-browsers__flag">
                                        <div class="wps-tooltip" title="">
                                            <a href=""><img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/operating-system/windows.svg') ?>" alt="" width="15" height="15"></a>
                                        </div>
                                    </li>

                                    <li class="wps-browsers__flag">
                                        <div class="wps-tooltip" title="">
                                            <a href=""><img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/browser/edge.svg') ?>" alt="" width="15" height="15"></a>
                                        </div>
                                    </li>

                                    <li class="wps-browsers__flag">
                                        <div class="wps-tooltip" title="">
                                            <a href=""><img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/incognito-user.svg') ?>" alt="<?php esc_attr_e('Incognito', 'wp-statistics') ?>" width="15" height="15"></a>
                                        </div>
                                    </li>

                                    <li class="wps-browsers__flag">
                                        <div class="wps-tooltip" data-tooltip-content="#tooltip_user_id">
                                            <a href=""><img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/user-icon.svg') ?>" alt="user name" width="15" height="15"></a>
                                        </div>
                                        <div class="wps-tooltip_templates">
                                            <div id="tooltip_user_id">
                                                <div><?php esc_html_e('ID: ', 'wp-statistics') ?> user_id</div>
                                                <div><?php esc_html_e('Email: ', 'wp-statistics') ?> user_email</div>
                                                <div><?php esc_html_e('Daily Visitor Hash: ', 'wp-statistics') ?> 12345</div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </td>

                            <td class="wps-pd-l">
                                <div class="wps-country-flag wps-ellipsis-parent">
                                    <a href="" class="wps-tooltip" title="">
                                        <img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/flags/000.svg') ?>" alt="" width="15" height="15">
                                    </a>
                                     <span class="wps-ellipsis-text" title="Île-de-France,  Paris">Île-de-France,  Paris</span>
                                </div>
                            </td>

                            <td class="wps-pd-l">
                                <?php if (!empty($page)) : ?>
                                    <a target="_blank" href="" title="title" class="wps-link-arrow">
                                        <span>title</span>
                                    </a>
                                <?php else : ?>
                                    <?php echo Admin_Template::UnknownColumn() ?>
                                <?php endif; ?>
                            </td>
                            <td class="wps-pd-l">
                                <a href="">
                                    13
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
            </div>
    </div>
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>