
<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Visitor;
use WP_Statistics\Components\View;

?>

<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table wps-recent-views-table">
                <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Date', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l start">
                            <?php esc_html_e('Page', 'wp-statistics'); ?>
                        </th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($data as $view) : 
                    if (isset($view->page_id)) {
                        $resource         = Visitor::get_page_by_id($view->page_id); 
                        $resourceLink     = $resource['link'];
                        $resourceTitle    = $resource['title'];
                        $resourceQuery    = $resource['query'] ? "?{$resource['query']}" : '';
                        $resourceViewDate = date_i18n(Helper::getDefaultDateFormat(true), strtotime($view->date));
                    } else {
                        $resource         = $view->getResource();
                        $resourceLink     = $resource->getUrl();
                        $resourceTitle    = $resource->getTitle();
                        $resourceQuery    = $view->getSession()->getParameter($resource->getId())->getFull();
                        $resourceViewDate = $view->getViewedAt();
                    }
                ?>
                    <tr>
                        <td class="wps-pd-l"><?php echo esc_html($resourceViewDate); ?></td>
                        <td class="wps-pd-l start">
                            <?php
                            View::load(
                                "components/objects/external-link", [
                                    'url' => $resourceQuery,
                                    'title' => $resourceTitle
                                ]
                            );
                            ?>
                        </td>
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
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>