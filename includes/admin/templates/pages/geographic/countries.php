<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Country;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;

?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['countries'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                <tr>
                                    <th scope="col" class="wps-pd-l">
                                        <?php esc_html_e('Country', 'wp-statistics') ?>
                                    </th>
                                    <th scope="col" class="wps-pd-l wps-col-num">
                                        <?php esc_html_e('Visitors', 'wp-statistics') ?>
                                    </th>
                                    <th scope="col" class="wps-pd-l wps-col-num">
                                        <?php esc_html_e('Views', 'wp-statistics') ?>
                                    </th>
                                    <th scope="col" class="wps-pd-l wps-col-share">
                                        <?php esc_html_e('Share', 'wp-statistics') ?>
                                    </th>
                                </tr>
                                </thead>

                                <tbody>

                                <?php foreach ($data['countries'] as $item) : ?>
                                    <tr>
                                        <td class="wps-pd-l">
                                            <span title="<?php echo esc_attr(Country::getName($item->country)) ?>" class="wps-country-name">
                                                <img alt="" src="<?php echo esc_url(Country::flag($item->country)) ?>" class="log-tools wps-flag"/>
                                                <?php if (empty($item->country) || $item->country === Country::$unknown_location) : ?>
                                                    <?php /* No report for visitors with an unknown country */ ?>
                                                    <?php echo esc_html(Country::getName($item->country)) ?>
                                                <?php else : ?>
                                                    <?php /* The name opens the country report (Data Plus; without it, the page explains the add-on) */ ?>
                                                    <a href="<?php echo esc_url(Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $item->country])) ?>"><?php echo esc_html(Country::getName($item->country)) ?></a>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="wps-pd-l wps-col-num">
                                            <?php echo esc_html(number_format_i18n($item->visitors)) ?>
                                        </td>
                                        <td class="wps-pd-l wps-col-num">
                                            <?php echo esc_html(number_format_i18n($item->views)) ?>
                                        </td>
                                        <td class="wps-pd-l wps-col-share">
                                            <?php View::load('components/objects/percent-bar', ['count' => $item->visitors, 'total' => $data['visitors'] ?? 0, 'label' => __('Share of all visitors', 'wp-statistics')]); ?>
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
                <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>