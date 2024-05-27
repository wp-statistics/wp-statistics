<?php
use WP_STATISTICS\Helper;
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data)) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-authors-table">
                                <thead>
                                    <tr>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort"><?php esc_html_e('Author', 'wp-statistics') ?></a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort des">
                                                <?php esc_html_e('Post Views', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Publish', 'wp-statistics') ?>
                                                <span class="wps-tooltip" title="<?php esc_html_e('Publish tooltip', 'wp-statistics') ?>"><i class="wps-tooltip-icon info"></i></span>

                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Author Page Views', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Comments', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Comments/Post', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Post Views/Post', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Words/Post', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="" class="sort">
                                                <?php esc_html_e('Word Counts', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($data as $author) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <div class="wps-author-name">
                                                    <img src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_attr($author->name) ?>"/>
                                                    <span title="<?php echo esc_attr($author->name) ?>"><?php echo esc_html($author->name) ?></span>
                                                </div>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo $author->total_views ? esc_html($author->total_views) : 0 ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html($author->total_posts) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                            <?php echo $author->total_author_views ? esc_html($author->total_author_views) : 0 ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo $author->total_comments ? esc_html($author->total_comments) : 0 ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(Helper::divideNumbers($author->total_comments, $author->total_posts)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(Helper::divideNumbers($author->total_views, $author->total_posts)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(Helper::divideNumbers($author->total_words, $author->total_posts)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo $author->total_words ? esc_html($author->total_words) : 0 ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="o-wrap o-wrap--no-data wps-center">
                            <?php esc_html_e('No recent data available.', 'wp-statistics')   ?> 
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>