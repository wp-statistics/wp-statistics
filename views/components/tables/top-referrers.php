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
                            <?php // The domain opens its report (the visitors it sent); the ↗ opens the site itself. ?>
                            <span class="wps-title-cell">
                                <?php View::load('components/objects/internal-link', [
                                    'url'   => Menus::admin_url('referrals', ['tab' => 'referred-visitors', 'referrer' => $item->getRawReferrer()]),
                                    'title' => $item->getListLabel($data),
                                ]); ?>
                                <?php View::load('components/objects/new-tab-link', ['url' => $item->getReferrer(), 'label' => __('Open site in a new tab', 'wp-statistics')]); ?>
                            </span>
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