<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Utils\Request;

$postType             = Request::get('pt', 'post');
$postTypeNameSingular = Helper::getPostTypeName($postType, true);
$order                = Request::get('order', 'desc');
?>
<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table wps-table-inspect">
                <thead>
                <tr>
                    <th scope="col" class="wps-pd-l">
                        <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('title')); ?>" class="sort <?php echo Request::compare('order_by', 'title') ? esc_attr($order) : ''; ?>">
                            <?php echo Request::has('pt') ? esc_html($postTypeNameSingular) : esc_html__('Page', 'wp-statistics'); ?>
                        </a>
                    </th>

                    <th scope="col" class="wps-pd-l wps-col-num">
                        <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('visitors')) ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'visitors') ? esc_attr($order) : ''; ?>">
                            <?php esc_html_e('Visitors', 'wp-statistics'); ?>
                        </a>
                    </th>

                    <th scope="col" class="wps-pd-l wps-col-num">
                        <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('views')) ?>" class="sort <?php echo Request::compare('order_by', 'views') ? esc_attr($order) : ''; ?>">
                            <?php esc_html_e('Views', 'wp-statistics'); ?>
                        </a>
                    </th>

                    <th scope="col" class="wps-pd-l wps-col-share">
                        <?php esc_html_e('Bounce Rate', 'wp-statistics'); ?>
                    </th>

                    <?php if (WordCountService::isActive()) : ?>
                        <th scope="col" class="wps-pd-l wps-col-num">
                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('words')) ?>" class="sort <?php echo Request::compare('order_by', 'words') ? esc_attr($order) : ''; ?>">
                                <?php esc_html_e('Words', 'wp-statistics') ?>
                            </a>
                        </th>
                    <?php endif; ?>

                    <th scope="col" class="wps-pd-l">
                        <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('date')) ?>" class="sort <?php echo Request::compare('order_by', 'date') ? esc_attr($order) : ''; ?>">
                            <?php esc_html_e('Published Date', 'wp-statistics'); ?>
                        </a>
                    </th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($data as $post) : ?>
                    <tr>
                        <td class="wps-pd-l">
                            <span class="wps-title-cell">
                            <a href="<?php echo esc_url(Menus::admin_url('content-analytics', ['type' => 'single', 'post_id' => $post->post_id])) ?>" class="wps-table-ellipsis--name">
                                <?php \WP_Statistics\Components\View::load('components/objects/post-thumb', [
                                    'post_id' => $post->post_id,
                                    'title'   => $post->title,
                                    'size'    => 27,
                                    'class'   => 'wps-pages-image',
                                ]); ?>

                                <span title="<?php echo esc_attr($post->title) ?>"><?php echo esc_html($post->title) ?></span>
                            </a>
                            <?php \WP_Statistics\Components\View::load('components/objects/new-tab-link', ['url' => get_the_permalink($post->post_id)]); ?>
                            </span>
                        </td>

                        <td class="wps-pd-l wps-col-num">
                            <?php echo esc_html(number_format_i18n($post->visitors)) ?>
                        </td>

                        <td class="wps-pd-l wps-col-num">
                            <?php echo esc_html(number_format_i18n($post->views)) ?>
                        </td>

                        <td class="wps-pd-l wps-col-share">
                            <?php if (isset($post->bounce_rate)) : ?>
                                <?php \WP_Statistics\Components\View::load('components/objects/percent-bar', ['value' => $post->bounce_rate, 'variant' => 'rate']); ?>
                            <?php else : ?>
                                <span class="wps-col-empty" title="<?php esc_attr_e('No visitor landed on this page first', 'wp-statistics'); ?>">-</span>
                            <?php endif; ?>
                        </td>

                        <?php if (WordCountService::isActive()) : ?>
                            <td class="wps-pd-l wps-col-num">
                                <?php echo esc_html(number_format_i18n($post->words)) ?>
                            </td>
                        <?php endif; ?>

                        <td class="wps-pd-l">
                            <?php echo esc_html(date_i18n(get_option('date_format', 'Y-m-d'), strtotime($post->date))) . ' ' . esc_html__('at', 'wp-statistics') . ' ' . esc_html(date_i18n(get_option('time_format', 'g:i a'), strtotime($post->date))); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <?php \WP_Statistics\Components\View::load('components/objects/empty-state'); ?>
    <?php endif; ?>
</div>

<?php echo isset($pagination) ? $pagination : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

