<?php

use WP_Statistics\Components\View;
use WP_Statistics\Decorators\ReferralDecorator;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
?>

<div class="postbox-container wps-postbox-full">
    <div class="meta-box-sortables">
        <div class="postbox mb-8">
            <div class="postbox-header--table-title">
                <h2>
                    <?php esc_html_e('Income Visitor Chart', 'wp-statistics'); ?>
                </h2>
            </div>
            <div class="inside">
                <?php View::load("components/charts/source-categories"); ?>
            </div>
        </div>
    </div>
</div>

<div class="postbox-container wps-postbox-full">
    <div class="meta-box-sortables">
        <div class="postbox">
            <div class="inside">
                <?php if (!empty($data)) : ?>
                    <div class="o-table-wrapper">
                        <table width="100%" class="o-table wps-new-table wps-new-table--referrers">
                            <thead>
                                <tr>
                                    <th class="wps-pd-l">
                                        <span><?php esc_html_e('Source Category', 'wp-statistics') ?></span>
                                    </th>
                                    <th class="wps-pd-l start">
                                        <span class="wps-order"><?php esc_html_e('Number of Referrals', 'wp-statistics') ?></span>
                                    </th>
                                    <th class="wps-pd-l"></th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach($data['categories'] as $category) : ?>
                                    <?php /** @var ReferralDecorator $category */ ?>
                                    <tr>
                                        <td class="wps-pd-l"><?php echo esc_html($category->getSourceChannel()) ?></td>

                                        <td class="wps-pd-l start">
                                            <a href=""><?php echo esc_html($category->getTotalReferrals()) ?></a>
                                        </td>

                                        <td class=" wps-pd-l">33%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="o-wrap o-wrap--no-data wps-center">
                        <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>