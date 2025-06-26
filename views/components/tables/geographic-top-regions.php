<?php
use WP_Statistics\Components\View;
use WP_STATISTICS\Country;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;

$timezoneCountry = Country::getName(Helper::getTimezoneCountry());
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html__('Regions of', 'wp-statistics')  . ' ' . esc_html($timezoneCountry); ?>
        </h2>
    </div>
    <div class="inside">
        <?php if (!empty($data)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table">
                    <thead>
                        <tr>
                            <th class="wps-pd-l">
                                <?php echo esc_html__('Regions', 'wp-statistics'); ?>
                            </th>
                            <th class="wps-pd-l">
                                <span class="wps-order">
                                    <?php esc_html_e('Visitors', 'wp-statistics') ?>
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $item) : ?>
                            <tr>
                                <td class="wps-pd-l">
                                    <?php echo esc_html($item->region); ?>
                                </td>
                                <td class="wps-pd-l">
                                    <?php echo esc_html(number_format_i18n($item->visitors)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php echo esc_html(Helper::getNoDataMessage()); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="wps-card__footer">
        <div class="wps-card__footer__more">
            <a class="wps-card__footer__more__link" href="<?php echo esc_url(Menus::admin_url('geographic', ['tab' => 'regions'])) ?>">
                <?php echo esc_html__('View Regions of', 'wp-statistics') . ' ' . esc_html($timezoneCountry); ?>
            </a>
        </div>
    </div>
</div>