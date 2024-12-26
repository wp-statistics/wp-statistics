<?php
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$order = Request::get('order', 'desc');
?>

<div class="inside">
    <?php if (!empty($data['data'])) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table wps-new-table--404">
                <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('URL', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <a href="<?php echo esc_url(Helper::getTableColumnSortUrl('views')); ?>" class="sort <?php echo esc_attr($order); ?>">
                                <?php esc_html_e('Views', 'wp-statistics') ?>
                            </a>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($data['data'] as $item) : ?>
                        <tr>
                            <td class="wps-pd-l">
                                <span title="<?php echo esc_html($item->uri) ?>"><?php echo esc_html($item->uri) ?></span>
                            </td>

                            <td class="wps-pd-l"><?php echo esc_html($item->views) ?></td>
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
<?php
    echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>