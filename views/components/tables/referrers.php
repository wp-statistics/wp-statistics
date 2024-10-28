<?php
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Decorators\ReferralDecorator;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
?>

    <div class="inside">
        <?php if (!empty($referrers)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table wps-new-table--referrers">
                    <thead>
                        <tr>
                            <th class="wps-pd-l">
                                <span><?php esc_html_e('Domain Address', 'wp-statistics') ?></span>
                            </th>
                            <th class="wps-pd-l">
                                <?php esc_html_e('Source Name', 'wp-statistics') ?>
                            </th>
                            <th class="wps-pd-l start">
                                <span class="wps-order"><?php esc_html_e('Number of Referrals', 'wp-statistics') ?></span>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($referrers as $referrer) : ?>
                            <?php /** @var ReferralDecorator $referrer */ ?>
                            <tr>
                                <td class="wps-pd-l">
                                    <a href="<?php echo esc_url($referrer->getReferrer()) ?>" title="<?php echo esc_html($referrer->getRawReferrer()) ?>" target="_blank" class="wps-link-arrow">
                                        <span><?php echo esc_html($referrer->getRawReferrer()) ?></span>
                                    </a>
                                </td>

                                <td class="wps-pd-l">
                                    <div class="wps-ellipsis-parent">
                                        <?php if (!empty($referrer->getSourceName())) : ?>
                                            <span class="wps-ellipsis-text" title="<?php echo esc_attr($referrer->getSourceName()) ?>"><?php echo esc_html($referrer->getSourceName()) ?></span>
                                        <?php else : ?>
                                            <?php echo Admin_Template::UnknownColumn() ?>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="wps-pd-l start">
                                    <a href="<?php echo esc_url(Menus::admin_url('referrals', ['referrer' => $referrer->getRawReferrer()])) ?>">
                                        <?php echo esc_html($referrer->getTotalReferrals()) ?>
                                    </a>
                                </td>
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
<?php
    echo $pagination ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>