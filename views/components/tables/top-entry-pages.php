<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Visitor;
use WP_Statistics\Components\View;

?>

<div class="inside">
    <?php if (!empty($pages)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table wps-table-inspect">
                <thead>
                <tr>
                    <th scope="col" class="wps-pd-l">
                        <?php esc_html_e('Entry Page', 'wp-statistics') ?>
                    </th>
                    <th scope="col" class="wps-pd-l wps-col-num">
                        <span class="wps-order"><?php esc_html_e('Unique Entrances', 'wp-statistics') ?></span>
                    </th>
                    <th scope="col" class="wps-pd-l wps-col-share">
                        <?php esc_html_e('Bounce Rate', 'wp-statistics') ?>
                    </th>
                    <th scope="col" class="wps-pd-l wps-col-date wps-col-low">
                        <?php esc_html_e('Publish Date', 'wp-statistics') ?>
                    </th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($pages as $page) :
                    $pageInfo = Visitor::get_page_by_id($page->page_id);
                    ?>
                    <tr>
                        <td class="wps-pd-l">
                            <span class="wps-title-cell">
                                <?php View::load("components/objects/internal-link", [
                                    'url'   => $pageInfo['report'],
                                    'title' => $pageInfo['title']
                                ]); ?>
                                <?php View::load('components/objects/new-tab-link', ['url' => $pageInfo['link']]); ?>
                            </span>
                        </td>

                        <td class="wps-pd-l wps-col-num">
                            <span><?php echo esc_html(number_format_i18n($page->visitors)); ?></span>
                        </td>

                        <td class="wps-pd-l wps-col-share">
                            <?php View::load('components/objects/percent-bar', ['value' => (float) ($page->bounce_rate ?? 0), 'variant' => 'rate']); ?>
                        </td>

                        <td class="wps-pd-l wps-col-date wps-col-low">
                            <?php if (!empty($page->post_date)) : ?>
                                <?php echo esc_html(date_i18n(get_option('date_format', 'Y-m-d'), strtotime($page->post_date))) . ' ' . esc_html__('at', 'wp-statistics') . ' ' . esc_html(date_i18n(get_option('time_format', 'g:i a'), strtotime($page->post_date))); ?>
                            <?php else : ?>
                                <?php echo Admin_Template::UnknownColumn() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <?php \WP_Statistics\Components\View::load('components/objects/empty-state'); ?>
    <?php endif; ?>
</div>