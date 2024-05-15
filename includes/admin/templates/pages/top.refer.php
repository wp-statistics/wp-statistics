<ul class="subsubsub">
    <li class="all">
        <a class="current" href="<?php echo esc_url(\WP_STATISTICS\Menus::admin_url('referrers')); ?>">
            <?php esc_html_e('All', 'wp-statistics'); ?>
            <span class="count">(<?php echo esc_html(number_format_i18n($total)); ?>)</span>
        </a>
    </li>
</ul>
<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (count($list) < 1) { ?>
                        <div class='wps-wrap--no-content wps-center'><?php esc_html_e("No recent data available.", "wp-statistics"); ?></div>
                    <?php } else { ?>
                        <div class="o-table-wrapper">
                            <table class="o-table o-table--link" id="top-referring">
                                <tr>
                                    <td></td>
                                    <td><?php esc_html_e('Domain Address', 'wp-statistics'); ?></td>
                                    <td><?php esc_html_e('Site Title', 'wp-statistics'); ?></td>
                                    <td><?php esc_html_e('Server IP', 'wp-statistics'); ?></td>
                                    <?php if (\WP_STATISTICS\GeoIP::active()) { ?>
                                        <td><?php esc_html_e('Country', 'wp-statistics'); ?></td>
                                    <?php } ?>
                                    <td><?php esc_html_e('Number of Referrals', 'wp-statistics'); ?></td>
                                </tr>
                                <?php foreach ($list as $item) { ?>

                                    <tr>
                                        <td><?php echo esc_html(number_format_i18n($item['rate'])); ?></td>
                                        <td><?php echo WP_STATISTICS\Helper::show_site_icon($item['domain']) . " " . \WP_STATISTICS\Referred::get_referrer_link($item['domain'], $item['title']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  ?>
                                        </td>
                                        <td><?php echo trim($item['title']) == "" ? \WP_STATISTICS\Admin_Template::UnknownColumn() : esc_attr($item['title']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	?>
                                        </td>
                                        <td><?php echo trim($item['ip']) == "" ? \WP_STATISTICS\Admin_Template::UnknownColumn() : esc_attr($item['ip']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                                        <?php if (\WP_STATISTICS\GeoIP::active()) { ?>
                                            <td><?php echo trim($item['country']) == "" ? \WP_STATISTICS\Admin_Template::UnknownColumn() : "<img src='" . esc_url($item['flag']) . "' title='" . esc_attr($item['country']) . "' alt='" . esc_attr($item['country']) . "' class='log-tools wps-flag'/>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></td>
                                        <?php } ?>
                                        <td>
                                            <a class='wps-text-success' href='<?php echo esc_url($item['page_link']); ?>'>
                                                <?php echo esc_html($item['number']); ?>
                                                <svg style="margin-top: 3px;" width="10" height="10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M4.82751 4.99993 2.62209 2.79451c-.0759-.07859-.1179-.18384-.11695-.29309.00095-.10924.04477-.21375.12203-.291.07725-.07726.18176-.12108.291-.12203.10925-.00094.2145.04105.29309.11695l2.5 2.5c.07811.07814.12199.1841.12199.29459 0 .11048-.04388.21644-.12199.29458l-2.5 2.5c-.07859.0759-.18384.1179-.29309.11695-.10924-.00095-.21375-.04477-.291-.12203-.07726-.07725-.12108-.18176-.12203-.291-.00095-.10925.04105-.2145.11695-.29309l2.20542-2.20541Z" fill="#404BF2" fill-opacity=".5"/>
                                                    <path d="M7.87792 5.13371 5.67251 2.9283c-.0759-.07859-.1179-.18384-.11695-.29309.00095-.10924.04477-.21375.12202-.291.07726-.07726.18176-.12108.29101-.12203.10925-.00095.2145.04105.29308.11695l2.5 2.5c.07812.07814.122.1841.122.29458 0 .11049-.04388.21645-.122.29459l-2.5 2.5c-.07858.0759-.18383.11789-.29308.11695-.10925-.00095-.21375-.04477-.29101-.12203-.07725-.07725-.12107-.18176-.12202-.29101-.00095-.10924.04105-.2145.11695-.29308l2.20541-2.20542Z" fill="#404BF2"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>

                                <?php } ?>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </div>
</div>