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
                                    <th></th>
                                    <th>
                                        <a href="" class="sort"><?php echo esc_html__('Post', 'wp-statistics') ?></a>
                                    </th>
                                    <th class="wps-pd-l">
                                        <a href="" class="sort des">
                                            <?php echo esc_html__('Post Views', 'wp-statistics') ?>
                                        </a>
                                    </th>
                                    <th class="wps-pd-l">
                                        <a href="" class="sort">
                                            <?php echo esc_html__('Post Comments', 'wp-statistics') ?>
                                            <span class="wps-tooltip" title="<?php echo esc_html__('Publish tooltip', 'wp-statistics') ?>"><i class="wps-tooltip-icon info"></i></span>

                                        </a>
                                    </th>
                                    <th class="wps-pd-l">
                                        <a href="" class="sort">
                                            <?php echo esc_html__('Post Words', 'wp-statistics') ?>
                                        </a>
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php for ($i = 1; $i < 10; $i++): ?>
                                    <tr>
                                        <td>
                                            <?php echo esc_html($i)  ?>
                                        </td>
                                        <td>
                                            <div class="wps-author-name">
                                                <?php $user = wp_get_current_user();
                                                //sample fot image
                                                if ($user) : ?>
                                                    <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="Author Name"/>
                                                <?php endif ?>
                                                <span title="Post Name">Post Name</span>
                                            </div>
                                        </td>
                                        <td class="wps-pd-l">
                                            25,632
                                        </td>
                                        <td class="wps-pd-l">
                                            25,632
                                        </td>
                                        <td class="wps-pd-l">
                                            25,632
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