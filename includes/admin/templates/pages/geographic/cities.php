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
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Region', 'wp-statistics') ?>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Country', 'wp-statistics') ?>
                                        </th>
                                        <th class="wps-pd-l" style="width: 15%">
                                            <?php esc_html_e('Visitor Count', 'wp-statistics') ?>
                                        </th>
                                        <th class="wps-pd-l" style="width: 15%">
                                            <?php esc_html_e('View Count', 'wp-statistics') ?>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($data['cities'] as $item) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(\WP_STATISTICS\Admin_Template::unknownToNotSet($item->city)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo $item->region ? esc_html(\WP_STATISTICS\Admin_Template::unknownToNotSet($item->region)) : Admin_Template::UnknownColumn() ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php if (empty($item->city) || $item->city == 'Unknown') : ?>
                                                    <span title="<?php esc_attr_e('(not set)', 'wp-statistics') ?>" class="wps-country-name">
                                                        <img alt="<?php esc_attr_e('(not set)', 'wp-statistics') ?>" src="<?php echo esc_url(Country::flag(Country::$unknown_location)) ?>" title="<?php esc_attr_e('Unknown', 'wp-statistics') ?>" class="log-tools wps-flag"/>
                                                        <?php esc_html_e('(not set)', 'wp-statistics') ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span title="<?php echo esc_attr(Country::getName($item->country)) ?>" class="wps-country-name">
                                                        <img alt="<?php echo esc_attr(Country::getName($item->country)) ?>" src="<?php echo esc_url(Country::flag($item->country)) ?>" title="<?php echo esc_attr(Country::getName($item->country)) ?>" class="log-tools wps-flag"/>
                                                        <?php echo esc_html(Country::getName($item->country)) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format($item->visitors)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format($item->views)) ?>
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