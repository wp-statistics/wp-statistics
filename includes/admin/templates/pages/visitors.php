<?php 
use WP_STATISTICS\GeoIP;
use WP_STATISTICS\Option;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
?>

<ul class="subsubsub wp-statistics-sub-fullwidth">
    <?php
    foreach ($sub as $key => $item) {
        ?>
        <li class="all">
            <a <?php if ($item['active'] === true) { ?> class="current" <?php } ?> href="<?php echo esc_url($item['link']); ?>">
                <?php echo esc_attr($item['title']); ?>
                <span class='count'>(<?php echo esc_html(number_format_i18n($item['count'])); ?>)</span>
            </a>
        </li>
        <?php $sub_keys = array_keys($sub);
        if (end($sub_keys) != $key) { ?> | <?php } ?><?php } ?>
</ul>

<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!is_array($list) || (is_array($list) and count($list) < 1)) { ?>
                        <div class='wps-wrap--no-content wps-center'><?php esc_html_e("No recent data available.", "wp-statistics"); ?></div>
                    <?php } else { ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table">
                                <tr>
                                    <td><?php esc_html_e('Browser', 'wp-statistics'); ?></td>
                                    <?php if (GeoIP::active()) { ?>
                                        <td><?php esc_html_e('Country', 'wp-statistics'); ?></td>
                                    <?php } ?>
                                    <?php if (GeoIP::active('city')) { ?>
                                        <td><?php esc_html_e('City', 'wp-statistics'); ?></td>
                                        <td><?php esc_html_e('Region', 'wp-statistics'); ?></td>
                                    <?php } ?>
                                    <td>
                                        <a href="<?php echo esc_url(add_query_arg('order', ((isset($_GET['order']) and $_GET['order'] == "asc") ? 'desc' : 'asc'))); ?>">
                                            <?php esc_html_e('Last View', 'wp-statistics'); ?>
                                            <span class="dashicons dashicons-arrow-<?php echo((isset($_GET['order']) and $_GET['order'] == "asc") ? 'up' : 'down'); ?>"></span>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html(Option::get('hash_ips') == true ? __('Daily Visitor Hash', 'wp-statistics') : __('IP Address', 'wp-statistics')); ?></td>
                                    <td><?php esc_html_e('Operating System', 'wp-statistics'); ?></td>
                                    <td><?php esc_html_e('Total Views', 'wp-statistics'); ?></td>
                                    <td><?php esc_html_e('User', 'wp-statistics'); ?></td>
                                    <?php
                                    if (Option::get('visitors_log')) {
                                        ?>
                                        <td class="tbl-page-column"><?php esc_html_e('Latest Page', 'wp-statistics'); ?></td>
                                        <?php
                                    }
                                    ?>
                                    <td><?php esc_html_e('Referrer', 'wp-statistics'); ?></td>
                                </tr>

                                <?php foreach ($list as $item) { ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo esc_url($item['browser']['link']); ?>" title="<?php echo esc_attr($item['browser']['name']); ?> (<?php echo esc_attr($item['browser']['version']); ?>)"><img src="<?php echo esc_url($item['browser']['logo']); ?>" alt="<?php echo esc_attr($item['browser']['name']); ?>" class="wps-flag log-tools" title="<?php echo esc_attr($item['browser']['name']); ?> (<?php echo esc_attr($item['browser']['version']); ?>)"/></a>
                                        </td>
                                        <?php if (GeoIP::active()) { ?>
                                            <td>
                                                <img src="<?php echo esc_attr($item['country']['flag']); ?>" alt="<?php echo esc_attr($item['country']['name']); ?>" title="<?php echo esc_attr($item['country']['name']); ?>" class="log-tools wps-flag"/>
                                            </td>
                                        <?php } ?>
                                        <?php if (GeoIP::active('city')) { ?>
                                            <td><?php echo esc_html($item['city']); ?></td>
                                            <td><?php echo !empty($item['region']) ? esc_html($item['region']) : Admin_Template::UnknownColumn() ?></td>
                                        <?php } ?>
                                        <td><span><?php echo esc_attr($item['date']); ?></span></td>
                                        <td class="wps-admin-column__ip">
                                            <?php echo(isset($item['map']) ? "<a class='show-map' href='" . esc_url($item['map']) . "' target='_blank' title='" . __('Map', 'wp-statistics') . "'>" . Admin_Template::icons('dashicons-location-alt') . "</a>" : ""); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?>
                                            <?php echo sprintf('<a href="%s">%s</a>', esc_url($item['ip']['link']), esc_attr($item['ip']['value'])); ?>
                                        </td>
                                        <td><?php echo esc_attr($item['platform']); ?></td>
                                        <td><?php echo esc_attr($item['hits']); ?></td>
                                        <td>
                                            <?php if (isset($item['user']) and isset($item['user']['ID']) and $item['user']['ID'] > 0) { ?>
                                                <a href="<?php echo esc_url(Menus::admin_url('visitors', array('user_id' => $item['user']['ID']))); ?>"><?php echo esc_attr($item['user']['user_login']); ?></a>

                                                <?php do_action('wp_statistics_after_user_column', $item); ?>
                                            <?php } else { ?>
                                                <?php echo Admin_Template::UnknownColumn(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                            <?php } ?>
                                        </td>
                                        <?php
                                        if (Option::get('visitors_log')) {
                                            ?>
                                            <td style='text-align: left;' class="tbl-page-column">
                                                <span class="txt-overflow" title="<?php echo esc_attr($item['page']['title'] != "" ? esc_attr($item['page']['title']) : ''); ?>"><?php echo ($item['page']['link'] != '' ? '<a href="' . esc_url($item['page']['link']) . '" target="_blank" class="wps-text-muted">' : '') . ($item['page']['title'] != "" ? $item['page']['title'] : Admin_Template::UnknownColumn()) . ($item['page']['link'] != '' ? '</a>' : ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                                            </td>
                                            <?php
                                        }
                                        ?>
                                        <td class="wps-admin-column__referred"><?php echo wp_kses_post($item['referred']); ?></td>
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
