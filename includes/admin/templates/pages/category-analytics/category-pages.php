<?php
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$taxonomy       = Request::get('tx', 'category');
$order          = Request::get('order', 'desc');
$reverseOrder   = $order == 'desc' ? 'asc' : 'desc';
$taxName        = Helper::getTaxonomyName(Request::get('tx', 'category'), true);
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['categories'][$taxonomy])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-authors-table">
                                <thead>
                                    <tr>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'name', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'name') ? esc_attr($order) : '' ?>"><?php echo esc_html($taxName) ?></a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'views', 'order' => $reverseOrder])) ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'views') ? esc_attr($order) : '' ?>">
                                                <?php echo sprintf(esc_html__('%s Page Views', 'wp-statistics'), $taxName) ?>
                                            </a>
                                        </th>
                                        <th class="wps-pd-l">
                                            <a href="<?php echo esc_url(add_query_arg(['order_by' => 'post_count', 'order' => $reverseOrder])) ?>" class="sort <?php echo Request::compare('order_by', 'post_count') ? esc_attr($order) : '' ?>">
                                                <?php esc_html_e('Published Posts', 'wp-statistics') ?>
                                            </a>
                                        </th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($data['categories'][$taxonomy] as $category) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <div class="wps-author-name">
                                                    <a href="<?php echo esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $category['term_id']])) ?>">
                                                        <span title="<?php echo esc_attr($category['term_name']) ?>"><?php echo esc_html($category['term_name']) ?></span>
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="wps-pd-l"><?php echo esc_html($category['views']) ?></td>
                                            <td class="wps-pd-l"><?php echo esc_html($category['posts_count']) ?></td>
                                            <td class="view-more">
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
                            <?php esc_html_e('No recent data available.', 'wp-statistics')   ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>