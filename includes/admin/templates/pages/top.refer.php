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
                            <table class="o-table" id="top-referring">
                                <tr>
                                    <td><?php esc_html_e('Rating', 'wp-statistics'); ?></td>
                                    <td><?php esc_html_e('Site Url', 'wp-statistics'); ?></td>
                                    <td><?php esc_html_e('Site Title', 'wp-statistics'); ?></td>
                                    <td><?php esc_html_e('Server IP', 'wp-statistics'); ?></td>
			                        <?php if (\WP_STATISTICS\GeoIP::active()) { ?>
                                        <td><?php esc_html_e('Country', 'wp-statistics'); ?></td>
			                        <?php } ?>
                                    <td><?php esc_html_e('Referral Sources', 'wp-statistics'); ?></td>
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
                                            <a class='wps-text-success' href='<?php echo esc_url($item['page_link']); ?>'><?php echo esc_attr($item['number']); ?></a>
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