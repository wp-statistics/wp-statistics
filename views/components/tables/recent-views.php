<?php
use WP_STATISTICS\Helper;
use WP_STATISTICS\Visitor;
use WP_Statistics\Components\View;
?>

<div class="inside">
    <?php if (!empty($data['sessions'])) : ?>
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
                <?php foreach ($data['sessions'] as $date => $item) : ?>
                    <tr>
                        <td class="wps-pd-l">
                            <div class="wps-recent-views__id">
                                <span class="wps-recent-views__visitor-hash"><?php echo esc_html($item['session']->getIp()); ?></span>

                                <ul>
                                    <?php foreach ($item['journey'] as $page) :
                                        $pageInfo = Visitor::get_page_by_id($page['page_id']);
                                    ?>
                                        <li>
                                            <span><?php echo esc_html(date_i18n('H:i a', strtotime($page['date']))) ?></span>
                                            <?php View::load("components/objects/internal-link", ['url' => $pageInfo['report'], 'title' => $pageInfo['title']]); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </td>
                        <td class="wps-pd-l"><?php echo esc_html(date_i18n(Helper::getDefaultDateFormat(), strtotime($date))); ?></td>
                        <td class="wps-pd-l">
                            <?php View::load("components/visitor-information", ['visitor' => $item['session']]); ?>
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