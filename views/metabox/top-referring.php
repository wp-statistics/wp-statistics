<?php

use WP_Statistics\Decorators\ReferralDecorator;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;

?>

<?php if (!empty($data)) : ?>
    <div class="o-table-wrapper">
        <table width="100%" class="o-table wps-new-table">
            <thead>
            <tr>
                <th class="wps-pd-l"><?php esc_html_e('Domain Address', 'wp-statistics'); ?></th>
                <th class="wps-pd-l"><span class="wps-order"><?php esc_html_e('Number of Referrals', 'wp-statistics'); ?></span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $item) : ?>
                <?php /** @var ReferralDecorator $item * */ ?>

                <tr>
                    <td class="wps-pd-l">
                        <a class="wps-link-arrow" href="<?php echo esc_attr($item->getReferrer()) ?>" title="<?php echo esc_attr($item->getRawReferrer()) ?>" target="_blank"><span><?php echo esc_html($item->getRawReferrer()) ?></span></a>
                    </td>

                    <td class="wps-pd-l wps-middle-vertical"><a href="<?php echo Menus::admin_url('referrals', ['referrer' => $item->getRawReferrer(), 'from' => $filters['date']['from'], 'to' => $filters['date']['to']]) ?>" target="_blank"><?php echo esc_html($item->getTotalReferrals()) ?></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <?php
    View::load("components/objects/no-data", [
        'url'   => WP_STATISTICS_URL . 'assets/images/no-data/vector-2.svg',
        'title' => __('Data coming soon!', 'wp-statistics')
    ]);
    ?>
<?php endif; ?>