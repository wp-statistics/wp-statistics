<?php
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_STATISTICS\Helper;
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['visitors'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                    <tr>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('OS', 'wp-statistics'); ?>
                                        </th>
                                        <th class="wps-pd-l">
                                            <span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                                        </th>
                                        <th class="wps-pd-l">
                                            %
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($data['visitors'] as $item) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <span title="<?php echo \WP_STATISTICS\Admin_Template::unknownToNotSet($item->platform); ?>" class="wps-platform-name">
                                                    <img alt="<?php echo \WP_STATISTICS\Admin_Template::unknownToNotSet($item->platform); ?>" src="<?php echo esc_url(DeviceHelper::getPlatformLogo($item->platform)); ?>" class="log-tools wps-flag" />
                                                    <?php echo \WP_STATISTICS\Admin_Template::unknownToNotSet($item->platform); ?>
                                                </span>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format_i18n($item->visitors)); ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(Helper::calculatePercentage($item->visitors, $data['visits'])); ?>%
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="o-wrap o-wrap--no-data wps-center">
                            <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
            </div>
        </div>
    </div>
</div>