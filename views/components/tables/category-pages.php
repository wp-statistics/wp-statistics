<?php

use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$order    = Request::get('order', 'desc');
$taxName  = Helper::getTaxonomyName(Request::get('tx', 'category'), true);
?>
<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table wps-table-inspect">
                <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('name')); ?>" class="sort <?php echo Request::compare('order_by', 'name') ? esc_attr($order) : '' ?>"><?php echo esc_html($taxName); ?></a>
                        </th>
                        <th class="wps-pd-l">
                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('views')); ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'views') ? esc_attr($order) : '' ?>">
                                <?php echo sprintf(esc_html__('%s Page Views', 'wp-statistics'), $taxName); ?>
                            </a>
                        </th>
                        <th class="wps-pd-l">
                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('post_count')); ?>" class="sort <?php echo Request::compare('order_by', 'post_count') ? esc_attr($order) : '' ?>">
                                <?php esc_html_e('Total Published Contents', 'wp-statistics') ?>
                            </a>
                        </th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($data as $category) : ?>
                        <tr>
                            <td class="wps-pd-l">
                                <a class="wps-table-ellipsis--name" target="_blank" href="<?php echo esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $category['term_id']])) ?>">
                                    <span title="<?php echo esc_attr($category['term_name']) ?>"><?php echo esc_html($category['term_name']) ?></span>
                                </a>
                            </td>
                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($category['views'])) ?></td>
                            <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($category['posts_count'])) ?></td>
                            <td class="wps-pd-l view-more view-more__arrow">
                                <a target="_blank" href="<?php echo esc_url(get_term_link(intval($category['term_id']))); ?>" title="<?php esc_html_e('View Category Page', 'wp-statistics') ?>">
                                    <?php echo sprintf(esc_html__('View %s Page', 'wp-statistics'), $taxName) ?>
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

