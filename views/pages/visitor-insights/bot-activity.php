<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\DateTime;
use WP_STATISTICS\Exclusion;

$reasons = Exclusion::exclusion_list();
?>

<div class="postbox-container wps-postbox-full">
    <div class="notice notice-info inline">
        <p>
            <strong><?php esc_html_e('Bot Activity', 'wp-statistics'); ?>:</strong>
            <?php esc_html_e('This separate log shows traffic excluded as bots during the last five minutes. It is not included in Online Visitors, visitor, view, or traffic totals.', 'wp-statistics'); ?>
        </p>
    </div>

    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['data'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                <tr>
                                    <th scope="col" class="wps-pd-l"><?php esc_html_e('Last Activity', 'wp-statistics'); ?></th>
                                    <th scope="col" class="wps-pd-l"><?php esc_html_e('Bot', 'wp-statistics'); ?></th>
                                    <th scope="col" class="wps-pd-l"><?php esc_html_e('Exclusion Reason', 'wp-statistics'); ?></th>
                                    <th scope="col" class="wps-pd-l"><?php esc_html_e('IP Address', 'wp-statistics'); ?></th>
                                    <th scope="col" class="wps-pd-l"><?php esc_html_e('Requested URL', 'wp-statistics'); ?></th>
                                    <th scope="col" class="wps-pd-l"><?php esc_html_e('Hits', 'wp-statistics'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data['data'] as $activity) : ?>
                                    <tr>
                                        <td class="wps-pd-l"><?php echo esc_html(DateTime::format($activity->last_view, ['include_time' => true])); ?></td>
                                        <td class="wps-pd-l">
                                            <strong><?php echo esc_html($activity->bot_name ?: __('Detected bot', 'wp-statistics')); ?></strong>
                                            <?php if (!empty($activity->user_agent)) : ?>
                                                <br><small><?php echo esc_html($activity->user_agent); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="wps-pd-l"><?php echo esc_html($reasons[$activity->reason] ?? $activity->reason); ?></td>
                                        <td class="wps-pd-l"><code><?php echo esc_html($activity->ip); ?></code></td>
                                        <td class="wps-pd-l"><code><?php echo esc_html($activity->uri ?: '/'); ?></code></td>
                                        <td class="wps-pd-l"><?php echo esc_html(number_format_i18n($activity->hits)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="o-wrap o-wrap--no-data wps-center">
                            <?php esc_html_e('No bot activity detected in the last five minutes.', 'wp-statistics'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>
