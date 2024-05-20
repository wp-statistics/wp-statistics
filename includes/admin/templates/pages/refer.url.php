<?php 
use WP_STATISTICS\Helper;
?>

<ul class="subsubsub">
    <li class="all">
        <a href="<?php echo esc_url(\WP_STATISTICS\Menus::admin_url('referrers')); ?>"><?php esc_html_e('All', 'wp-statistics'); ?></a>
    </li>
    |
    <li>
        <a class="current" href="<?php echo esc_url(add_query_arg(array('referrer' => $args['domain']))); ?>">
            <?php echo esc_attr($args['domain']); ?>
            <span class="count">(<?php echo esc_html(number_format_i18n($total)); ?>)</span>
        </a>
    </li>
</ul>

<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="postbox-header postbox-toggle">
                    <h2 class="hndle wps-d-inline-block"><span><?php echo esc_attr($title); ?></span></h2>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php echo sprintf(__('Toggle panel: %s', 'wp-statistics'), esc_attr($title)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="inside">
                    <?php if (count($list) < 1) { ?>
                        <div class='wps-wrap--no-content wps-center'><?php esc_html_e("No recent data available.", "wp-statistics"); ?></div>
                    <?php } else { ?>
                        <table width="100%" class="widefat table-stats" id="top-referring">
                            <tr>
                                <td><?php esc_html_e('Link', 'wp-statistics'); ?></td>
                                <td><?php echo esc_html(\WP_STATISTICS\Option::get('hash_ips') == true ? __('Daily Visitor Hash', 'wp-statistics') : __('IP Address', 'wp-statistics')); ?></td>
                                <td><?php esc_html_e('Browser', 'wp-statistics'); ?></td>
                                <?php if (\WP_STATISTICS\GeoIP::active()) { ?>
                                    <td><?php esc_html_e('Country', 'wp-statistics'); ?></td>
                                <?php } ?>
                                <td><?php esc_html_e('Date', 'wp-statistics'); ?></td>
                                <td></td>
                            </tr>
                            <?php foreach ($list as $item) { ?>
                                <tr>
                                    <td style="text-align: left" class="wps-admin-column__referred">
                                        <a href="<?php echo esc_url($item['refer']); ?>" target="_blank" title="<?php echo esc_attr($item['refer']); ?>"><?php echo esc_html(Helper::get_domain_name($item['refer'])); ?></a>
                                    </td>
                                    <td style='text-align: left;' class="wps-admin-column__ip">
                                        <?php echo sprintf('<a href="%s">%s</a>', esc_url($item['ip']['link']), esc_attr($item['ip']['value'])); ?>
                                    </td>
                                    <td style="text-align: left">
                                        <a href="<?php echo esc_url($item['browser']['link']); ?>" title="<?php echo esc_attr($item['browser']['name']); ?>"><img src="<?php echo esc_url($item['browser']['logo']); ?>" alt="<?php echo esc_attr($item['browser']['name']); ?>" class="log-tools wps-flag" title="<?php echo esc_attr($item['browser']['name']); ?>"/></a>
                                    </td>
                                    <?php if (WP_STATISTICS\GeoIP::active()) { ?>
                                        <td style="text-align: left">
                                            <img src="<?php echo esc_url($item['country']['flag']); ?>" alt="<?php echo esc_attr($item['country']['name']); ?>" title="<?php echo esc_attr($item['country']['name']); ?>" class="log-tools wps-flag"/>
                                        </td>
                                    <?php } ?>
                                    <td style="text-align: left"><?php echo esc_attr($item['date']); ?></td>
                                    <td style='text-align: center'><?php echo(isset($item['map']) ? "<a class='wps-text-muted' href='" . esc_url($item['ip']['link']) . "'>" . WP_STATISTICS\Admin_Template::icons('dashicons-visibility') . "</a><a class='show-map wps-text-muted' href='" . esc_url($item['map']) . "' target='_blank' title='" . __('Map', 'wp-statistics') . "'>" . WP_STATISTICS\Admin_Template::icons('dashicons-location-alt') . "</a>" : ""); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } ?>
                </div>
            </div>
            <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </div>
</div>