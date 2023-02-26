<ul class="subsubsub wp-statistics-sub-fullwidth">
    <?php
    foreach ($sub as $key => $item) {
        ?>
        <li class="all">
            <a <?php if ($item['active'] === true) { ?> class="current" <?php } ?> href="<?php echo esc_url($item['link']); ?>">
                <?php echo esc_attr($item['title']); ?>
                <span class='count'>(<?php echo number_format_i18n($item['count']); ?>)</span>
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
                        <div class='wps-wrap--no-content wps-center'><?php _e("No data to display", "wp-statistics"); ?></div>
                    <?php } else { ?>
                    <div class="o-table-wrapper">
                            <table width="100%" class="o-table">
                                <tr>
                                    <td><?php _e('Browser', 'wp-statistics'); ?></td>
                                    <?php if (WP_STATISTICS\GeoIP::active()) { ?>
                                        <td><?php _e('Country', 'wp-statistics'); ?></td>
                                    <?php } ?>
                                    <?php if (WP_STATISTICS\GeoIP::active('city')) { ?>
                                        <td><?php _e('City', 'wp-statistics'); ?></td>
                                    <?php } ?>
                                    <td>
                                        <a href="<?php echo esc_url( add_query_arg('order', ((isset($_GET['order']) and $_GET['order'] == "asc") ? 'desc' : 'asc'))); ?>">
                                            <?php _e('Date', 'wp-statistics'); ?>
                                            <span class="dashicons dashicons-arrow-<?php echo((isset($_GET['order']) and $_GET['order'] == "asc") ? 'up' : 'down'); ?>"></span>
                                        </a>
                                    </td>
                                    <td><?php _e('IP', 'wp-statistics'); ?></td>
                                    <td><?php _e('Platform', 'wp-statistics'); ?></td>
                                    <td><?php _e('Hits', 'wp-statistics'); ?></td>
                                    <td><?php _e('User', 'wp-statistics'); ?></td>
                                    <?php
                                    if (\WP_STATISTICS\Option::get('visitors_log')) {
                                        ?>
                                        <td class="tbl-page-column"><?php _e('Page', 'wp-statistics'); ?></td>
                                        <?php
                                    }
                                    ?>
                                    <td><?php _e('Referrer', 'wp-statistics'); ?></td>
                                </tr>

                                <?php foreach ($list as $item) { ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo esc_url($item['browser']['link']); ?>" title="<?php echo esc_attr($item['browser']['name']); ?>"><img src="<?php echo esc_url($item['browser']['logo']); ?>" alt="<?php echo esc_attr($item['browser']['name']); ?>" class="wps-flag log-tools" title="<?php echo esc_attr($item['browser']['name']); ?>"/></a>
                                        </td>
                                        <?php if (WP_STATISTICS\GeoIP::active()) { ?>
                                            <td>
                                                <img src="<?php echo esc_attr($item['country']['flag']); ?>" alt="<?php echo esc_attr($item['country']['name']); ?>" title="<?php echo esc_attr($item['country']['name']); ?>" class="log-tools wps-flag"/>
                                            </td>
                                        <?php } ?>
                                        <?php if (WP_STATISTICS\GeoIP::active('city')) { ?>
                                            <td><?php echo esc_attr($item['city']); ?></td>
                                        <?php } ?>
                                        <td><span><?php echo esc_attr($item['date']); ?></span></td>
                                        <td class="wps-admin-column__ip">
                                            <?php echo(isset($item['map']) ? "<a class='show-map' href='" . esc_url($item['map']) . "' target='_blank' title='" . __('Map', 'wp-statistics') . "'>" . WP_STATISTICS\Admin_Template::icons('dashicons-location-alt') . "</a>" : ""); ?>
                                            <?php echo(isset($item['hash_ip']) ? esc_attr($item['hash_ip']) : "<a href='" . esc_url($item['ip']['link']) . "'>" . esc_attr($item['ip']['value']) . "</a>"); ?>
                                        </td>
                                        <td><?php echo esc_attr($item['platform']); ?></td>
                                        <td><?php echo esc_attr($item['hits']); ?></td>
                                        <td>
                                            <?php if (isset($item['user']) and isset($item['user']['ID']) and $item['user']['ID'] > 0) { ?>
                                                <a href="<?php echo esc_url(\WP_STATISTICS\Menus::admin_url('visitors', array('user_id' => $item['user']['ID']))); ?>"><?php echo esc_attr($item['user']['user_login']); ?></a>
                                            <?php } else { ?>
                                                <?php echo \WP_STATISTICS\Admin_Template::UnknownColumn(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                            <?php } ?>
                                        </td>
                                        <?php
                                        if (\WP_STATISTICS\Option::get('visitors_log')) {
                                            ?>
                                            <td style='text-align: left;' class="tbl-page-column">
                                                <span class="txt-overflow" title="<?php echo($item['page']['title'] != "" ? esc_attr($item['page']['title']) : ''); ?>"><?php echo ($item['page']['link'] != '' ? '<a href="' . esc_url($item['page']['link']) . '" target="_blank" class="wps-text-muted">' : '') . ($item['page']['title'] != "" ? $item['page']['title'] : \WP_STATISTICS\Admin_Template::UnknownColumn()) . ($item['page']['link'] != '' ? '</a>' : ''); ?></span>
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
