
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
                            <?php esc_html_e('ID', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Date', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Visitor Information', 'wp-statistics'); ?>
                        </th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($data as $view) : 
                    $page = Visitor::get_page_by_id($view->page_id); 
                ?>
                    <tr>
                        <td class="wps-pd-l">
                            <div class="wps-recent-views__id">
                                <span class="wps-recent-views__visitor-hash">#4e2vdjit3</span>
                                <ul>
                                    <li>
                                        <span>3 : 26 pm</span>
                                        <?php
                                        View::load("components/objects/internal-link", ['url' => $page['report'], 'title' => $page['title']]);
                                        ?>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td class="wps-pd-l"><?php echo esc_html(date_i18n(Helper::getDefaultDateFormat(true), strtotime($view->date))); ?></td>
                        <td class="wps-pd-l">
                            <?php View::load("components/visitor-information", ['visitor' => []]); ?>
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