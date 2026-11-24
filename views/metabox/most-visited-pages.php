<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Pages;
use WP_Statistics\Components\View;
?>
<?php if (!empty($data)) : ?>
    <div class="o-table-wrapper">
        <table width="100%" class="o-table wps-new-table wps-table-inspect wps-new-table__most-visited">
            <thead>
            <tr>
                <th scope="col" class="wps-pd-l"><?php esc_html_e('Page', 'wp-statistics'); ?></th>
                <th scope="col" class="wps-pd-l wps-col-num"><?php esc_html_e('Visitors', 'wp-statistics'); ?></th>
                <th scope="col" class="wps-pd-l wps-col-num"><span class="wps-order"><?php esc_html_e('Views', 'wp-statistics'); ?></span></th>
                <th scope="col" class="wps-pd-l wps-col-share"><?php esc_html_e('Bounce Rate', 'wp-statistics'); ?></th>
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
                        <span class="wps-title-cell">
                            <a title="<?php echo esc_attr($page['title']) ?>" class="wps-table-ellipsis--name" href="<?php echo esc_url($reportUrl) ?>"><span><?php echo esc_html($page['title']) ?></span></a>
                            <?php View::load('components/objects/new-tab-link', ['url' => $page['link']]); ?>
                        </span>
                    </td>

                    <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($item->visitors ?? 0)) ?></td>

                    <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($item->views)) ?></td>

                    <td class="wps-pd-l wps-col-share">
                        <?php if (isset($item->bounce_rate) && $item->bounce_rate !== null) : ?>
                            <?php View::load('components/objects/percent-bar', ['value' => $item->bounce_rate, 'variant' => 'rate']); ?>
                        <?php else : ?>
                            <span class="wps-col-empty" title="<?php esc_attr_e('No visitor landed on this page first', 'wp-statistics'); ?>">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <?php View::load('components/objects/empty-state', ['date' => $args['date'] ?? []]); ?>
<?php endif; ?>
