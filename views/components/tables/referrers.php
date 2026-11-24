<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Decorators\ReferralDecorator;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_Statistics\Components\View;

/**
 * Args:
 *  - referrers  (ReferralDecorator[]) Rows.
 *  - total      (int) Optional. All referrals behind every row (not only this page), the base of the Share column.
 *  - share_label (string) Optional. Screen-reader text of the Share cells, naming that base
 *                ("Share of search engine referrals"). Default "Share of all referrals".
 *  - pagination (string) Optional pagination HTML.
 *
 * Rows are grouped by domain and source, so one domain can have two rows (google.com as
 * Google and as Google Ads). The source name is also printed under the domain, shown only
 * when the Source Name column is hidden, so the rows differ at every width. Rows without
 * a referrer (paid clicks known from the landing URL) read "(no referrer)".
 */
$total      = isset($total) ? intval($total) : 0;
$shareLabel = !empty($share_label) ? $share_label : __('Share of all referrals', 'wp-statistics');
?>

    <div class="inside">
        <?php if (!empty($referrers)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table wps-new-table--referrers">
                    <thead>
                    <tr>
                        <th scope="col" class="wps-pd-l">
                            <span><?php esc_html_e('Domain Address', 'wp-statistics') ?></span>
                        </th>
                        <th scope="col" class="wps-pd-l wps-col-low">
                            <?php esc_html_e('Source Name', 'wp-statistics') ?>
                        </th>
                        <th scope="col" class="wps-pd-l wps-col-num">
                            <span class="wps-order"><?php esc_html_e('Number of Referrals', 'wp-statistics') ?></span>
                        </th>
                        <?php if ($total > 0) : ?>
                            <th scope="col" class="wps-pd-l wps-col-share">
                                <?php esc_html_e('Share', 'wp-statistics') ?>
                            </th>
                        <?php endif; ?>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($referrers as $referrer) : ?>
                        <?php /** @var ReferralDecorator $referrer */ ?>
                        <tr>
                            <td class="wps-pd-l">
                                <?php if (!empty($referrer->getRawReferrer())) : ?>
                                    <div class="wps-ellipsis-parent">
                                        <a href="<?php echo esc_url($referrer->getReferrer()) ?>" title="<?php echo esc_html($referrer->getRawReferrer()) ?>" target="_blank" class="wps-link-arrow">
                                            <span class="wps-ellipsis-text"><?php echo esc_html($referrer->getRawReferrer()) ?></span>
                                        </a>
                                    </div>
                                <?php else : ?>
                                    <span class="wps-referrer-none"><?php esc_html_e('(no referrer)', 'wp-statistics') ?></span>
                                <?php endif; ?>
                                <?php if (!empty($referrer->getSourceName())) : ?>
                                    <?php // Shown when the Source Name column is hidden (narrow tables), so rows with the same domain still differ. ?>
                                    <span class="wps-referrer-source-inline"><bdi><?php echo esc_html($referrer->getSourceName()) ?></bdi></span>
                                <?php endif; ?>
                            </td>

                            <td class="wps-pd-l wps-col-low">
                                <div class="wps-ellipsis-parent">
                                    <?php if (!empty($referrer->getSourceName())) : ?>
                                        <span class="wps-ellipsis-text" title="<?php echo esc_attr($referrer->getSourceName()) ?>"><?php echo esc_html($referrer->getSourceName()) ?></span>
                                    <?php else : ?>
                                        <?php echo Admin_Template::UnknownColumn() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="wps-pd-l wps-col-num">
                                <a href="<?php echo esc_url(Menus::admin_url('referrals', ['tab' => 'referred-visitors', 'referrer' => $referrer->getRawReferrer()])) ?>">
                                    <?php echo esc_html($referrer->getTotalReferrals()) ?>
                                </a>
                            </td>
                            <?php if ($total > 0) : ?>
                                <td class="wps-pd-l wps-col-share">
                                    <?php View::load('components/objects/percent-bar', ['count' => $referrer->getTotalReferrals(true), 'total' => $total, 'label' => $shareLabel]); ?>
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
<?php
echo $pagination ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>