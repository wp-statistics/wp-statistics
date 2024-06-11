<?php
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$order          = Request::get('order', 'desc');
$reverseOrder   = $order == 'desc' ? 'asc' : 'desc';
$postType       = Request::get('pt', 'post');
$postTypeLabel  = Helper::getPostTypeName($postType, true);
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
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'name', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'name') ? esc_attr($order) : ''; ?>"><?php esc_html_e('Author', 'wp-statistics') ?></a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'total_views', 'order' => $reverseOrder])) ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'total_views') ? esc_attr($order) : ''; ?>">
                                                <?php echo sprintf(esc_html__('%s Views', 'wp-statistics'), $postTypeLabel) ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'total_posts', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'total_posts') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Publish', 'wp-statistics') ?>
                                                <span class="wps-tooltip" title="<?php esc_html_e('Publish tooltip', 'wp-statistics') ?>"><i class="wps-tooltip-icon info"></i></span>

                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'total_author_views', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'total_author_views') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Author Page Views', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'total_comments', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'total_comments') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Comments', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'average_comments', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'average_comments') ? esc_attr($order) : ''; ?>">
                                                <?php echo sprintf(esc_html__('Comments/%s', 'wp-statistics'), $postTypeLabel) ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'average_views', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'average_views') ? esc_attr($order) : ''; ?>">
                                                <?php echo sprintf(esc_html__('Views/%s', 'wp-statistics'), $postTypeLabel) ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'average_words', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'average_words') ? esc_attr($order) : ''; ?>">
                                                <?php echo sprintf(esc_html__('Words/%s', 'wp-statistics'), $postTypeLabel) ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'total_words', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'total_words') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Word Counts', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($data as $author) : ?>
                                        <?php /** @var stdClass $author */ ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <div class="wps-author-name">
                                                    <img src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_attr($author->name) ?>"/>
                                                    <a href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id])) ?>">
                                                        <span title="<?php echo esc_attr($author->name) ?>"><?php echo esc_html($author->name) ?></span>
                                                    </a>
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
                                                <?php echo $author->average_comments ? esc_html(round($author->average_comments, 1)) : 0; ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo $author->average_views ? esc_html(round($author->average_views, 1)) : 0; ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo $author->average_words ? esc_html(round($author->average_words, 1)) : 0; ?>
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

                <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>