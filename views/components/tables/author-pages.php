<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Utils\Request;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;

$order = Request::get('order', 'desc');
?>
    <div class="inside">
        <?php if (!empty($data)) : ?>
            <div class="o-table-wrapper">
                <!--screen readers -->
                <div id="table-summary" class="screen-reader-text">
                    <?php esc_html_e('This table displays author analytics, including author names, their page views, and total published content.', 'wp-statistics'); ?>
                </div>
                <table class="o-table wps-new-table wps-table-inspect" aria-describedby="table-summary">
                    <thead>
                    <tr>
                        <th scope="col" class="wps-pd-l">
                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('name')); ?>" class="sort <?php echo Request::compare('order_by', 'name') ? esc_attr($order) : '' ?>">
                                <?php esc_html_e('Author', 'wp-statistics'); ?>
                            </a>
                        </th>
                        <th scope="col" class="wps-pd-l wps-col-num">
                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('page_views')); ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'page_views') ? esc_attr($order) : '' ?>">
                                <?php esc_html_e('Author\'s Page Views', 'wp-statistics'); ?>
                            </a>
                        </th>
                        <th scope="col" class="wps-pd-l wps-col-num">
                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('total_posts')); ?>" class="sort <?php echo Request::compare('order_by', 'total_posts') ? esc_attr($order) : '' ?>">
                                <?php esc_html_e('Published Contents', 'wp-statistics'); ?>
                            </a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $author) : ?>
                        <tr>
                            <td class="wps-pd-l">
                                <span class="wps-title-cell">
                                    <a class="wps-table-ellipsis--name" href="<?php echo esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id])) ?>">
                                        <img class="wps-avatar" src="<?php echo esc_url(get_avatar_url($author->id)); ?>" alt="<?php echo esc_attr($author->name) ?>" role="presentation"/>
                                        <span><?php echo esc_html($author->name) ?></span>
                                    </a>
                                    <?php \WP_Statistics\Components\View::load('components/objects/new-tab-link', ['url' => get_author_posts_url($author->id), 'label' => __('Open author page in a new tab', 'wp-statistics')]); ?>
                                </span>
                            </td>
                            <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($author->page_views)) ?></td>
                            <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($author->total_posts)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <?php \WP_Statistics\Components\View::load('components/objects/empty-state'); ?>
        <?php endif; ?>
    </div>
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>