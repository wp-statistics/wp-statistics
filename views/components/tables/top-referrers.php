<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\ReferralDecorator;

?>

<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table">
                <thead>
                <tr>
                    <th scope="col" class="wps-pd-l">
                        <?php esc_html_e('Domain Address', 'wp-statistics') ?>
                    </th>
                    <th scope="col" class="wps-pd-l">
                            <span class="wps-order">
                                <?php esc_html_e('Referrals', 'wp-statistics') ?>
                            </span>
                    </th>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($data as $item) : ?>
                    <?php /** @var ReferralDecorator $item * */ ?>

                    <tr>
                        <td class="wps-pd-l">
                            <?php
                            View::load("components/objects/external-link", [
                                'url'   => $item->getReferrer(),
                                'title' => $item->getListLabel($data)
                            ]);
                            ?>
                        </td>

                        <td class="wps-pd-l wps-middle-vertical">
                            <a href="<?php echo esc_url(Menus::admin_url('referrals', ['tab' => 'referred-visitors', 'referrer' => $item->getRawReferrer()])) ?>"><?php echo esc_html($item->getTotalReferrals()) ?></a>
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