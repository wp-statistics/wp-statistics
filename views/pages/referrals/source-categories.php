<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

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
                    <?php esc_html_e('Incoming Visitor Chart', 'wp-statistics'); ?>
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
                <?php if (!empty($data['categories'])) : ?>
                    <div class="o-table-wrapper">
                        <table width="100%" class="o-table wps-new-table wps-new-table--referrers">
                            <thead>
                            <tr>
                                <th scope="col" class="wps-pd-l">
                                    <span><?php esc_html_e('Source Category', 'wp-statistics') ?></span>
                                </th>
                                <th scope="col" class="wps-pd-l wps-col-num">
                                    <span class="wps-order"><?php esc_html_e('Number of Referrals', 'wp-statistics') ?></span>
                                </th>
                                <th scope="col" class="wps-pd-l wps-col-share">
                                    <?php esc_html_e('Share', 'wp-statistics'); ?>
                                </th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($data['categories'] as $referral) : ?>
                                <?php /** @var ReferralDecorator $referral */ ?>
                                <tr>
                                    <td class="wps-pd-l">
                                        <span class="wps-source-channel">
                                            <?php View::load('components/objects/source-channel-icon', ['key' => $referral->getRawSourceChannel()]); ?>
                                            <span class="wps-source-channel__name"><?php echo esc_html($referral->getSourceChannel()) ?></span>
                                        </span>
                                    </td>

                                    <td class="wps-pd-l wps-col-num">
                                        <?php
                                        // Direct visits are not referrals: their count opens the visitors list filtered to Direct.
                                        $countUrl = $referral->getRawSourceChannel() === 'direct'
                                            ? Menus::admin_url('visitors', ['tab' => 'visitors', 'source_channel' => 'direct'])
                                            : Menus::admin_url('referrals', ['tab' => 'referred-visitors', 'source_channel' => $referral->getRawSourceChannel()]);
                                        ?>
                                        <a href="<?php echo esc_url($countUrl) ?>"><?php echo esc_html($referral->getTotalReferrals()) ?></a>
                                    </td>

                                    <td class="wps-pd-l wps-col-share"><?php View::load('components/objects/percent-bar', ['count' => $referral->getTotalReferrals(true), 'total' => $data['total']]); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <?php View::load('components/objects/empty-state'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>