<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

$order          = Request::get('order', 'desc');
$taxName        = Helper::getTaxonomyName(Request::get('tx', 'category'), true);
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
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('views')) ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'views') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Views', 'wp-statistics'); ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('posts')) ?>" class="sort <?php echo Request::compare('order_by', 'posts') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Published', 'wp-statistics'); ?>&nbsp;
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('words')) ?>" class="sort <?php echo Request::compare('order_by', 'words') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Words', 'wp-statistics'); ?>&nbsp;
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('avg_views')) ?>" class="sort <?php echo Request::compare('order_by', 'avg_views') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Views/Content', 'wp-statistics'); ?>&nbsp;
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('avg_words')) ?>" class="sort <?php echo Request::compare('order_by', 'avg_words') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Words/Content', 'wp-statistics'); ?>&nbsp;
                                            </a>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['terms'] as $term) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <span class="wps-ellipsis-parent" title="<?php echo esc_attr($term->term_name) ?>">
                                                    <a href="<?php echo esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $term->term_id])) ?>"><span class="wps-ellipsis-text"><?php echo esc_html($term->term_name) ?></span></a>
                                                </span>
                                            </td>
                                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($term->views)) ?></td>
                                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($term->posts)) ?></td>
                                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($term->words)) ?></td>
                                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($term->avg_views)) ?></td>
                                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($term->avg_words)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="o-wrap o-wrap--no-data wps-center">
                            <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>