<?php 
use WP_STATISTICS\Country;
use WP_STATISTICS\Admin_Template;
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['cities'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                    <tr>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('City', 'wp-statistics') ?>
                                            <span class="wps-tooltip" title="City Tooltip"><i class="wps-tooltip-icon info"></i></span>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Region', 'wp-statistics') ?>
                                            <span class="wps-tooltip" title="Region Tooltip"><i class="wps-tooltip-icon info"></i></span>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Country', 'wp-statistics') ?>
                                            <span class="wps-tooltip" title="Country Tooltip"><i class="wps-tooltip-icon info"></i></span>
                                        </th>
                                        <th class="wps-pd-l" style="width: 15%">
                                            <?php esc_html_e('Visitor Count', 'wp-statistics') ?>
                                            <span class="wps-tooltip" title="Visitor Count Tooltip"><i class="wps-tooltip-icon info"></i></span>
                                        </th>
                                        <th class="wps-pd-l" style="width: 15%">
                                            <?php esc_html_e('View Count', 'wp-statistics') ?>
                                            <span class="wps-tooltip" title="View Count Tooltip"><i class="wps-tooltip-icon info"></i></span>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($data['cities'] as $item) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <?php echo $item->city ? esc_html($item->city) : Admin_Template::UnknownColumn() ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html($item->region) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <span title="<?php echo esc_attr(Country::getName($item->country)) ?>" class="wps-country-name">
                                                    <img alt="<?php echo esc_attr(Country::getName($item->country)) ?>" src="<?php echo esc_url(Country::flag($item->country)) ?>" title="<?php echo esc_attr(Country::getName($item->country)) ?>" class="log-tools wps-flag"/>
                                                    <?php echo esc_html(Country::getName($item->country)) ?>
                                                </span>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html($item->visitors) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html($item->views) ?>
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