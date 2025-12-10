<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

$isLicenseValid    = LicenseHelper::isPluginLicenseValid('wp-statistics-ai-insights');
$isAiInsightActive = Helper::isAddOnActive('ai-insights');

$isAuthenticated = apply_filters('wp_statistics_oath_authentication_status', false);
$gscProperty     = Option::getByAddon('site', 'marketing');

$settingsData = apply_filters('wp_statistics_ai_insights_settings_data', []);
$isMarketingActive = Helper::isAddOnActive('marketing');

$syncStatus   = $settingsData['sync_status'] ?? '';
$lastSyncTime = $settingsData['last_sync_timestamp'] ?? null;
$nextSyncTime = $settingsData['next_sync_timestamp'] ?? null;
$gscRecords   = $settingsData['records_synced'] ?? 0;
$tableSize    = $settingsData['table_size'] ?? 0;
?>

<h2 class="wps-settings-box__title">
    <span><?php esc_html_e('AI Insight', 'wp-statistics'); ?></span>
</h2>

<?php
if (!$isAiInsightActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug'         => esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-ai-insights/?utm_source=wp-statistics&utm_medium=link&utm_campaign=ai-insight'),
     'addon_title'        => __('AI Insight Add-on', 'wp-statistics'),
     'addon_modal_target' => 'wp-statistics-ai-insights',
     'addon_campaign'     => 'settings',
     'addon_description'  => __('The settings on this page are part of the AI Insights add-on, which helps you identify SEO opportunities and optimize your website\'s search performance with actionable recommendations.', 'wp-statistics'),
     'addon_features'     => [
         __('Discover keywords in striking distance of ranking #1.', 'wp-statistics'),
         __('Improve click-through rates with optimization recommendations.', 'wp-statistics'),
         __('Identify trending content and spot performance changes early.', 'wp-statistics'),
         __('Track traffic from AI tools like ChatGPT and Perplexity.', 'wp-statistics'),
     ],
     'addon_info'         => __('Unlock SEO growth opportunities and actionable insights with the AI Insights add-on', 'wp-statistics'),
    ], true);


if ($isAiInsightActive && !$isLicenseValid) {
    View::load("components/lock-sections/notice-inactive-license-addon");
}
?>

<div class="postbox">
    <table class="form-table <?php echo !$isAiInsightActive ? 'form-table--preview' : '' ?>">
        <tbody>
        <tr class="wps-settings-box_head">
            <th scope="row" colspan="2">
                <div class="wps-addon-settings--marketing__title">
                    <div>
                        <h3><?php esc_html_e('GSC Data Sync', 'wp-statistics'); ?></h3>
                    </div>
                    <div class="wps-status">
                        <?php if ($syncStatus === 'success') : ?>
                            <div class="alert alert-success"><span><?php esc_html_e('Sync completed successfully', 'wp-statistics'); ?></span></div>
                        <?php elseif ($syncStatus === 'error') : ?>
                            <a href="<?php echo esc_url(Menus::admin_url('settings', ['tab' => 'ai-insights-settings', 'action' => 'wp_statistics_init_gsc_sync', 'nonce' => wp_create_nonce('wp_statistics_init_gsc_sync')])); ?>"
                               aria-label="<?php esc_attr_e('Retry failed sync', 'wp-statistics'); ?>" style="text-decoration: underline">
                                <?php esc_html_e('Retry', 'wp-statistics'); ?>
                            </a>
                            <div class="alert alert-danger"><span><?php esc_html_e('Last sync failed', 'wp-statistics'); ?></span></div>
                        <?php elseif ($syncStatus === 'in-progress') : ?>
                            <div class="alert alert-loading"><span><?php esc_html_e('Syncing GSC data...', 'wp-statistics'); ?></span></div>
                        <?php else : ?>
                            <div class="alert alert-primary"><span><?php esc_html_e('No recent sync activity', 'wp-statistics'); ?></span></div>
                        <?php endif; ?>
                    </div>
                </div>
            </th>
        </tr>

        <tr>
            <th colspan="2" scope="row">
                <p class="description"><?php esc_html_e('AI Insights syncs Google Search Console data into custom tables for faster analysis. Configure automatic sync frequency or manually trigger a sync to refresh your data.', 'wp-statistics'); ?></p>
            </th>
        </tr>

        <?php if ($isAiInsightActive && !$isMarketingActive) : ?>
            <tr>
                <th colspan="2" scope="row">
                    <div class="wps-alert wps-alert__danger">
                        <?php esc_html_e('AI Insights requires the Marketing add-on to be active. Please activate Marketing to access these settings.', 'wp-statistics'); ?>
                    </div>
                </th>
            </tr>
        <?php endif; ?>

        <?php if ($gscRecords === 0 && $syncStatus === 'success') : ?>
            <tr>
                <th colspan="2" scope="row">
                    <div class="wps-alert wps-alert__info">
                        <?php esc_html_e('No GSC data available yet. Your site may be new or not receiving search traffic. Check back in a few days.', 'wp-statistics'); ?>
                    </div>
                </th>
            </tr>
        <?php endif; ?>

        <?php if ($syncStatus === 'error') : ?>
            <tr>
                <th colspan="2" scope="row">
                    <div class="wps-alert wps-alert__danger">
                        <?php esc_html_e('Failed to sync GSC data. Please try again or check your GSC connection in Marketing add-on settings.', 'wp-statistics'); ?>
                    </div>
                </th>
            </tr>
        <?php endif; ?>

        <?php if ($syncStatus === 'not-initiated') : ?>
            <tr>
                <th colspan="2" scope="row">
                    <div class="wps-alert wps-alert__info">
                        <?php esc_html_e('No Google Search Console data has been synced yet. Sync now to populate AI Insights reports and start seeing your SEO performance data.', 'wp-statistics'); ?>
                    </div>
                </th>
            </tr>
        <?php endif; ?>

        <?php if (!$gscProperty || !$isAuthenticated) : ?>
            <tr>
                <th colspan="2" scope="row">
                    <div class="wps-alert wps-alert__warning">
                        <?php esc_html_e('Google Search Console is not connected. Connect GSC in Marketing add-on settings to enable data sync.', 'wp-statistics'); ?>
                    </div>
                </th>
            </tr>
        <?php endif; ?>

        <tr class="js-wps-show_if_ai_insight_auto_sync_disabled">
            <th colspan="2" scope="row">
                <div class="wps-alert wps-alert__info"><?php esc_html_e('Automatic sync is disabled. Your AI Insights reports will not update until you manually sync GSC data.', 'wp-statistics'); ?></div>
            </th>
        </tr>

        <tr data-id="enable_auto_sync_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Enable Automatic Sync', 'wp-statistics'); ?></span>
            </th>

            <td>
                <input type="hidden" name="wps_addon_settings[ai_insights][gsc_auto_sync]" value="0"/>
                <?php $isDisabled = (!$gscProperty || !$isAuthenticated); ?>
                <input id="wps_addon_settings[ai_insights][gsc_auto_sync]" type="checkbox"
                       value="1" name="wps_addon_settings[ai_insights][gsc_auto_sync]"
                    <?php if ($isDisabled): ?>
                        disabled
                        title="<?php esc_attr_e('Connect GSC first', 'wp-statistics'); ?>"
                        class="wps-tooltip"
                    <?php endif; ?>
                    <?php checked(Option::getByAddon('gsc_auto_sync', 'ai_insights', '1'), '1'); ?>>
                <label for="wps_addon_settings[ai_insights][gsc_auto_sync]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Automatically sync Google Search Console data on a scheduled basis. When disabled, data must be synced manually.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr data-id="gsc_sync_frequency_tr" class="js-wps-show_if_ai_insight_auto_sync_enabled">
            <?php $syncFrequency = Option::getByAddon('gsc_sync_frequency', 'ai_insights'); ?>

            <th scope="row">
                <label for="gsc_sync_frequency"><?php esc_html_e('Sync Frequency', 'wp-statistics'); ?></label>
            </th>
            <td>
                <select id="gsc_sync_frequency" name="wps_addon_settings[ai_insights][gsc_sync_frequency]">
                    <option value="daily" <?php selected($syncFrequency, 'daily'); ?>>
                        <?php esc_html_e('Daily', 'wp-statistics'); ?>
                    </option>
                    <option value="two_days" <?php selected($syncFrequency, 'two_days'); ?>>
                        <?php esc_html_e('Every 2 Days', 'wp-statistics'); ?>
                    </option>
                </select>
                <p class="description"><?php esc_html_e('How often GSC data should be synced automatically.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr id="sync_now_tr" data-id="sync_now_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Sync Now', 'wp-statistics'); ?></span>
            </th>

            <td>
                <div>
                    <?php if ($syncStatus === 'in-progress') : ?>
                        <a aria-label="<?php esc_attr_e('Syncing GSC data', 'wp-statistics'); ?>" class="wps-button wps-loading-button wps-button--default">
                            <?php esc_html_e('Syncing...', 'wp-statistics'); ?>
                        </a>
                    <?php else : ?>
                        <a data-last-sync="<?php echo esc_attr($lastSyncTime); ?>"
                            <?php if (!$gscProperty || !$isAuthenticated) : ?>
                                title="<?php esc_attr_e('Connect GSC first', 'wp-statistics'); ?>"
                            <?php endif; ?>
                           href="<?php echo esc_url(Menus::admin_url('settings', ['tab' => 'ai-insights-settings', 'action' => 'wp_statistics_init_gsc_sync', 'nonce' => wp_create_nonce('wp_statistics_init_gsc_sync')])); ?>"
                           aria-label="<?php esc_attr_e('Manually trigger an immediate sync of GSC data', 'wp-statistics'); ?>"
                           class="wps-button wps-button--default <?php echo !$gscProperty || !$isAuthenticated ? esc_attr('wps-tooltip disabled') : ''; ?>">
                            <?php esc_html_e('Sync Now', 'wp-statistics'); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <p class="description"><?php esc_html_e('Manually trigger an immediate sync of GSC data. This fetches the latest available data from Google Search Console and updates all AI Insights reports.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr data-id="last_sync_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Last Sync', 'wp-statistics'); ?></span>
            </th>
            <td>
                <input type="text" title="<?php echo $lastSyncTime ? esc_attr(DateTime::format($lastSyncTime, ['include_time' => true])) : esc_attr__('Never', 'wp-statistics'); ?>" value="<?php echo $lastSyncTime ? esc_attr(human_time_diff($lastSyncTime)) . ' ' . esc_attr__('ago', 'wp-statistics') : esc_attr__('Never', 'wp-statistics'); ?>" aria-label="<?php esc_attr_e('Last Sync', 'wp-statistics'); ?>" readonly class="wps-tooltip regular-text"/>
                <p class="description">
                    <?php esc_html_e('Timestamp of the most recent successful sync.', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>

        <tr data-id="next_scheduled_sync_tr" class="js-wps-show_if_ai_insight_auto_sync_enabled">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Next Scheduled Sync', 'wp-statistics'); ?></span>
            </th>
            <td>
                <input type="text" title="<?php echo $nextSyncTime ? esc_attr(DateTime::format($nextSyncTime, ['include_time' => true])) : esc_attr__('Never', 'wp-statistics'); ?>" value="<?php echo $nextSyncTime ? esc_attr__('In ', 'wp-statistics') . human_time_diff($nextSyncTime) : esc_attr__('Never', 'wp-statistics'); ?>" aria-label="<?php esc_attr_e('Next Scheduled Sync', 'wp-statistics'); ?>" readonly class="wps-tooltip regular-text"/>
                <p class="description">
                    <?php esc_html_e('When the next automatic sync will occur.', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>

        <tr data-id="records_synced_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Records Synced', 'wp-statistics'); ?></span>
            </th>
            <td>
                <input type="text" value="<?php echo sprintf(esc_attr__('%s records'), number_format_i18n($gscRecords)); ?>" aria-label="<?php esc_html_e('Records Synced', 'wp-statistics'); ?>" readonly class="regular-text"/>
                <p class="description">
                    <?php esc_html_e('Total number of GSC records currently stored in the database.', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>

        <tr data-id="database_table_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Database Table Size', 'wp-statistics'); ?></span>
            </th>
            <td>
                <input type="text" value="<?php echo sprintf(esc_attr__('%s MB', 'wp-statistics'), $tableSize); ?>" aria-label="<?php esc_attr_e('Database Table Size', 'wp-statistics'); ?>" readonly class="regular-text"/>
                <p class="description">
                    <?php esc_html_e('Total disk space used by AI Insights custom tables.', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>

        </tbody>
    </table>
</div>

<?php
if ($isAiInsightActive) {
    submit_button(esc_html__('Update', 'wp-statistics'), 'wps-button wps-button--primary', 'submit', false, ['id' => 'ai_insights_submit', 'OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='ai-insights-settings'"]);
}

// GSC Sync Confirmation Modal
View::load('components/modals/setting-confirmation/setting-confirmation-modal', [
    'title'               => __('Sync Data Again?', 'wp-statistics'),
    'description'         => __('GSC data was synced recently. Syncing again may not retrieve new data. Do you want to continue anyway?', 'wp-statistics'),
    'primaryButtonText'   => __('Sync Anyway', 'wp-statistics'),
    'secondaryButtonText' => __('Cancel', 'wp-statistics'),
    'primaryButtonStyle'  => 'primary',
    'secondaryButtonStyle'=> 'cancel',
    'class'               => 'wps-modal--sync-data',
    'actions'             => [
        'primary'   => 'confirmSync',
        'secondary' => 'closeModal'
    ]
]);
?>
