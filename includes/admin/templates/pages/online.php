<?php 
use WP_STATISTICS\GeoIP;
use WP_STATISTICS\Option;
use WP_STATISTICS\Admin_Template;
?>

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
                                    <td><?php esc_html_e('Browser', 'wp-statistics'); ?></td>
                                    <?php if (GeoIP::active()) { ?>
                                        <td><?php esc_html_e('Country', 'wp-statistics'); ?></td>
                                    <?php } ?>
                                    <?php if (GeoIP::active('city')) { ?>
                                        <td><?php esc_html_e('City', 'wp-statistics'); ?></td>
                                        <td><?php esc_html_e('Region', 'wp-statistics'); ?></td>
                                    <?php } ?>
                                    <td><?php echo esc_html(Option::get('hash_ips') == true ? __('Daily Visitor Hash', 'wp-statistics') : __('IP Address', 'wp-statistics')); ?></td>
                                    <td><?php esc_html_e('Online For', 'wp-statistics'); ?></td>
                                    <td><?php esc_html_e('Page', 'wp-statistics'); ?></td>
                                    <td><?php esc_html_e('Referrer', 'wp-statistics'); ?></td>
                                    <td><?php esc_html_e('User', 'wp-statistics'); ?></td>
                                    <td></td>
                                </tr>

                                <?php foreach ($user_online_list as $item) { ?>
                                    <tr>
                                        <td style="text-align: left">
                                            <a href="<?php echo esc_url($item['browser']['link']); ?>" title="<?php echo esc_attr($item['browser']['name']); ?>"><img src="<?php echo esc_url($item['browser']['logo']); ?>" alt="<?php echo esc_attr($item['browser']['name']); ?>" class="wps-flag log-tools" title="<?php echo esc_attr($item['browser']['name']); ?>"/></a>
                                        </td>
                                        <?php if (GeoIP::active()) { ?>
                                            <td style="text-align: left">
                                                <img src="<?php echo esc_url($item['country']['flag']); ?>" alt="<?php echo esc_attr($item['country']['name']); ?>" title="<?php echo esc_attr($item['country']['name']); ?>" class="log-tools wps-flag"/>
                                            </td>
                                        <?php } ?>
                                        <?php if (GeoIP::active('city')) { ?>
                                            <td><?php echo esc_html($item['city']); ?></td>
                                            <td><?php echo !empty($item['region']) ? esc_html($item['region']) : Admin_Template::UnknownColumn() ?></td>
                                        <?php } ?>
                                        <td style='text-align: left' class="wps-admin-column__ip"><?php echo sprintf('<a href="%s">%s</a>', esc_url($item['ip']['link']), esc_attr($item['ip']['value'])); ?></td>
                                        <td style='text-align: left'><span><?php echo esc_attr($item['online_for']); ?></span></td>
                                        <td style='text-align: left'><?php echo ($item['page']['link'] != '' ? '<a href="' . esc_url($item['page']['link']) . '" target="_blank" class="wps-text-muted">' : '') . esc_attr($item['page']['title']) . ($item['page']['link'] != '' ? '</a>' : ''); ?></td>
                                        <td style='text-align: left' class="wps-admin-column__referred"><?php echo wp_kses_post($item['referred']); ?></td>
                                        <td style='text-align: left'>
                                            <?php if (isset($item['user']) and isset($item['user']['ID']) and $item['user']['ID'] > 0) { ?>
                                                <p><?php esc_html_e('ID', 'wp-statistics'); ?>: <a href="<?php echo esc_url(get_edit_user_link($item['user']['ID'])); ?>" target="_blank" class="wps-text-success">#<?php echo esc_attr($item['user']['ID']); ?></a></p><p><?php esc_html_e('Email', 'wp-statistics'); ?>: <?php echo esc_attr($item['user']['user_email']); ?></p><p><?php echo sprintf('Role: %s', implode(',', get_userdata($item['user']['ID'])->roles)) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                                            <?php } else { ?>
                                                <?php echo Admin_Template::UnknownColumn(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                            <?php } ?>
                                        </td>
                                        <td style='text-align: center'><?php echo(isset($item['map']) ? "<a class='wps-text-muted' href='" . esc_url($item['ip']['link']) . "'>" . Admin_Template::icons('dashicons-visibility') . "</a><a class='show-map wps-text-muted' href='" . esc_url($item['map']) . "' target='_blank' title='" . __('Map', 'wp-statistics') . "'>" . Admin_Template::icons('dashicons-location-alt') . "</a>" : ""); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
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
