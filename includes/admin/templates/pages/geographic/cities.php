<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Country;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;

?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['cities'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                <tr>
                                    <th scope="col" class="wps-pd-l">
                                        <?php esc_html_e('City', 'wp-statistics') ?>
                                    </th>
                                    <th scope="col" class="wps-pd-l wps-col-low">
                                        <?php esc_html_e('Region', 'wp-statistics') ?>
                                    </th>
                                    <th scope="col" class="wps-pd-l wps-col-mid">
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
                                <?php foreach ($data['cities'] as $item) : ?>
                                    <tr>
                                        <td class="wps-pd-l">
                                            <?php echo esc_html(\WP_STATISTICS\Admin_Template::unknownToNotSet($item->city)) ?>
                                        </td>
                                        <td class="wps-pd-l wps-col-low">
                                            <?php echo $item->region ? esc_html(\WP_STATISTICS\Admin_Template::unknownToNotSet($item->region)) : Admin_Template::UnknownColumn() ?>
                                        </td>
                                        <td class="wps-pd-l wps-col-mid">
                                            <?php if (\WP_STATISTICS\Admin_Template::isUnknown($item->city)) : ?>
                                                <span title="<?php esc_attr_e('(not set)', 'wp-statistics') ?>" class="wps-country-name">
                                                        <img alt="<?php esc_attr_e('(not set)', 'wp-statistics') ?>" src="<?php echo esc_url(Country::flag(Country::$unknown_location)) ?>" class="log-tools wps-flag"/>
                                                        <?php esc_html_e('(not set)', 'wp-statistics') ?>
                                                    </span>
                                            <?php else : ?>
                                                <span title="<?php echo esc_attr(Country::getName($item->country)) ?>" class="wps-country-name">
                                                        <img alt="<?php echo esc_attr(Country::getName($item->country)) ?>" src="<?php echo esc_url(Country::flag($item->country)) ?>" class="log-tools wps-flag"/>
                                                        <?php echo esc_html(Country::getName($item->country)) ?>
                                                    </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="wps-pd-l wps-col-num">
                                            <?php echo esc_html(number_format_i18n($item->visitors)) ?>
                                        </td>
                                        <td class="wps-pd-l wps-col-num">
                                            <?php echo esc_html(number_format_i18n($item->views)) ?>
                                        </td>
                                        <td class="wps-pd-l wps-col-share">
                                            <?php View::load('components/objects/percent-bar', ['count' => $item->visitors, 'total' => $data['visitors'] ?? 0, 'label' => __('Share of visitors with a known city', 'wp-statistics')]); ?>
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