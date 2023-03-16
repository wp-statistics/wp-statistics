<form class="wps-search-date" method="get" style="margin-top: 15px;">
    <label for="search-date-input"><?php _e('Date', 'wp-statistics'); ?>:</label>
    <input type="hidden" name="page" value="<?php echo esc_attr($pageName); ?>">
    <input class="wps-search-date__input" id="search-date-input" type="text" size="18" name="day" data-wps-date-picker="day" value="<?php echo esc_attr($day); ?>" autocomplete="off" placeholder="YYYY-MM-DD" required>
    <button type="submit" class="button-primary"><span class="dashicons dashicons-search"></span></button>
</form>
<div class="wp-clearfix"></div>
<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!is_array($list) || (is_array($list) and count($list) < 1)) { ?>
                        <div class='wps-wrap--no-content wps-center'><?php _e("No information is available for this day.", "wp-statistics"); ?></div>
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
                                    <td><?php _e('Date', 'wp-statistics'); ?></td>
                                    <td><?php _e('IP', 'wp-statistics'); ?></td>
                                    <td><?php _e('Platform', 'wp-statistics'); ?></td>
                                    <td><?php _e('User', 'wp-statistics'); ?></td>
                                    <td><?php _e('Referrer', 'wp-statistics'); ?></td>
                                    <td><?php _e('Hits', 'wp-statistics'); ?></td>
                                    <td></td>
                                </tr>

                                <?php foreach ($list as $item) { ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo esc_url($item['browser']['link']); ?>" title="<?php echo esc_attr($item['browser']['name']); ?>"><img src="<?php echo esc_url($item['browser']['logo']); ?>" alt="<?php echo esc_attr($item['browser']['name']); ?>" class="wps-flag log-tools" title="<?php echo esc_attr($item['browser']['name']); ?>"/></a>
                                        </td>
                                        <?php if (WP_STATISTICS\GeoIP::active()) { ?>
                                            <td>
                                                <img src="<?php echo esc_url($item['country']['flag']); ?>" alt="<?php echo esc_attr($item['country']['name']); ?>" title="<?php echo esc_attr($item['country']['name']); ?>" class="log-tools wps-flag"/>
                                            </td>
                                        <?php } ?>
                                        <?php if (WP_STATISTICS\GeoIP::active('city')) { ?>
                                            <td><?php echo esc_attr($item['city']); ?></td>
                                        <?php } ?>
                                        <td><span><?php echo esc_attr($item['date']); ?></span></td>
                                        <td class="wps-admin-column__ip"><?php echo(isset($item['hash_ip']) ? esc_attr($item['hash_ip']) : "<a href='" . esc_url($item['ip']['link']) . "' class='wps-text-muted'>" . esc_attr($item['ip']['value']) . "</a>"); ?></td>
                                        <td><?php echo esc_attr($item['platform']); ?></td>
                                        <td>
                                            <?php if (isset($item['user']) and isset($item['user']['ID']) and $item['user']['ID'] > 0) { ?>
                                                <a href="<?php echo \WP_STATISTICS\Menus::admin_url('visitors', array('user_id' => $item['user']['ID'])); ?>" class="wps-text-success"><?php echo esc_attr($item['user']['user_login']); ?></a>
                                            <?php } else { ?>
                                                <?php echo \WP_STATISTICS\Admin_Template::UnknownColumn(); ?>
                                            <?php } ?>
                                        </td>
                                        <td class="wps-admin-column__referred"><?php echo wp_kses_post($item['referred']); ?></td>
                                        <td><?php echo esc_attr($item['hits']); ?></td>
                                        <td style='text-align: center'><?php echo(isset($item['map']) ? "<a class='table-icon-btn wps-text-muted' href='" . esc_url($item['ip']['link']) . "'>" . WP_STATISTICS\Admin_Template::icons('dashicons-visibility') . "</a><a class='table-icon-btn show-map wps-text-muted' href='" . esc_url($item['map']) . "' target='_blank' title='" . __('Map', 'wp-statistics') . "'>" . WP_STATISTICS\Admin_Template::icons('dashicons-location-alt') . "</a>" : ""); ?></td>
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
