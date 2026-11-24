<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

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
                        <th scope="col" class="wps-pd-l">
                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('name')); ?>" class="sort <?php echo Request::compare('order_by', 'name') ? esc_attr($order) : '' ?>"><?php echo esc_html($taxName); ?></a>
                        </th>
                        <th scope="col" class="wps-pd-l wps-col-num">
                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('views')); ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'views') ? esc_attr($order) : '' ?>">
                                <?php /* translators: %s: string value */ echo sprintf(esc_html__('%s Page Views', 'wp-statistics'), $taxName); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </a>
                        </th>
                        <th scope="col" class="wps-pd-l wps-col-num">
                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('post_count')); ?>" class="sort <?php echo Request::compare('order_by', 'post_count') ? esc_attr($order) : '' ?>">
                                <?php esc_html_e('Total Published Contents', 'wp-statistics') ?>
                            </a>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($data as $category) : ?>
                        <tr>
                            <?php $termLink = get_term_link(intval($category['term_id'])); ?>
                            <td class="wps-pd-l">
                                <span class="wps-title-cell">
                                    <a class="wps-table-ellipsis--name" href="<?php echo esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $category['term_id']])) ?>">
                                        <span title="<?php echo esc_attr($category['term_name']) ?>"><?php echo esc_html($category['term_name']) ?></span>
                                    </a>
                                    <?php \WP_Statistics\Components\View::load('components/objects/new-tab-link', ['url' => is_wp_error($termLink) ? '' : $termLink]); ?>
                                </span>
                            </td>
                            <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($category['views'])) ?></td>
                            <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($category['posts_count'])) ?></td>
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

