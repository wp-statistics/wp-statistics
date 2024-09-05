<?php

use WP_Statistics\Utils\Request;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;

$order = Request::get('order', 'desc');
?>
    <div class="inside">
        <?php if (!empty($data)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table wps-table-inspect">
                    <thead>
                        <tr>
                            <th class="wps-pd-l">
                                <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('name')); ?>" class="sort <?php echo Request::compare('order_by', 'name') ? esc_attr($order) : '' ?>"><?php esc_html_e('Author', 'wp-statistics'); ?></a>
                            </th>
                            <th class="wps-pd-l">
                                <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('page_views')); ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'page_views') ? esc_attr($order) : '' ?>">
                                    <?php esc_html_e('Author\'s Page Views', 'wp-statistics') ?>
                                </a>
                            </th>
                            <th class="wps-pd-l">
                                <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('total_posts')) ?>" class="sort <?php echo Request::compare('order_by', 'total_posts') ? esc_attr($order) : '' ?>">
                                    <?php esc_html_e('Published Contents', 'wp-statistics') ?>
                                </a>
                            </th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($data as $author) : ?>
                        <tr>
                            <td class="wps-pd-l">
                                <a class="wps-table-ellipsis--name" href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id])) ?>" target="_blank">
                                    <img class="wps-avatar" src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_attr($author->name) ?>"/>
                                    <span title="<?php echo esc_attr($author->name) ?>"><?php echo esc_html($author->name) ?></span>
                                </a>
                            </td>
                            <td class="wps-pd-l"><?php echo esc_html($author->page_views) ?></td>
                            <td class="wps-pd-l"><?php echo esc_html($author->total_posts) ?></td>
                            <td class="wps-pd-l view-more view-more__arrow">
                                <a target="_blank" href="<?php echo esc_url(get_author_posts_url($author->id)); ?>" title="<?php esc_html_e('View Author Page', 'wp-statistics') ?>">
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
                <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
            </div>
        <?php endif; ?>
    </div>
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>