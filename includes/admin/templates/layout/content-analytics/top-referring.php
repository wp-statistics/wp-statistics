<?php

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Referred;
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title) ?>
            <?php if ($tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="inside">
        <?php if (!empty($data)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-content-table">
                    <thead>
                        <tr>
                            <th class="wps-pd-l">
                                <?php echo esc_html__('Referrer Name', 'wp-statistics') ?>
                            </th>
                            <th class="wps-pd-l">
                                <?php echo esc_html__('Domain Address', 'wp-statistics') ?>
                            </th>
                            <th class="wps-pd-l">
                                <?php echo esc_html__('Number of Refers', 'wp-statistics') ?>
                            </th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php foreach ($data as $item) : ?>
                            <tr>
                                <td class="wps-pd-l">
                                    <div class="wps-ellipsis-parent">
                                        <span class="wps-ellipsis-text"><?php echo !empty($item['title']) ? esc_html($item['title']) : Admin_Template::UnknownColumn() ?></span>
                                    </div>
                                </td>
                                <td class="wps-pd-l">
                                    <div class="wps-ellipsis-parent">
                                        <span class="wps-ellipsis-text">
                                            <?php echo Helper::show_site_icon($item['domain']) . ' ' . Referred::get_referrer_link($item['domain'], $item['title'], true) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="wps-pd-l"><?php echo esc_html(number_format($item['number'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics')   ?> 
            </div>
        <?php endif; ?>
    </div>

</div>