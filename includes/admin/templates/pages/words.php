<ul class="subsubsub wp-statistics-sub-fullwidth">
    <?php
    foreach ($search_engine as $key => $item) {
        ?>
        <li class="all">
            <a <?php if ($item['active'] === true) { ?> class="current" <?php } ?> href="<?php echo esc_url($item['link']); ?>">
                <?php echo esc_attr($item['title']); ?>
                <span class='count'>(<?php echo number_format_i18n($item['count']); ?>)</span>
            </a>
        </li>
        <?php $search_engine_keys = array_keys($search_engine);
        if (end($search_engine_keys) != $key) { ?> | <?php } ?><?php } ?>
</ul>

<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (count($list) < 1) { ?>
                        <div class='wps-wrap--no-content wps-center'><?php _e("No data to display", "wp-statistics"); ?></div>
                    <?php } else { ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table">
                                <tr>
                                    <td><?php _e('Word', 'wp-statistics'); ?></td>
                                    <td><?php _e('Browser', 'wp-statistics'); ?></td>
                                    <?php if (\WP_STATISTICS\GeoIP::active()) { ?>
                                        <td><?php _e('Country', 'wp-statistics'); ?></td>
                                    <?php } ?>
                                    <?php if (\WP_STATISTICS\GeoIP::active('city')) { ?>
                                        <td><?php _e('City', 'wp-statistics'); ?></td>
                                    <?php } ?>
                                    <td><?php _e('Date', 'wp-statistics'); ?></td>
                                    <td><?php _e('IP', 'wp-statistics'); ?></td>
                                    <td><?php _e('Referrer', 'wp-statistics'); ?></td>
                                </tr>

                                <?php foreach ($list as $item) { ?>
                                    <tr>
                                        <td><?php echo esc_attr($item['word']); ?></td>
                                        <td>
                                            <a href="<?php echo esc_url($item['browser']['link']); ?>" title="<?php echo esc_attr($item['browser']['name']); ?>"><img src="<?php echo esc_url($item['browser']['logo']); ?>" alt="<?php echo esc_attr($item['browser']['name']); ?>" class="log-tools wps-flag" title="<?php echo esc_attr($item['browser']['name']); ?>"/></a>
                                        </td>
                                        <?php if (WP_STATISTICS\GeoIP::active()) { ?>
                                            <td>
                                                <img src="<?php echo esc_attr($item['country']['flag']); ?>" alt="<?php echo esc_attr($item['country']['name']); ?>" title="<?php echo esc_attr($item['country']['name']); ?>" class="log-tools wps-flag"/>
                                            </td>
                                        <?php } ?>
                                        <?php if (WP_STATISTICS\GeoIP::active('city')) { ?>
                                            <td>
                                                <?php echo esc_attr($item['city']); ?>
                                            </td>
                                        <?php } ?>
                                        <td><?php echo esc_attr($item['date']); ?></td>
                                        <td class="wps-admin-column__ip"><?php echo(isset($item['hash_ip']) ? esc_attr($item['hash_ip']) : "<a href='" . esc_url($item['ip']['link']) . "' class='wps-text-success'>" . esc_attr($item['ip']['value']) . "</a>"); ?></td>
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