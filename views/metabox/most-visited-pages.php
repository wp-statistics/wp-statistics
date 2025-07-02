<?php

use WP_STATISTICS\Menus;
use WP_STATISTICS\Pages;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$page = Request::get('current_page', [], 'array');
$page = $page['file'] ?? '';

if (strpos($page, 'overview') !== false) {
    $viewTitle = esc_html__('View Content', 'wp-statistics');
} else {
    $viewTitle = esc_html__('View', 'wp-statistics');
}
?>
<?php if (!empty($data)) : ?>
    <div class="o-table-wrapper">
        <table width="100%" class="o-table wps-new-table wps-table-inspect wps-new-table__most-visited">
            <thead>
            <tr>
                <th class="wps-pd-l"><?php esc_html_e('Page', 'wp-statistics'); ?></th>
                <th class="wps-pd-l"><span class="wps-order"><?php esc_html_e('Views', 'wp-statistics'); ?></span></th>
                <th class="wps-pd-l"></th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($data as $item) : ?>
                <?php
                    $page       = Pages::get_page_info($item->id, $item->type, $item->uri);
                    $reportUrl  = add_query_arg(['from' => $args['date']['from'], 'to' => $args['date']['to']], $page['report']);
                ?>
                <tr>
                    <td class="wps-pd-l">
                        <a title="<?php echo esc_attr($page['title']) ?>" class="wps-table-ellipsis--name" href="<?php echo esc_url($reportUrl) ?>"><span><?php echo esc_html($page['title']) ?></span></a>
                    </td>

                    <td class="wps-pd-l">
                        <a href="<?php echo esc_url($reportUrl) ?>" target="_blank"><?php echo esc_html(number_format_i18n($item->views)) ?></a>
                    </td>

                    <td class="wps-pd-l wps-middle-vertical">
                        <a target="_blank" class="wps-view-content" href="<?php echo esc_url($page['link']) ?>"><?php echo esc_html($viewTitle); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <?php
    $title = __('No data found for this date range.', 'wp-statistics');
    if ($isTodayOrFutureDate) {
        $title = __('Data coming soon!', 'wp-statistics');
    }
    View::load("components/objects/no-data", [
        'url'   => WP_STATISTICS_URL . 'assets/images/no-data/vector-3.svg',
        'title' => $title
    ]);
    ?>
<?php endif; ?>