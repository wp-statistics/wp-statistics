<?php
use WP_STATISTICS\Country;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$pluginHandler  = new PluginHandler();
$isActive       = $pluginHandler->isPluginActive('wp-statistics-data-plus');
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['countries'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="wps-pd-l">
                                            <?php esc_html_e('Country', 'wp-statistics') ?>
                                        </th>
                                        <th scope="col" class="wps-pd-l">
                                            <?php esc_html_e('Visitor Count', 'wp-statistics') ?>
                                        </th>
                                        <th scope="col" class="wps-pd-l">
                                            <?php esc_html_e('View Count', 'wp-statistics') ?>
                                        </th>
                                        <th scope="col">
                                            <span class="screen-reader-text"><?php esc_html_e('Details', 'wp-statistics'); ?></span>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($data['countries'] as $item) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <span title="<?php echo esc_attr(Country::getName($item->country)) ?>" class="wps-country-name">
                                                    <img alt="<?php echo esc_attr(Country::getName($item->country)) ?>" src="<?php echo esc_url(Country::flag($item->country)) ?>" class="log-tools wps-flag"/>
                                                    <?php echo esc_html(Country::getName($item->country)) ?>
                                                </span>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format($item->visitors)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format($item->views)) ?>
                                            </td>
                                            <td class="-table__cell o-table__cell--right view-more">
                                                <?php if($isActive): ?>
                                                    <a href="<?php echo esc_url(Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $item->country])) ?>" title="<?php esc_html_e('View Details', 'wp-statistics'); ?>">
                                                        <?php esc_html_e('View Details', 'wp-statistics'); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <div>
                                                        <button class="disabled wps-tooltip-premium">
                                                            <a class="wps-tooltip-premium__link" href="<?php echo esc_url(\WP_STATISTICS\Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $item->country])) ?>"><?php esc_html_e('View Details', 'wp-statistics'); ?></a>
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
                            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>