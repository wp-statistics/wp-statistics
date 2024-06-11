<?php
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

$order          = Request::get('order', 'desc');
$reverseOrder   = $order == 'desc' ? 'asc' : 'desc';
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['authors'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-authors-table">
                                <thead>
                                    <tr>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'name', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'name') ? esc_attr($order) : '' ?>"><?php esc_html_e('Author', 'wp-statistics') ?></a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'total_views', 'order' => $reverseOrder])) ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'total_views') ? esc_attr($order) : '' ?>">
                                                <?php esc_html_e('Author\'s Page Views', 'wp-statistics') ?>
                                                <span class="wps-tooltip" title="<?php esc_attr_e('Published Posts tooltip', 'wp-statistics') ?>"><i class="wps-tooltip-icon info"></i></span>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'total_posts', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'total_posts') ? esc_attr($order) : '' ?>">
                                                <?php esc_html_e('Published Posts', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($data['authors'] as $author) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <div class="wps-author-name">
                                                    <img src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_attr($author->name) ?>"/>
                                                    <a href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id])) ?>">
                                                        <span title="<?php echo esc_attr($author->name) ?>"><?php echo esc_html($author->name) ?></span>
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="wps-pd-l"><?php echo $author->total_views ? esc_html($author->total_views) : 0 ?></td>
                                            <td class="wps-pd-l"><?php echo esc_html($author->total_posts) ?></td>
                                            <td class="view-more">
                                                <a target="_blank" href="<?php echo esc_url(get_author_posts_url($author->id)); ?>" title="<?php esc_html_e('View Details', 'wp-statistics') ?>">
                                                    <?php esc_html_e('View Author Page', 'wp-statistics') ?>
                                                </a>
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