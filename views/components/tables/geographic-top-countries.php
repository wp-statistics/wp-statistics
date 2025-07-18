<?php
use WP_STATISTICS\Country;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$pluginHandler  = new PluginHandler();
$isActive       = $pluginHandler->isPluginActive('wp-statistics-data-plus');
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html__('Top Countries', 'wp-statistics') ?>
        </h2>
    </div>
    <div class="inside">
        <?php if (!empty($data)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table">
                    <thead>
                        <tr>
                            <th class="wps-pd-l">
                                <?php esc_html_e('Country', 'wp-statistics') ?>
                            </th>
                            <th class="wps-pd-l">
                                <span class="wps-order">
                                    <?php esc_html_e('Visitors', 'wp-statistics') ?>
                                </span>
                            </th>
                            <th class="wps-pd-l">
                                <?php esc_html_e('Views', 'wp-statistics') ?>
                            </th>
                            <th class="wps-pd-l"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $country) : ?>
                            <tr>
                                <td class="wps-pd-l">
                                    <div class="wps-country-name">
                                        <img class="wps-flag" src="<?php echo esc_url(Country::flag($country->country)); ?>" alt="<?php echo esc_attr(Country::getName($country->country)); ?>">
                                        <span class="truncate" title="<?php echo esc_attr(Country::getName($country->country)); ?>"><?php echo esc_html(Country::getName($country->country)); ?></span>
                                    </div>
                                </td>
                                <td class="wps-pd-l">
                                    <span><?php echo esc_html(number_format_i18n($country->visitors)); ?></span>
                                </td>
                                <td class="wps-pd-l">
                                    <span><?php echo esc_html(number_format_i18n($country->views)); ?></span>
                                </td>
                                <td class="-table__cell o-table__cell--right view-more">
                                    <?php if($isActive): ?>
                                    <a href="<?php echo esc_url(Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $country->country ?? Country::$unknown_location])) ?>" aria-label="View Details">
                                        <?php esc_html_e('View Details', 'wp-statistics') ?>
                                    </a>
                                    <?php else: ?>
                                        <div>
                                            <button class="disabled wps-tooltip-premium">
                                                <a class="wps-tooltip-premium__link" href="#"><?php esc_html_e('View Details', 'wp-statistics'); ?></a>
                                                <span class="wps-tooltip_templates tooltip-premium tooltip-premium--side tooltip-premium--left">
                                                    <span id="tooltip_realtime">
                                                        <a data-target="wp-statistics-data-plus" class="js-wps-openPremiumModal"><?php esc_html_e('Learn More', 'wp-statistics'); ?></a>
                                                        <span>
                                                             <?php esc_html_e('Premium Feature', 'wp-statistics'); ?>
                                                        </span>
                                                    </span>
                                                </span>
                                            </button>
                                        </div>
                                    <?php endif; ?>
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
            <a class="wps-card__footer__more__link" href="<?php echo esc_url(Menus::admin_url('geographic', ['tab' => 'countries'])) ?>">
                <?php esc_html_e('View Countries', 'wp-statistics') ?>
            </a>
        </div>
    </div>
</div>