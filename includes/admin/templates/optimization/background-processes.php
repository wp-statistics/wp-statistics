<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Admin\BackgroundProcessService;

$service   = new BackgroundProcessService();
$processes = $service->getAll();
?>
<div class="wrap wps-wrap">
    <h2 class="wps-settings-box__title">
        <span><?php esc_html_e('Background Processes', 'wp-statistics'); ?></span>
    </h2>
    <div class="postbox">
        <table class="form-table">
            <tbody>
                <tr>
                    <td>
                        <p class="description">
                            <?php esc_html_e('View and manage all background tasks. These processes run safely in the background and you can continue using the plugin while they complete.', 'wp-statistics'); ?>
                        </p>
                        <p class="description" style="margin-top: 5px;">
                            <em><strong><?php esc_html_e('Note:', 'wp-statistics'); ?></strong> <?php esc_html_e('If a process repeatedly fails, your server may be blocking loopback requests. Check Tools → Site Health for connectivity issues.', 'wp-statistics'); ?></em>
                        </p>
                    </td>
                </tr>

                <?php if (empty($processes)) : ?>
                    <tr>
                        <td>
                            <p class="wps-bg-processes__empty">
                                <?php esc_html_e('No background processes. Background tasks will appear here when triggered.', 'wp-statistics'); ?>
                            </p>
                        </td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td class="wps-bg-processes__table-wrapper">
                            <table class="wps-bg-processes__table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Process', 'wp-statistics'); ?></th>
                                        <th><?php esc_html_e('Status', 'wp-statistics'); ?></th>
                                        <th><?php esc_html_e('Progress', 'wp-statistics'); ?></th>
                                        <th><?php esc_html_e('Last Activity', 'wp-statistics'); ?></th>
                                        <th><?php esc_html_e('Actions', 'wp-statistics'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($processes as $process) : ?>
                                        <?php
                                        $statusClass = '';
                                        $statusLabel = '';

                                        switch ($process['status']) {
                                            case BackgroundProcessService::STATUS_RUNNING:
                                                $statusClass = 'wps-badge--info';
                                                $statusLabel = __('Running', 'wp-statistics');
                                                break;
                                            case BackgroundProcessService::STATUS_PENDING:
                                                $statusClass = 'wps-badge--warning';
                                                $statusLabel = __('Pending', 'wp-statistics');
                                                break;
                                            case BackgroundProcessService::STATUS_STUCK:
                                                $statusClass = 'wps-badge--orange';
                                                $statusLabel = __('Stuck', 'wp-statistics');
                                                break;
                                            case BackgroundProcessService::STATUS_FAILED:
                                                $statusClass = 'wps-badge--danger';
                                                $statusLabel = __('Failed', 'wp-statistics');
                                                break;
                                        }

                                        $showRetry = in_array($process['status'], [BackgroundProcessService::STATUS_STUCK, BackgroundProcessService::STATUS_FAILED], true);

                                        $retryUrl = wp_nonce_url(
                                            add_query_arg([
                                                'action'  => 'retry_background_process',
                                                'job_key' => $process['key'],
                                            ], admin_url('admin-post.php')),
                                            'retry_background_process'
                                        );

                                        $cancelUrl = wp_nonce_url(
                                            add_query_arg([
                                                'action'  => 'cancel_background_process',
                                                'job_key' => $process['key'],
                                            ], admin_url('admin-post.php')),
                                            'cancel_background_process'
                                        );
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="wps-bg-processes__title"><?php echo esc_html($process['title']); ?></span>
                                            </td>
                                            <td>
                                                <span class="wps-badge <?php echo esc_attr($statusClass); ?>">
                                                    <?php echo esc_html($statusLabel); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                /* translators: 1: Percentage complete, 2: Processed count, 3: Total count */
                                                printf(
                                                    esc_html__('%1$d%% (%2$d/%3$d)', 'wp-statistics'),
                                                    $process['progress'],
                                                    $process['processed'],
                                                    $process['total']
                                                );
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo esc_html($process['last_activity']); ?>
                                            </td>
                                            <td class="wps-bg-processes__actions">
                                                <?php if ($showRetry) : ?>
                                                    <a href="<?php echo esc_url($retryUrl); ?>" class="wps-button wps-button--primary">
                                                        <?php esc_html_e('Retry', 'wp-statistics'); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?php echo esc_url($cancelUrl); ?>" class="wps-button wps-button--default">
                                                    <?php esc_html_e('Cancel', 'wp-statistics'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
