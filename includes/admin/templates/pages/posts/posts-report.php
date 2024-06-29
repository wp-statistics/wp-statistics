<?php

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$postType             = Request::get('pt', 'post');
$postTypeNameSingular = Helper::getPostTypeName($postType, true);
$order                = Request::get('order', 'desc');
$reverseOrder         = $order == 'desc' ? 'asc' : 'desc';
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['posts'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-authors-table">
                                <thead>
                                <tr>
                                    <th class="wps-pd-l">
                                        <a href="<?php echo esc_url(add_query_arg(['order_by' => 'title', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'title') ? esc_attr($order) : ''; ?>">
                                            <?php echo esc_html($postTypeNameSingular); ?>
                                        </a>
                                    </th>
                                    <th class="wps-pd-l">
                                        <a href="<?php echo esc_url(add_query_arg(['order_by' => 'views', 'order' => $reverseOrder])) ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'views') ? esc_attr($order) : ''; ?>">
                                            <?php esc_html_e('Views', 'wp-statistics'); ?>
                                        </a>
                                    </th>
                                    
                                    <?php if (post_type_supports($postType, 'comments')) : ?>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'comments', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'comments') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Comments', 'wp-statistics'); ?>
                                            </a>
                                        </th>
                                    <?php endif; ?>

                                    <th class="wps-pd-l">
                                        <a href="<?php echo esc_url(add_query_arg(['order_by' => 'words', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'words') ? esc_attr($order) : ''; ?>">
                                            <?php esc_html_e('Words', 'wp-statistics') ?>
                                        </a>
                                    </th>
                                </tr>
                                </thead>

                                <tbody>
                                <?php foreach ($data['posts'] as $post) : ?>
                                    <tr>
                                        <td class="wps-pd-l wps-author-posts">
                                            <a target="_blank" href="<?php echo get_the_permalink($post->post_id) ?>" class="wps-author-post--name">
                                                <span title="<?php echo esc_attr($post->title) ?>">
                                                    <?php echo esc_html($post->title) ?>
                                                </span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="11" viewBox="0 0 10 11" fill="none">
                                                    <path d="M3.91667 2.25V3.33333H1.20833V9.29167H7.16667V6.58333H8.25V9.83333C8.25 10.1325 8.0075 10.375 7.70833 10.375H0.666667C0.367515 10.375 0.125 10.1325 0.125 9.83333V2.79167C0.125 2.49252 0.367515 2.25 0.666667 2.25H3.91667ZM9.875 0.625V4.95833H8.79167L8.79161 2.47371L4.57051 6.69551L3.80448 5.92949L8.02515 1.70833H5.54167V0.625H9.875Z" fill="#0C0C0D"/>
                                                </svg>
                                            </a>
                                        </td>
                                        <td class="wps-pd-l">
                                            <?php echo esc_html($post->views) ?>
                                        </td>

                                        <?php if (post_type_supports($postType, 'comments')) : ?>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html($post->comments) ?>
                                            </td>
                                        <?php endif; ?>

                                        <td class="wps-pd-l">
                                            <?php echo esc_html($post->words) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="o-wrap o-wrap--no-data wps-center">
                            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>