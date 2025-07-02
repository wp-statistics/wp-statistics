<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;

                    if (!empty($data['visitors'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                    <tr>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Browser', 'wp-statistics'); ?>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Visitor Count', 'wp-statistics'); ?>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Percent Share', 'wp-statistics'); ?>
                                        </th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($data['visitors'] as $item) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <span title="<?php echo \WP_STATISTICS\Admin_Template::unknownToNotSet($item->agent); ?>" class="wps-browser-name">
                                                    <img alt="<?php echo \WP_STATISTICS\Admin_Template::unknownToNotSet($item->agent); ?>" src="<?php echo esc_url(DeviceHelper::getBrowserLogo($item->agent)); ?>" title="<?php echo \WP_STATISTICS\Admin_Template::unknownToNotSet($item->agent); ?>" class="log-tools wps-flag" />
                                                    <?php echo \WP_STATISTICS\Admin_Template::unknownToNotSet($item->agent); ?>
                                                </span>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo number_format($item->visitors); ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo number_format(\WP_STATISTICS\Helper::divideNumbers($item->visitors, $data['visits'], 4) * 100, 2); ?>%
                                            </td>
                                            <td class="view-more">
                                                <a href="<?php echo esc_url(\WP_STATISTICS\Menus::admin_url('devices', array_merge($viewMoreUrlArgs, ['browser' => $item->agent]))); ?>" title="<?php esc_html_e('View Details', 'wp-statistics'); ?>">
                                                    <?php esc_html_e('View Details', 'wp-statistics'); ?>
                                                </a>
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