<?php 
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$order = Request::get('order', 'desc');
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['regions'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="wps-pd-l">
                                            <?php esc_html_e('Region', 'wp-statistics') ?>
                                        </th>
                                        <th scope="col" class="wps-pd-l">
                                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('visitors')) ?>" class="sort <?php echo !Request::has('order_by') || Request::compare('order_by', 'visitors') ? esc_attr($order) : ''; ?>">
                                                <?php esc_html_e('Visitors', 'wp-statistics'); ?>
                                            </a>
                                        </th>
                                        <th scope="col" class="wps-pd-l">
                                            <?php esc_html_e('Views', 'wp-statistics') ?>
                                        </th>
                                        <th class="wps-pd-l">
                                            %
                                        </th>
                                    </tr>
                                </thead>
    
                                <tbody>

                                    <?php foreach ($data['regions'] as $item) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(Admin_Template::unknownToNotSet($item->region)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format($item->visitors)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format($item->views)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(Helper::calculatePercentage($item->visitors, $data['visits'])); ?>%
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
            </div>
        </div>
    </div>
</div>