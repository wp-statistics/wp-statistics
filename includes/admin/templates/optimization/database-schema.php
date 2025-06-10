<?php

use WP_Statistics\Service\Database\Managers\SchemaMaintainer;

$schemaCheckResult = SchemaMaintainer::check();
$databaseStatus    = $schemaCheckResult['status'] ?? null;
?>
<div class="wrap wps-wrap wps-wrap__setting-form js-updatesForm">
    <h2 class="wps-settings-box__title">
        <span><?php esc_html_e('Database Schema', 'wp-statistics'); ?></span>
        <a href="#" target="_blank"><?php esc_html_e('View Guide', 'wp-statistics'); ?></a>
    </h2>
    <div class="postbox">
        <form class="wps-submit-agree" data-agree="<?php esc_html_e('Are you sure you want to repair the schema issues?', 'wp-statistics'); ?>" action="<?php echo esc_url(admin_url('admin.php?page=wps_optimization_page&tab=updates')) ?>" id="wps_database_schema_form" method="post">
            <?php wp_nonce_field('wps_optimization_nonce'); ?>
            <table class="form-table">
                <tbody>
                <tr valign="top" class="wps-settings-box_head">
                    <th scope="row" colspan="2"><h3><?php esc_html_e('Database Schema Issues', 'wp-statistics'); ?></h3></th>
                </tr>

                <tr valign="top" data-id="database_schema_issues">
                    <th scope="row">
                        <label><?php esc_html_e('Check & Repair Database Schema', 'wp-statistics'); ?></label>
                    </th>
                    <td>
                        <?php if ($databaseStatus === 'success'): ?>
                            <label>✅ <?php esc_html_e('Database schema is healthy.', 'wp-statistics'); ?></label>
                        <?php else: ?>
                            <input type="hidden" id="repair-schema-submit" name="repair_schema_action" value="1"/>
                            <button id="repair-schema-submit-button" class="wps-button wps-button--danger-outline js-openModal-setting-confirmation wps-mt-0" type="button" name="database-schema-issues-submit">⚠️ <?php esc_html_e('Repair Schema Issues', 'wp-statistics'); ?></button>
                            <p class="description"><?php esc_html_e('Checks the integrity of the WP Statistics database tables and automatically applies any required fixes to keep your analytics accurate.', 'wp-statistics'); ?></p>
                            <label><?php esc_html_e('Detected Schema Issues', 'wp-statistics'); ?></label>
                            <p class="description"><?php _e('We’ve found the following inconsistencies. Click <b>Repair Schema Issues</b> to fix them automatically.', 'wp-statistics'); ?></p>
                            <ul>
                                <?php
                                if (!empty($schemaCheckResult['issues']) && is_array($schemaCheckResult['issues'])) {
                                    foreach ($schemaCheckResult['issues'] as $issue) {
                                        if ($issue['type'] === 'missing_column') {
                                            $message = "<b>{$issue['table']}.{$issue['column']}</b> — " . __('Missing column');
                                            echo '<li>' . $message . '</li>';
                                        }
                                    }
                                }

                                if (!empty($schemaCheckResult['errors']) && is_array($schemaCheckResult['errors'])) {
                                    foreach ($schemaCheckResult['errors'] as $issue) {
                                        if ($issue['type'] === 'table_missing') {
                                            $message = "<b>{$issue['table']}</b> — " . __('Missing table');
                                            echo '<li>' . $message . '</li>';
                                        }
                                    }
                                }
                                ?>
                            </ul>
                        <?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
