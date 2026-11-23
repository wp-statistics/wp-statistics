<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Country;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;

/**
 * Args:
 *  - data  (array) Top country rows (country, visitors, views).
 *  - total (int)   All visitors in the range, the base of the Share column. Optional: without it the column is left out.
 */
$total = isset($total) ? intval($total) : 0;
?>

<div class="wps-card">
    <?php View::load('components/objects/card-header', ['title' => __('Top Countries', 'wp-statistics'), 'context' => 'card']); ?>
    <div class="inside">
        <?php if (!empty($data)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
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
                        <?php if ($total > 0) : ?>
                            <th class="wps-pd-l wps-col-share">
                                <?php esc_html_e('Share', 'wp-statistics') ?>
                            </th>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $country) : ?>
                        <tr>
                            <td class="wps-pd-l">
                                <div class="wps-country-name">
                                    <img class="wps-flag" src="<?php echo esc_url(Country::flag($country->country)); ?>" alt="">
                                    <?php if (empty($country->country) || $country->country === Country::$unknown_location) : ?>
                                        <?php /* No report for visitors with an unknown country */ ?>
                                        <span class="truncate" title="<?php echo esc_attr(Country::getName($country->country)); ?>"><?php echo esc_html(Country::getName($country->country)); ?></span>
                                    <?php else : ?>
                                        <?php /* The name opens the country report (Data Plus; without it, the page explains the add-on) */ ?>
                                        <a class="truncate" href="<?php echo esc_url(Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $country->country])) ?>" title="<?php echo esc_attr(Country::getName($country->country)); ?>"><?php echo esc_html(Country::getName($country->country)); ?></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="wps-pd-l wps-col-num">
                                <span><?php echo esc_html(number_format_i18n($country->visitors)); ?></span>
                            </td>
                            <td class="wps-pd-l wps-col-num">
                                <span><?php echo esc_html(number_format_i18n($country->views)); ?></span>
                            </td>
                            <?php if ($total > 0) : ?>
                                <td class="wps-pd-l wps-col-share">
                                    <?php View::load('components/objects/percent-bar', ['count' => $country->visitors, 'total' => $total, 'label' => __('Share of all visitors', 'wp-statistics')]); ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <?php View::load('components/objects/empty-state'); ?>
        <?php endif; ?>
    </div>
    <?php View::load('components/objects/card-footer', ['href' => Menus::admin_url('geographic', ['tab' => 'countries']), 'title' => __('View Countries', 'wp-statistics')]); ?>
</div>
