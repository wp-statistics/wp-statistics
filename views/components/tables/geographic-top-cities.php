<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Country;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;

?>

<div class="wps-card">
    <?php View::load('components/objects/card-header', ['title' => __('Top Cities', 'wp-statistics'), 'context' => 'card']); ?>
    <div class="inside">
        <?php if (!empty($data)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('City', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l wps-col-low">
                            <?php esc_html_e('Region', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l wps-col-mid">
                            <?php esc_html_e('Country', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l wps-col-num">
                                <span class="wps-order">
                                    <?php esc_html_e('Visitors', 'wp-statistics') ?>
                                </span>
                        </th>
                        <th class="wps-pd-l wps-col-num">
                            <?php esc_html_e('Views', 'wp-statistics') ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $item) : ?>
                        <tr>
                            <td class="wps-pd-l">
                                <span class="truncate" title="<?php echo esc_attr($item->city); ?>"><?php echo esc_html($item->city); ?></span>
                            </td>
                            <td class="wps-pd-l wps-col-low">
                                <span class="truncate" title="<?php echo esc_attr($item->region); ?>"><?php echo esc_html($item->region); ?></span>
                            </td>
                            <td class="wps-pd-l wps-col-mid">
                                <div class="wps-country-name">
                                    <img class="wps-flag" src="<?php echo esc_url(Country::flag($item->country)); ?>" alt="<?php echo esc_attr(Country::getName($item->country)); ?>">
                                    <span class="truncate" title="<?php echo esc_attr(Country::getName($item->country)); ?>"><?php echo esc_html(Country::getName($item->country)); ?></span>
                                </div>
                            </td>
                            <td class="wps-pd-l wps-col-num">
                                <span><?php echo esc_html(number_format_i18n($item->visitors)); ?></span>
                            </td>
                            <td class="wps-pd-l wps-col-num">
                                <span><?php echo esc_html(number_format_i18n($item->views)); ?></span>
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
    <?php View::load('components/objects/card-footer', ['href' => Menus::admin_url('geographic', ['tab' => 'cities']), 'title' => __('View Cities', 'wp-statistics')]); ?>
</div>