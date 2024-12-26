<?php
use WP_STATISTICS\Menus;
use WP_STATISTICS\Pages;
use WP_Statistics\Utils\Request;

$page = Request::get('current_page', [], 'array');
$page = $page['file'] ?? '';

if (strpos($page, 'overview') !== false) {
    $viewTitle = esc_html__('View Content', 'wp-statistics');
} else {
    $viewTitle = esc_html__('View', 'wp-statistics');
}
?>

<div class="o-table-wrapper">
    <?php if (!empty($data)) : ?>
        <table width="100%" class="o-table wps-new-table wps-new-table__most-visited">
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
                        $reportUrl  = '';

                        if (isset($page['meta']['term_taxonomy_id'])) {
                            $reportUrl = Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $page['meta']['term_taxonomy_id'], 'from' => $args['date']['from'], 'to' => $args['date']['to']]);
                        } else if (isset($page['meta']['author_id'])) {
                            $reportUrl = Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $page['meta']['author_id'], 'from' => $args['date']['from'], 'to' => $args['date']['to']]);
                        } else if (isset($page['meta']['post_type'])) {
                            $reportUrl = Menus::admin_url('content-analytics', ['type' => 'single', 'post_id' => $item->id, 'from' => $args['date']['from'], 'to' => $args['date']['to']]);
                        } else if ($item->type == '404') {
                            $reportUrl = Menus::admin_url('pages', ['tab' => '404', 'from' => $args['date']['from'], 'to' => $args['date']['to']]);
                        }
                    ?>
                    <tr>
                        <td class="wps-pd-l">
                            <div class="wps-ellipsis-parent" title="<?php echo esc_attr($page['title']) ?>"><span class="wps-ellipsis-text"><?php echo esc_html($page['title']) ?></span></div>
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
    <?php else : ?>
        <div class="o-wrap o-wrap--no-data wps-center">
            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
        </div>
    <?php endif; ?>
</div>