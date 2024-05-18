<?php 
use WP_STATISTICS\Menus;
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <!--  if empty-->
                    <!-- <div class="o-wrap o-wrap--no-data wps-center">-->
                    <!-- <?php //esc_html_e('No recent data available.', 'wp-statistics')   ?> -->
                    <!-- </div>
                      else
                      -->
                    <div class="o-table-wrapper">
                        <table width="100%" class="o-table wps-authors-table">
                            <thead>
                            <tr>
                                <th class="wps-pd-l">
                                    <a href="" class="sort"><?php esc_html_e('Author', 'wp-statistics') ?></a>
                                </th>
                                <th class="wps-pd-l">
                                    <a href="" class="sort des">
                                        <?php esc_html_e('Published Posts', 'wp-statistics') ?>
                                    </a>
                                </th>
                                <th class="wps-pd-l">
                                    <a href="" class="sort">
                                        <?php esc_html_e('Author\'s Page Views', 'wp-statistics') ?>
                                        <span class="wps-tooltip" title="<?php esc_html_e('Published Posts tooltip', 'wp-statistics') ?>"><i class="wps-tooltip-icon info"></i></span>
                                    </a>
                                </th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php for ($i = 1; $i < 10; $i++): ?>
                                <tr>
                                    <td>
                                        <div class="wps-author-name">
                                            <?php $user = wp_get_current_user();
                                            if ($user) : ?>
                                                 <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="Author Name"/>
                                            <?php endif ?>
                                            <span title="Author Name">Author Name</span>
                                        </div>
                                    </td>
                                    <td class="wps-pd-l">
                                        25,632
                                    </td>
                                    <td class="wps-pd-l">
                                        126
                                    </td>
                                    <td class="view-more">
                                        <!-- add class disabled-->
                                        <a href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => 1])) ?>" title="<?php esc_html_e('View Details', 'wp-statistics') ?>">
                                            <?php esc_html_e('View Details', 'wp-statistics') ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>