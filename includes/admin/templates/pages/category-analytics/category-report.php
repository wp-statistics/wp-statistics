<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

$order   = Request::get('order', 'desc');
$taxName = Helper::getTaxonomyName(Request::get('tx', 'category'), true);
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['terms'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                <tr>
                                    <th class="wps-pd-l">
                                        <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('term_name')) ?>" class="sort <?php echo Request::compare('order_by', 'term_name') ? esc_attr($order) : ''; ?>">
                                            <?php echo esc_html($taxName); ?>
                                        </a>
                                    </th>
                                    <th class="wps-pd-l wps-col-num">
                                        <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('views')) ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'views') ? esc_attr($order) : ''; ?>">
                                            <?php esc_html_e('Views', 'wp-statistics'); ?>
                                        </a>
                                    </th>
                                    <th class="wps-pd-l wps-col-num">
                                        <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('posts')) ?>" class="sort <?php echo Request::compare('order_by', 'posts') ? esc_attr($order) : ''; ?>">
                                            <?php esc_html_e('Published', 'wp-statistics'); ?>&nbsp;
                                        </a>
                                    </th>
                                    <th class="wps-pd-l wps-col-num">
                                        <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('words')) ?>" class="sort <?php echo Request::compare('order_by', 'words') ? esc_attr($order) : ''; ?>">
                                            <?php esc_html_e('Words', 'wp-statistics'); ?>&nbsp;
                                        </a>
                                    </th>
                                    <th class="wps-pd-l wps-col-num">
                                        <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('avg_views')) ?>" class="sort <?php echo Request::compare('order_by', 'avg_views') ? esc_attr($order) : ''; ?>">
                                            <?php esc_html_e('Views/Content', 'wp-statistics'); ?>&nbsp;
                                        </a>
                                    </th>
                                    <th class="wps-pd-l wps-col-num">
                                        <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('avg_words')) ?>" class="sort <?php echo Request::compare('order_by', 'avg_words') ? esc_attr($order) : ''; ?>">
                                            <?php esc_html_e('Words/Content', 'wp-statistics'); ?>&nbsp;
                                        </a>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                // Two terms with the same name ("Blog" under two parents): show each one's slug, so the rows can be told apart.
                                $nameCounts = array_count_values(array_map(function ($t) {
                                    return function_exists('mb_strtolower') ? mb_strtolower((string)$t->term_name) : strtolower((string)$t->term_name);
                                }, $data['terms']));
                                ?>
                                <?php foreach ($data['terms'] as $term) : ?>
                                    <?php
                                    $nameKey = function_exists('mb_strtolower') ? mb_strtolower((string)$term->term_name) : strtolower((string)$term->term_name);
                                    $termObj = $nameCounts[$nameKey] > 1 ? get_term((int)$term->term_id) : null;
                                    $slug    = ($termObj && !is_wp_error($termObj)) ? $termObj->slug : '';
                                    ?>
                                    <tr>
                                        <td class="wps-pd-l">
                                            <span class="wps-title-cell">
                                                <a class="wps-table-ellipsis--name" href="<?php echo esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $term->term_id])) ?>" title="<?php echo esc_attr($term->term_name) ?>"><span><?php echo esc_html($term->term_name) ?></span></a>
                                                <?php if ($slug !== '') : ?>
                                                    <span class="wps-term-slug" title="<?php echo esc_attr__('Slug', 'wp-statistics') . ': ' . esc_attr($slug); ?>"><span class="screen-reader-text"><?php esc_html_e('Slug', 'wp-statistics'); ?>: </span><?php echo esc_html($slug); ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($term->views)) ?></td>
                                        <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($term->posts)) ?></td>
                                        <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($term->words)) ?></td>
                                        <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($term->avg_views)) ?></td>
                                        <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($term->avg_words)) ?></td>
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
            </div>
        </div>
    </div>
</div>