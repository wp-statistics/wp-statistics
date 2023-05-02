<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox" id="<?php echo \WP_STATISTICS\Meta_Box::getMetaBoxKey('top-pages-chart'); ?>">
                <div class="postbox-header postbox-toggle">
                    <h2 class="hndle wps-d-inline-block"><span><?php echo esc_html($top_trending_title); ?></span></h2>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf(__('Toggle panel: %s', 'wp-statistics'), __('Top 5 Trending Pages', 'wp-statistics')); ?></span>
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
            <div class="postbox" id="<?php echo \WP_STATISTICS\Meta_Box::getMetaBoxKey('pages'); ?>">
                <div class="postbox-header postbox-toggle">
                    <h2 class="hndle wps-d-inline-block"><span><?php echo esc_attr($title); ?></span></h2>
                    <button class="handlediv" type="button" aria-expanded="true">
                        <span class="screen-reader-text"><?php printf(__('Toggle panel: %s', 'wp-statistics'), esc_attr($title)); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="inside">

                    <?php
                    if (empty($lists)) {
                        echo '<div class="o-wrap o-wrap--no-data">';
                        _e('No data to display', 'wp-statistics');
                        echo '</div>';
                    } else {
                        ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table">
                                <tbody>
                                <tr>
                                    <td width='10%'><?php _e('ID', 'wp-statistics'); ?></td>
                                    <td width='40%'><?php _e('Title', 'wp-statistics'); ?></td>
                                    <td width='40%'><?php _e('Link', 'wp-statistics'); ?></td>
                                    <td width='10%'><?php _e('Visits', 'wp-statistics'); ?></td>
                                </tr>

                                <?php
                                $i = 1;
                                foreach ($lists as $li) {
                                    ?>

                                    <tr>
                                        <td style='text-align: left;'><?php echo esc_attr($i + ($perPage * ($currentPage - 1 ?? 0))); ?></td>
                                        <td style='text-align: left;'>
                                            <span title='<?php echo esc_attr($li['title']); ?>' class='wps-cursor-default wps-text-wrap'>
                                                <?php echo esc_attr($li['title']); ?>
                                            </span>
                                        </td>
                                        <td style='text-align: left;'>
                                            <a href="<?php echo esc_url(site_url($li['str_url'])); ?>" title="<?php echo esc_attr($li['title']); ?>" target="_blank"><?php echo esc_attr($li['title']); ?> <i class="dashicons dashicons-external" style="font-size: 15px; vertical-align: middle"></i></a>
                                        </td>
                                        <td style="text-align: left">
                                            <a href="<?php echo esc_url($li['hits_page']); ?>">
                                                <?php printf(__('View analytics | %s visits'), $li['number']) ?>
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
                        <?php
                    }
                    ?>


                    <?php echo !empty($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            </div>
        </div>
    </div>
</div>
