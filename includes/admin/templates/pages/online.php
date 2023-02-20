<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!is_array($user_online_list)) { ?>
                        <div class='wps-wrap--no-content wps-center'><?php echo esc_attr($user_online_list); ?></div>
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
                                    <td><?php _e('IP', 'wp-statistics'); ?></td>
                                    <td><?php _e('Online For', 'wp-statistics'); ?></td>
                                    <td><?php _e('Page', 'wp-statistics'); ?></td>
                                    <td><?php _e('Referrer', 'wp-statistics'); ?></td>
                                    <td><?php _e('User', 'wp-statistics'); ?></td>
                                    <td></td>
                                </tr>

                                <?php foreach ($user_online_list as $item) { ?>
                                    <tr>
                                        <td style="text-align: left">
                                            <a href="<?php echo esc_url($item['browser']['link']); ?>" title="<?php echo esc_attr($item['browser']['name']); ?>"><img src="<?php echo esc_url($item['browser']['logo']); ?>" alt="<?php echo esc_attr($item['browser']['name']); ?>" class="wps-flag log-tools" title="<?php echo esc_attr($item['browser']['name']); ?>"/></a>
                                        </td>
                                        <?php if (WP_STATISTICS\GeoIP::active()) { ?>
                                            <td style="text-align: left">
                                                <img src="<?php echo esc_url($item['country']['flag']); ?>" alt="<?php echo esc_attr($item['country']['name']); ?>" title="<?php echo esc_attr($item['country']['name']); ?>" class="log-tools wps-flag"/>
                                            </td>
                                        <?php } ?>
                                        <?php if (WP_STATISTICS\GeoIP::active('city')) { ?>
                                            <td><?php echo esc_attr($item['city']); ?></td>
                                        <?php } ?>
                                        <td style='text-align: left' class="wps-admin-column__ip"><?php echo(isset($item['hash_ip']) ? esc_attr($item['hash_ip']) : "<a href='" . esc_url($item['ip']['link']) . "'>" . esc_attr($item['ip']['value']) . "</a>"); ?></td>
                                        <td style='text-align: left'><span><?php echo esc_attr($item['online_for']); ?></span></td>
                                        <td style='text-align: left'><?php echo ($item['page']['link'] != '' ? '<a href="' . esc_url($item['page']['link']) . '" target="_blank" class="wps-text-muted">' : '') . esc_attr($item['page']['title']) . ($item['page']['link'] != '' ? '</a>' : ''); ?></td>
                                        <td style='text-align: left' class="wps-admin-column__referred"><?php echo wp_kses_post($item['referred']); ?></td>
                                        <td style='text-align: left'>
                                            <?php if (isset($item['user']) and isset($item['user']['ID']) and $item['user']['ID'] > 0) { ?>
                                                <p><?php _e('ID', 'wp-statistics'); ?>: <a href="<?php echo get_edit_user_link($item['user']['ID']); ?>" target="_blank" class="wps-text-success">#<?php echo esc_attr($item['user']['ID']); ?></a></p><p><?php _e('Email', 'wp-statistics'); ?>: <?php echo esc_attr($item['user']['user_email']); ?></p><p><?php echo sprintf('Role: %s', implode(',', get_userdata($item['user']['ID'])->roles)) ?></p>
                                            <?php } else { ?>
                                                <?php echo \WP_STATISTICS\Admin_Template::UnknownColumn(); ?>
                                            <?php } ?>
                                        </td>
                                        <td style='text-align: center'><?php echo(isset($item['map']) ? "<a class='wps-text-muted' href='" . esc_url($item['ip']['link']) . "'>" . WP_STATISTICS\Admin_Template::icons('dashicons-visibility') . "</a><a class='show-map wps-text-muted' href='" . esc_url($item['map']) . "' target='_blank' title='" . __('Map', 'wp-statistics') . "'>" . WP_STATISTICS\Admin_Template::icons('dashicons-location-alt') . "</a>" : ""); ?></td>
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
