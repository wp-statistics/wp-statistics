<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox" id="<?php echo esc_attr(\WP_STATISTICS\Meta_Box::getMetaBoxKey('pages-chart')); ?>">
                <div class="postbox-header postbox-toggle">
                    <h2 class="hndle wps-d-inline-block"><span><?php echo esc_html(sprintf(__('%s chart', 'wp-statistics'), $title)); ?></span></h2>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php echo esc_html(printf(__('Toggle panel: %s Chart', 'wp-statistics'), $title)); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="inside">
                    <!-- Do Js -->
                </div>
            </div>
        </div>
    </div>
</div>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="postbox-header postbox-toggle">
                    <h2 class="hndle wps-d-inline-block"><span><?php echo sprintf(__('%s Summary', 'wp-statistics'), esc_html($title)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></span></h2>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php echo printf(__('Toggle panel: %s Summary', 'wp-statistics'), esc_html($title)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="inside">
                    <table class="widefat table-stats wps-summary-stats" id="summary-stats">
                        <tbody>
                        <tr>
                            <th></th>
                            <th width="15%"><?php esc_html_e('Count', 'wp-statistics'); ?></th>
                        </tr>

                        <?php
                        if (isset($number_post_in_taxonomy)) {
                            ?>
                            <tr>
                                <th><?php esc_html_e('The number of posts:', 'wp-statistics'); ?></th>
                                <th>
                                    <span><?php echo esc_html(number_format_i18n($number_post_in_taxonomy)); ?></span></th>
                            </tr>
                            <?php
                        }
                        ?>

                        <?php
                        if (isset($total_posts_visits_in_taxonomy)) {
                            ?>
                            <tr>
                                <th><?php esc_html_e('Total posts visits:', 'wp-statistics'); ?></th>
                                <th>
                                    <span><?php echo esc_html(number_format_i18n($total_posts_visits_in_taxonomy)); ?></span></th>
                            </tr>
                            <?php
                        }
                        ?>

                        <tr>
                            <th><?php esc_html_e('Chart visits:', 'wp-statistics'); ?></th>
                            <th><span id="number-total-chart-visits" style="float: left;"></span></th>
                        </tr>

                        <tr>
                            <th><?php esc_html_e('All time visits:', 'wp-statistics'); ?></th>
                            <th><span id="number-total-visits" style="float: left"></span></th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (count($top_list) > 0) { ?>
    <div class="postbox-container wps-postbox-full">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <div class="postbox-header postbox-toggle">
                        <h2 class="hndle wps-d-inline-block"><span><?php echo esc_attr($top_title); ?></span></h2>
                        <button class="handlediv" type="button" aria-expanded="true">
                            <span class="screen-reader-text"><?php printf(__('Toggle panel: %s', 'wp-statistics'), esc_attr($top_title)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></span>
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="inside">
                        <?php if ($top_list_type == $taxonomy) { ?>
                            <table class="widefat table-stats wps-summary-stats" id="summary-stats">
                                <tbody>
                                <tr>
                                    <th></th>
                                    <th width="15%"><?php esc_html_e('Views', 'wp-statistics'); ?></th>
                                </tr>
                                <?php
                                foreach ($top_list as $item) {
                                    ?>
                                    <tr>
                                        <th>
                                            <a href="<?php echo esc_url($item['link']); ?>" title="<?php echo esc_attr($item['name']); ?>"><?php echo esc_attr($item['name']); ?></a>
                                        </th>
                                        <th>
                                            <span><?php echo esc_html(number_format_i18n($item['count_visit'])); ?></span>
                                        </th>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <div id="wp-statistics-pages-widget">
                                <table width="100%" class="o-table">
                                    <tbody>
                                    <tr>
                                        <td width='5%'><?php esc_html_e('ID', 'wp-statistics'); ?></td>
                                        <td><?php esc_html_e('Title', 'wp-statistics'); ?></td>
                                        <td width='15%'><?php esc_html_e('Views', 'wp-statistics'); ?></td>
                                    </tr>

                                    <?php
                                    $i = 1;
                                    foreach ($top_list as $item) {
                                        $postType = \WP_STATISTICS\Pages::get_post_type($item['ID']);
                                        $hitsPage = \WP_STATISTICS\Menus::admin_url('pages', array('ID' => $item['ID'], 'type' => $postType));
                                        ?>

                                        <tr>
                                            <td style='text-align: left;'><?php echo esc_attr($i); ?></td>
                                            <td style='text-align: left;'>
                                                <a href="<?php echo esc_url(get_edit_post_link($item['ID'])); ?>" title="<?php echo esc_attr($item['name']); ?>" target="_blank"><?php echo esc_attr($item['name']); ?></a>
                                            </td>
                                            <td style="text-align: left">
                                                <a href="<?php echo esc_url($hitsPage); ?>">
                                                    <?php printf(__('View analytics | %s visits'), esc_html($item['count_visit'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                    <svg style="margin-top: 3px;" width="10" height="10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M4.82751 4.99993 2.62209 2.79451c-.0759-.07859-.1179-.18384-.11695-.29309.00095-.10924.04477-.21375.12203-.291.07725-.07726.18176-.12108.291-.12203.10925-.00094.2145.04105.29309.11695l2.5 2.5c.07811.07814.12199.1841.12199.29459 0 .11048-.04388.21644-.12199.29458l-2.5 2.5c-.07859.0759-.18384.1179-.29309.11695-.10924-.00095-.21375-.04477-.291-.12203-.07726-.07725-.12108-.18176-.12203-.291-.00095-.10925.04105-.2145.11695-.29309l2.20542-2.20541Z" fill="#404BF2" fill-opacity=".5"/>
                                                        <path d="M7.87792 5.13371 5.67251 2.9283c-.0759-.07859-.1179-.18384-.11695-.29309.00095-.10924.04477-.21375.12202-.291.07726-.07726.18176-.12108.29101-.12203.10925-.00095.2145.04105.29308.11695l2.5 2.5c.07812.07814.122.1841.122.29458 0 .11049-.04388.21645-.122.29459l-2.5 2.5c-.07858.0759-.18383.11789-.29308.11695-.10925-.00095-.21375-.04477-.29101-.12203-.07725-.07725-.12107-.18176-.12202-.29101-.00095-.10924.04105-.2145.11695-.29308l2.20541-2.20542Z" fill="#404BF2"/>
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>

                                        <?php
                                        $i++;
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>