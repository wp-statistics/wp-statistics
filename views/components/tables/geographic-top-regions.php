<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;

?>

<div class="wps-card">
    <?php View::load('components/objects/card-header', ['title' => $title, 'context' => 'card']); ?>
    <div class="inside">
        <?php if (!empty($data)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php echo esc_html($top_title); ?>
                        </th>
                        <th class="wps-pd-l wps-col-num">
                                <span class="wps-order">
                                    <?php esc_html_e('Visitors', 'wp-statistics') ?>
                                </span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $item) : ?>
                        <tr>
                            <td class="wps-pd-l">
                                <?php echo esc_html($item->region); ?>
                            </td>
                            <td class="wps-pd-l wps-col-num">
                                <?php echo esc_html(number_format_i18n($item->visitors)) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <?php View::load('components/objects/empty-state'); ?>
        <?php endif; ?>
    </div>
    <?php View::load('components/objects/card-footer', ['href' => $footer_link, 'title' => $footer_title]); ?>
</div>