<?php

namespace WP_Statistics\Service\Admin\Settings\Definitions;

use WP_Statistics\Utils\Environment;
use WP_Statistics\Utils\QueryParams;

/**
 * Single source of truth for settings-area tabs, cards, fields, and defaults.
 *
 * Returns one nested array via getDefinitions():
 *   tab → cards → fields
 *
 * Defaults are extracted automatically by getDefaults() from:
 *   - Tab-level `defaults` (hidden settings not tied to fields)
 *   - Field-level `default` values (keyed by `setting_key`)
 *
 * @since 15.3.0
 */
class SettingsAreaDefinitions
{
    /**
     * Complete settings definitions as one nested array.
     *
     * Each tab contains:
     *   - label, icon, order, save_description — tab metadata
     *   - defaults     — hidden settings with default values (also whitelisted for save)
     *   - allowed_keys — keys whitelisted for save but without default values
     *   - cards        — nested array of card → fields
     *
     * @return array
     */
    public function getDefinitions(): array
    {
        return [
            // ==============================================================
            // General
            // ==============================================================
            'general' => [
                'label'            => __('General', 'wp-statistics'),
                'icon'             => 'settings',
                'order'            => 10,
                'save_description' => __('General settings have been updated.', 'wp-statistics'),
                'defaults'         => [
                    'useronline' => true,
                    'pages'      => true,
                ],
                'cards' => [
                    'tracking' => [
                        'title'       => __('Tracking Options', 'wp-statistics'),
                        'description' => __('Configure what data WP Statistics collects from your visitors.', 'wp-statistics'),
                        'order'       => 10,
                        'fields'      => [
                            'visitors_log' => [
                                'type'        => 'toggle',
                                'setting_key' => 'visitors_log',
                                'label'       => __('Track Logged-In User Activity', 'wp-statistics'),
                                'description' => __('Tracks activities of logged-in users with their WordPress User IDs. If disabled, logged-in users are tracked anonymously.', 'wp-statistics'),
                                'default'     => false,
                                'order'       => 10,
                            ],
                        ],
                    ],
                    'tracker-config' => [
                        'title'       => __('Tracker Configuration', 'wp-statistics'),
                        'description' => __('Configure how the tracking script works on your site.', 'wp-statistics'),
                        'order'       => 20,
                        'fields'      => [
                            'bypass_ad_blockers' => [
                                'type'        => 'toggle',
                                'setting_key' => 'bypass_ad_blockers',
                                'label'       => __('Bypass Ad Blockers', 'wp-statistics'),
                                'description' => __('Dynamically load the tracking script with a unique name and address to bypass ad blockers.', 'wp-statistics'),
                                'default'     => false,
                                'order'       => 10,
                            ],
                        ],
                    ],
                ],
            ],

            // ==============================================================
            // Display
            // ==============================================================
            'display' => [
                'label'            => __('Display', 'wp-statistics'),
                'icon'             => 'monitor',
                'order'            => 20,
                'save_description' => __('Display settings have been updated.', 'wp-statistics'),
                'cards' => [
                    'admin-interface' => [
                        'title'       => __('Admin Interface', 'wp-statistics'),
                        'description' => __('Configure how WP Statistics appears in the WordPress admin area.', 'wp-statistics'),
                        'order'       => 10,
                        'fields'      => [
                            'disable_editor' => [
                                'type'        => 'toggle',
                                'setting_key' => 'disable_editor',
                                'label'       => __('View Stats in Editor', 'wp-statistics'),
                                'description' => __('Show a summary of content view statistics in the post editor.', 'wp-statistics'),
                                'default'     => false,
                                'inverted'    => true,
                                'order'       => 10,
                            ],
                            'disable_column' => [
                                'type'        => 'toggle',
                                'setting_key' => 'disable_column',
                                'label'       => __('Stats Column in Content List', 'wp-statistics'),
                                'description' => __('Display the statistics column in the content list menus, showing page view or visitor counts.', 'wp-statistics'),
                                'default'     => false,
                                'inverted'    => true,
                                'order'       => 20,
                            ],
                            'enable_user_column' => [
                                'type'        => 'toggle',
                                'setting_key' => 'enable_user_column',
                                'label'       => __('Views Column in User List', 'wp-statistics'),
                                'description' => __('Display the "Views" column in the admin user list. Requires "Track Logged-In User Activity" to be enabled.', 'wp-statistics'),
                                'default'     => false,
                                'order'       => 30,
                            ],
                            'display_notifications' => [
                                'type'        => 'toggle',
                                'setting_key' => 'display_notifications',
                                'label'       => __('WP Statistics Notifications', 'wp-statistics'),
                                'description' => __('Display important notifications such as new version releases, feature updates, and news.', 'wp-statistics'),
                                'default'     => true,
                                'order'       => 40,
                            ],
                            'hide_notices' => [
                                'type'        => 'toggle',
                                'setting_key' => 'hide_notices',
                                'label'       => __('Disable Admin Notices', 'wp-statistics'),
                                'description' => __('Hides configuration and optimization notices in the admin area. Critical database notices will still be shown.', 'wp-statistics'),
                                'default'     => false,
                                'order'       => 50,
                            ],
                            'menu_bar' => [
                                'type'        => 'toggle',
                                'setting_key' => 'menu_bar',
                                'label'       => __('Show Stats in Admin Bar', 'wp-statistics'),
                                'description' => __('Display a quick statistics summary in the WordPress admin bar.', 'wp-statistics'),
                                'default'     => true,
                                'order'       => 60,
                            ],
                        ],
                    ],
                    'frontend-display' => [
                        'title'       => __('Frontend Display', 'wp-statistics'),
                        'description' => __('Configure how statistics appear on your website frontend.', 'wp-statistics'),
                        'order'       => 20,
                        'fields'      => [
                            'show_hits' => [
                                'type'        => 'toggle',
                                'setting_key' => 'show_hits',
                                'label'       => __('Views in Single Contents', 'wp-statistics'),
                                'description' => __("Shows the view count on the content's page for visitor insight.", 'wp-statistics'),
                                'default'     => false,
                                'order'       => 10,
                            ],
                            'display_hits_position' => [
                                'type'         => 'select',
                                'setting_key'  => 'display_hits_position',
                                'label'        => __('Display Position', 'wp-statistics'),
                                'description'  => __('Choose the position to show views on your content pages.', 'wp-statistics'),
                                'default'      => 'none',
                                'layout'       => 'stacked',
                                'nested'       => true,
                                'visible_when' => [
                                    'show_hits' => true,
                                ],
                                'options' => [
                                    ['value' => 'none', 'label' => __('Please select', 'wp-statistics')],
                                    ['value' => 'before_content', 'label' => __('Before Content', 'wp-statistics')],
                                    ['value' => 'after_content', 'label' => __('After Content', 'wp-statistics')],
                                ],
                                'order' => 20,
                            ],
                        ],
                    ],
                ],
            ],

            // ==============================================================
            // Privacy
            // ==============================================================
            'privacy' => [
                'label'            => __('Privacy', 'wp-statistics'),
                'icon'             => 'shield',
                'order'            => 30,
                'save_description' => __('Privacy settings have been updated.', 'wp-statistics'),
                'cards' => [
                    'data-protection' => [
                        'title'       => __('Data Protection', 'wp-statistics'),
                        'description' => __('Configure how visitor IP addresses are stored and processed.', 'wp-statistics'),
                        'order'       => 10,
                        'fields'      => [
                            'store_ip' => [
                                'type'        => 'toggle',
                                'setting_key' => 'store_ip',
                                'label'       => __('Store IP Addresses', 'wp-statistics'),
                                'description' => __('Record full visitor IP addresses in the database. When disabled, only anonymous hashes are stored.', 'wp-statistics'),
                                'default'     => false,
                                'order'       => 10,
                            ],
                            'hash_rotation_interval' => [
                                'type'        => 'select',
                                'setting_key' => 'hash_rotation_interval',
                                'label'       => __('Hash Rotation Interval', 'wp-statistics'),
                                'description' => __('How often the salt used for visitor hashing rotates. Shorter intervals improve privacy but reduce returning-visitor detection accuracy.', 'wp-statistics'),
                                'default'     => 'daily',
                                'options'     => [
                                    ['value' => 'daily', 'label' => __('Daily', 'wp-statistics')],
                                    ['value' => 'weekly', 'label' => __('Weekly', 'wp-statistics')],
                                    ['value' => 'monthly', 'label' => __('Monthly', 'wp-statistics')],
                                    ['value' => 'disabled', 'label' => __('Disabled', 'wp-statistics')],
                                ],
                                'order' => 20,
                            ],
                            'hash_rotation_warning' => [
                                'type'         => 'notice',
                                'notice_type'  => 'warning',
                                'message'      => __('Disabling hash rotation means the same visitor will always produce the same hash. This improves returning-visitor detection but reduces privacy protection.', 'wp-statistics'),
                                'visible_when' => [
                                    'hash_rotation_interval' => 'disabled',
                                ],
                                'order' => 25,
                            ],
                        ],
                    ],
                    'user-preferences' => [
                        'title'       => __('User Preferences', 'wp-statistics'),
                        'description' => __('Configure consent integration and respect user privacy preferences.', 'wp-statistics'),
                        'order'       => 20,
                        'fields'      => [
                            'consent_integration' => [
                                'type'        => 'select',
                                'setting_key' => 'consent_integration',
                                'label'       => __('Consent Plugin Integration', 'wp-statistics'),
                                'description' => __('Integrate with supported consent management plugins.', 'wp-statistics'),
                                'default'     => 'none',
                                'options'     => [
                                    ['value' => 'none', 'label' => __('None', 'wp-statistics')],
                                    ['value' => 'wp_consent_api', 'label' => __('Via WP Consent API', 'wp-statistics')],
                                    ['value' => 'complianz', 'label' => __('Complianz', 'wp-statistics')],
                                    ['value' => 'cookieyes', 'label' => __('CookieYes', 'wp-statistics')],
                                    ['value' => 'real_cookie_banner', 'label' => __('Real Cookie Banner', 'wp-statistics')],
                                    ['value' => 'borlabs_cookie', 'label' => __('Borlabs Cookie', 'wp-statistics')],
                                ],
                                'order' => 10,
                            ],
                            'consent_level_integration' => [
                                'type'         => 'select',
                                'setting_key'  => 'consent_level_integration',
                                'label'        => __('Consent Category', 'wp-statistics'),
                                'description'  => __('Select the consent category WP Statistics should track.', 'wp-statistics'),
                                'default'      => 'disabled',
                                'nested'       => true,
                                'visible_when' => [
                                    'consent_integration' => 'wp_consent_api',
                                ],
                                'options' => [
                                    ['value' => 'functional', 'label' => __('Functional', 'wp-statistics')],
                                    ['value' => 'statistics-anonymous', 'label' => __('Statistics-Anonymous', 'wp-statistics')],
                                    ['value' => 'statistics', 'label' => __('Statistics', 'wp-statistics')],
                                    ['value' => 'marketing', 'label' => __('Marketing', 'wp-statistics')],
                                ],
                                'order' => 20,
                            ],
                            'anonymous_tracking' => [
                                'type'         => 'toggle',
                                'setting_key'  => 'anonymous_tracking',
                                'label'        => __('Anonymous Tracking', 'wp-statistics'),
                                'description'  => __('Track all users anonymously without PII by default.', 'wp-statistics'),
                                'default'      => false,
                                'nested'       => true,
                                'visible_when' => [
                                    'consent_integration' => ['!=', 'none'],
                                ],
                                'order' => 30,
                            ],
                        ],
                    ],
                    'privacy-audit' => [
                        'title'       => __('Privacy Audit', 'wp-statistics'),
                        'description' => __('Enable privacy monitoring and compliance tools.', 'wp-statistics'),
                        'order'       => 30,
                        'fields'      => [
                            'privacy_audit' => [
                                'type'        => 'toggle',
                                'setting_key' => 'privacy_audit',
                                'label'       => __('Enable Privacy Audit', 'wp-statistics'),
                                'description' => __('Show privacy indicators on settings that affect user privacy.', 'wp-statistics'),
                                'default'     => true,
                                'order'       => 10,
                            ],
                        ],
                    ],
                ],
            ],

            // ==============================================================
            // Notifications
            // ==============================================================
            'notifications' => [
                'label'            => __('Notifications', 'wp-statistics'),
                'icon'             => 'bell',
                'order'            => 40,
                'save_description' => __('Notification settings have been updated.', 'wp-statistics'),
                'defaults'         => [
                    'email_reports_enabled'   => false,
                    'email_reports_frequency' => 'weekly',
                ],
                'cards' => [
                    'email-reports' => [
                        'title'       => __('Email Reports', 'wp-statistics'),
                        'description' => __('Receive periodic email summaries of your site statistics.', 'wp-statistics'),
                        'order'       => 10,
                        'fields'      => [
                            'email_reports_enabled' => [
                                'type'        => 'toggle',
                                'setting_key' => 'email_reports_enabled',
                                'label'       => __('Enable Email Reports', 'wp-statistics'),
                                'description' => __('Send periodic statistics summaries to your email.', 'wp-statistics'),
                                'default'     => false,
                                'order'       => 10,
                            ],
                            'email_reports_frequency' => [
                                'type'         => 'select',
                                'setting_key'  => 'email_reports_frequency',
                                'label'        => __('Report Frequency', 'wp-statistics'),
                                'default'      => 'weekly',
                                'nested'       => true,
                                'visible_when' => ['email_reports_enabled' => true],
                                'options'      => [
                                    ['value' => 'weekly',  'label' => __('Weekly', 'wp-statistics')],
                                    ['value' => 'monthly', 'label' => __('Monthly (1st of month)', 'wp-statistics')],
                                ],
                                'order' => 20,
                            ],
                            'email_reports_delivery_hour' => [
                                'type'         => 'select',
                                'setting_key'  => 'email_reports_delivery_hour',
                                'label'        => __('Delivery Time', 'wp-statistics'),
                                'default'      => '8',
                                'nested'       => true,
                                'visible_when' => ['email_reports_enabled' => true],
                                'options'      => [
                                    ['value' => '0',  'label' => '12:00 AM'],
                                    ['value' => '1',  'label' => '1:00 AM'],
                                    ['value' => '2',  'label' => '2:00 AM'],
                                    ['value' => '3',  'label' => '3:00 AM'],
                                    ['value' => '4',  'label' => '4:00 AM'],
                                    ['value' => '5',  'label' => '5:00 AM'],
                                    ['value' => '6',  'label' => '6:00 AM'],
                                    ['value' => '7',  'label' => '7:00 AM'],
                                    ['value' => '8',  'label' => '8:00 AM'],
                                    ['value' => '9',  'label' => '9:00 AM'],
                                    ['value' => '10', 'label' => '10:00 AM'],
                                    ['value' => '11', 'label' => '11:00 AM'],
                                    ['value' => '12', 'label' => '12:00 PM'],
                                    ['value' => '13', 'label' => '1:00 PM'],
                                    ['value' => '14', 'label' => '2:00 PM'],
                                    ['value' => '15', 'label' => '3:00 PM'],
                                    ['value' => '16', 'label' => '4:00 PM'],
                                    ['value' => '17', 'label' => '5:00 PM'],
                                    ['value' => '18', 'label' => '6:00 PM'],
                                    ['value' => '19', 'label' => '7:00 PM'],
                                    ['value' => '20', 'label' => '8:00 PM'],
                                    ['value' => '21', 'label' => '9:00 PM'],
                                    ['value' => '22', 'label' => '10:00 PM'],
                                    ['value' => '23', 'label' => '11:00 PM'],
                                ],
                                'order' => 25,
                            ],
                            'email_reports_schedule_info' => [
                                'type'         => 'component',
                                'component'    => 'EmailReportScheduleInfo',
                                'nested'       => true,
                                'visible_when' => ['email_reports_enabled' => true],
                                'order'        => 26,
                            ],
                            'email_list' => [
                                'type'         => 'input',
                                'setting_key'  => 'email_list',
                                'label'        => __('Recipient Email', 'wp-statistics'),
                                'description'  => __('Email address to receive reports. Defaults to admin email.', 'wp-statistics'),
                                'layout'       => 'stacked',
                                'nested'       => true,
                                'visible_when' => ['email_reports_enabled' => true],
                                'order'        => 30,
                            ],
                            'email_reports_content_info' => [
                                'type'         => 'component',
                                'component'    => 'EmailReportContentInfo',
                                'nested'       => true,
                                'visible_when' => ['email_reports_enabled' => true],
                                'order'        => 40,
                            ],
                        ],
                    ],
                ],
            ],

            // ==============================================================
            // Exclusions
            // ==============================================================
            'exclusions' => [
                'label'            => __('Exclusions', 'wp-statistics'),
                'icon'             => 'ban',
                'order'            => 50,
                'save_description' => __('Exclusion settings have been updated.', 'wp-statistics'),
                'defaults'         => [
                    'exclude_ip'              => '',
                    'exclude_anonymous_users' => false,
                    'query_params_allow_list' => QueryParams::getDefaultAllowedList('string'),
                ],
                'cards' => [
                    'page-exclusions' => [
                        'title'       => __('Page Exclusions', 'wp-statistics'),
                        'description' => __('Exclude specific pages or paths from being tracked.', 'wp-statistics'),
                        'order'       => 10,
                        'fields'      => [
                            'exclude_loginpage' => [
                                'type'        => 'toggle',
                                'setting_key' => 'exclude_loginpage',
                                'label'       => __('Exclude Login Page', 'wp-statistics'),
                                'description' => __("Don't track WordPress login page visits.", 'wp-statistics'),
                                'default'     => true,
                                'order'       => 10,
                            ],
                            'exclude_feeds' => [
                                'type'        => 'toggle',
                                'setting_key' => 'exclude_feeds',
                                'label'       => __('Exclude RSS Feeds', 'wp-statistics'),
                                'description' => __("Don't count RSS feed requests in statistics.", 'wp-statistics'),
                                'default'     => true,
                                'order'       => 20,
                            ],
                            'exclude_404s' => [
                                'type'        => 'toggle',
                                'setting_key' => 'exclude_404s',
                                'label'       => __('Exclude 404 Pages', 'wp-statistics'),
                                'description' => __("Don't track visits to pages that return 404 errors. Note: 404s for static files (images, CSS, JS) are always excluded automatically.", 'wp-statistics'),
                                'default'     => false,
                                'order'       => 30,
                            ],
                            'excluded_urls' => [
                                'type'        => 'textarea',
                                'setting_key' => 'excluded_urls',
                                'label'       => __('Excluded URLs', 'wp-statistics'),
                                'description' => __('Enter URL paths to exclude, one per line. Supports wildcards (*).', 'wp-statistics'),
                                'default'     => '',
                                'layout'      => 'stacked',
                                'placeholder' => "/admin\n/wp-json\n/api/*",
                                'rows'        => 4,
                                'order'       => 40,
                            ],
                        ],
                    ],
                    'role-exclusions' => [
                        'title'       => __('User Role Exclusions', 'wp-statistics'),
                        'description' => __('Exclude users with specific roles from being tracked.', 'wp-statistics'),
                        'order'       => 20,
                        'fields'      => [
                            'role_exclusions' => [
                                'type'      => 'component',
                                'component' => 'RoleExclusions',
                                'order'     => 10,
                            ],
                        ],
                    ],
                    'ip-exclusions' => [
                        'title'       => __('IP Exclusions', 'wp-statistics'),
                        'description' => __('Exclude specific IP addresses from being tracked.', 'wp-statistics'),
                        'order'       => 30,
                        'fields'      => [
                            'exclude_ip_field' => [
                                'type'      => 'component',
                                'component' => 'ExcludeIpField',
                                'order'     => 10,
                            ],
                        ],
                    ],
                    'country-filters' => [
                        'title'       => __('Country Filters', 'wp-statistics'),
                        'description' => __('Filter visitors by country. Excluded countries take priority over included countries.', 'wp-statistics'),
                        'order'       => 40,
                        'fields'      => [
                            'excluded_countries' => [
                                'type'        => 'textarea',
                                'setting_key' => 'excluded_countries',
                                'label'       => __('Excluded Countries', 'wp-statistics'),
                                'description' => __('Enter ISO country codes (e.g., US, CN, DE) to exclude, one per line.', 'wp-statistics'),
                                'default'     => '',
                                'layout'      => 'stacked',
                                'placeholder' => "US\nCN",
                                'rows'        => 3,
                                'order'       => 10,
                            ],
                            'included_countries' => [
                                'type'        => 'textarea',
                                'setting_key' => 'included_countries',
                                'label'       => __('Included Countries Only', 'wp-statistics'),
                                'description' => __('If specified, only track visitors from these countries. Enter ISO country codes (e.g., US, CA, GB), one per line.', 'wp-statistics'),
                                'default'     => '',
                                'layout'      => 'stacked',
                                'placeholder' => "US\nCA\nGB",
                                'rows'        => 3,
                                'order'       => 20,
                            ],
                        ],
                    ],
                    'query-params' => [
                        'title'       => __('URL Query Parameters', 'wp-statistics'),
                        'description' => __('Control which URL query parameters are retained in your statistics.', 'wp-statistics'),
                        'order'       => 50,
                        'fields'      => [
                            'query_params_field' => [
                                'type'      => 'component',
                                'component' => 'QueryParamsField',
                                'order'     => 10,
                            ],
                        ],
                    ],
                    'bot-detection' => [
                        'title'       => __('Bot & Crawler Detection', 'wp-statistics'),
                        'description' => __('Known bots and crawlers are automatically detected. Use these settings for additional filtering.', 'wp-statistics'),
                        'order'       => 60,
                        'fields'      => [
                            'robotlist' => [
                                'type'        => 'textarea',
                                'setting_key' => 'robotlist',
                                'label'       => __('Additional Bot User Agents', 'wp-statistics'),
                                'description' => __('Known bots are automatically detected. Add custom user agent names here to exclude additional bots, one per line. Each entry must be more than 3 characters.', 'wp-statistics'),
                                'default'     => '',
                                'layout'      => 'stacked',
                                'placeholder' => "MyCustomBot\nInternalCrawler\nTestAgent",
                                'rows'        => 5,
                                'order'       => 10,
                            ],
                            'robot_threshold' => [
                                'type'        => 'number',
                                'setting_key' => 'robot_threshold',
                                'label'       => __('Daily Hit Threshold', 'wp-statistics'),
                                'description' => __('Consider visitors with more than this many daily hits as bots (0 to disable).', 'wp-statistics'),
                                'default'     => 0,
                                'layout'      => 'stacked',
                                'min'         => 0,
                                'max'         => 9999,
                                'order'       => 20,
                            ],
                        ],
                    ],
                    'exclusion-logging' => [
                        'title'       => __('Exclusion Logging', 'wp-statistics'),
                        'description' => __('Log details about excluded visits for debugging and auditing.', 'wp-statistics'),
                        'order'       => 70,
                        'fields'      => [
                            'record_exclusions' => [
                                'type'        => 'toggle',
                                'setting_key' => 'record_exclusions',
                                'label'       => __('Record Exclusions', 'wp-statistics'),
                                'description' => __('Log all excluded visitors with the reason for exclusion. Useful for verifying that your exclusion rules are working correctly.', 'wp-statistics'),
                                'default'     => false,
                                'order'       => 10,
                            ],
                        ],
                    ],
                ],
            ],

            // ==============================================================
            // Access
            // ==============================================================
            'access' => [
                'label'            => __('Access', 'wp-statistics'),
                'icon'             => 'users',
                'order'            => 60,
                'save_description' => __('Access settings have been updated.', 'wp-statistics'),
                'allowed_keys'     => [
                    'access_levels',
                    'read_capability',
                    'manage_capability',
                ],
                'cards' => [
                    'roles-permissions' => [
                        'title'       => __('Roles & Permissions', 'wp-statistics'),
                        'description' => __('Control what level of statistics access each user role receives.', 'wp-statistics'),
                        'order'       => 10,
                        'fields'      => [
                            'access_level_table' => [
                                'type'      => 'component',
                                'component' => 'AccessLevelTable',
                                'order'     => 10,
                            ],
                        ],
                    ],
                ],
            ],

            // ==============================================================
            // Data Management
            // ==============================================================
            'data-management' => [
                'label'            => __('Data Management', 'wp-statistics'),
                'icon'             => 'database',
                'order'            => 70,
                'save_description' => __('Data management settings have been updated.', 'wp-statistics'),
                'tab_key'          => 'data',
                'defaults'         => [
                    'data_retention_mode' => 'forever',
                    'data_retention_days' => 180,
                    'schedule_dbmaint'    => true,
                    'schedule_dbmaint_days' => '180',
                ],
                'cards' => [
                    'data-retention' => [
                        'title'       => __('Data Retention', 'wp-statistics'),
                        'description' => __('Choose how to manage old statistics data. This affects database size and query performance.', 'wp-statistics'),
                        'order'       => 10,
                        'fields'      => [
                            'retention_mode_selector' => [
                                'type'      => 'component',
                                'component' => 'RetentionModeSelector',
                                'order'     => 10,
                            ],
                        ],
                    ],
                    'danger-zone' => [
                        'title'       => __('Danger Zone', 'wp-statistics'),
                        'description' => __('These actions are irreversible. Please proceed with caution.', 'wp-statistics'),
                        'variant'     => 'danger',
                        'order'       => 20,
                        'fields'      => [
                            'apply_retention_action' => [
                                'type'      => 'component',
                                'component' => 'ApplyRetentionAction',
                                'order'     => 10,
                            ],
                        ],
                    ],
                ],
            ],

            // ==============================================================
            // Advanced
            // ==============================================================
            'advanced' => [
                'label'            => __('Advanced', 'wp-statistics'),
                'icon'             => 'wrench',
                'order'            => 80,
                'save_description' => __('Advanced settings have been updated.', 'wp-statistics'),
                'defaults'         => [
                    'ip_method' => 'sequential',
                ],
                'allowed_keys'     => [
                    'user_custom_header_ip_method',
                ],
                'cards' => [
                    'geoip-settings' => [
                        'title'       => __('GeoIP Settings', 'wp-statistics'),
                        'description' => __('Configure how visitor locations are detected and displayed.', 'wp-statistics'),
                        'order'       => 10,
                        'fields'      => [
                            'geoip_location_detection_method' => [
                                'type'        => 'select',
                                'setting_key' => 'geoip_location_detection_method',
                                'label'       => __('Location Detection Method', 'wp-statistics'),
                                'default'     => 'maxmind',
                                'layout'      => 'stacked',
                                'options'     => [
                                    ['value' => 'maxmind', 'label' => __('MaxMind GeoIP', 'wp-statistics')],
                                    ['value' => 'dbip', 'label' => __('DB-IP', 'wp-statistics')],
                                    ['value' => 'cf', 'label' => __('Cloudflare IP Geolocation', 'wp-statistics')],
                                ],
                                'order' => 10,
                            ],
                            'cf_info_notice' => [
                                'type'         => 'notice',
                                'notice_type'  => 'info',
                                'message'      => __('Cloudflare provides location data via HTTP headers. Make sure IP Geolocation is enabled in your Cloudflare dashboard.', 'wp-statistics'),
                                'visible_when' => [
                                    'geoip_location_detection_method' => 'cf',
                                ],
                                'order' => 15,
                            ],
                            'geoip_license_type' => [
                                'type'         => 'select',
                                'setting_key'  => 'geoip_license_type',
                                'label'        => __('Database Update Source', 'wp-statistics'),
                                'description'  => __('Select how the GeoIP database is downloaded. The free option uses a community-maintained database.', 'wp-statistics'),
                                'default'      => 'js-deliver',
                                'layout'       => 'stacked',
                                'visible_when' => [
                                    'geoip_location_detection_method' => ['!=', 'cf'],
                                ],
                                'options' => [
                                    ['value' => 'js-deliver', 'label' => __('Free Database', 'wp-statistics')],
                                    ['value' => 'user-license', 'label' => __('Custom License Key', 'wp-statistics')],
                                ],
                                'order' => 20,
                            ],
                            'geoip_license_key' => [
                                'type'         => 'input',
                                'setting_key'  => 'geoip_license_key',
                                'label'        => __('MaxMind License Key', 'wp-statistics'),
                                'default'      => '',
                                'layout'       => 'stacked',
                                'nested'       => true,
                                'placeholder'  => __('Enter your MaxMind license key', 'wp-statistics'),
                                'visible_when' => [
                                    'geoip_license_type'              => 'user-license',
                                    'geoip_location_detection_method' => 'maxmind',
                                ],
                                'order' => 25,
                            ],
                            'geoip_dbip_license_key_option' => [
                                'type'         => 'input',
                                'setting_key'  => 'geoip_dbip_license_key_option',
                                'label'        => __('DB-IP License Key', 'wp-statistics'),
                                'default'      => '',
                                'layout'       => 'stacked',
                                'nested'       => true,
                                'placeholder'  => __('Enter your DB-IP license key', 'wp-statistics'),
                                'visible_when' => [
                                    'geoip_license_type'              => 'user-license',
                                    'geoip_location_detection_method' => 'dbip',
                                ],
                                'order' => 30,
                            ],
                        ],
                    ],
                    'data-sharing' => [
                        'title'       => __('Anonymous Data Sharing', 'wp-statistics'),
                        'description' => __('Help improve WP Statistics.', 'wp-statistics'),
                        'order'       => 20,
                        'fields'      => [
                            'share_anonymous_data' => [
                                'type'        => 'toggle',
                                'setting_key' => 'share_anonymous_data',
                                'label'       => __('Share Anonymous Usage Data', 'wp-statistics'),
                                'description' => __('Help us improve WP Statistics by sharing anonymous usage data.', 'wp-statistics'),
                                'default'     => false,
                                'order'       => 10,
                            ],
                        ],
                    ],
                    'advanced-danger-zone' => [
                        'title'       => __('Danger Zone', 'wp-statistics'),
                        'description' => __('These actions are irreversible. Please proceed with caution.', 'wp-statistics'),
                        'variant'     => 'danger',
                        'order'       => 30,
                        'fields'      => [
                            'delete_data_on_uninstall' => [
                                'type'        => 'toggle',
                                'setting_key' => 'delete_data_on_uninstall',
                                'label'       => __('Delete All Data on Uninstall', 'wp-statistics'),
                                'description' => __('Remove all WP Statistics data from the database when the plugin is uninstalled. This action cannot be undone.', 'wp-statistics'),
                                'default'     => false,
                                'order'       => 10,
                            ],
                            'restore_defaults_action' => [
                                'type'      => 'component',
                                'component' => 'RestoreDefaultsAction',
                                'order'     => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Extract all defaults from definitions.
     *
     * Collects from:
     *   1. Tab-level `defaults` array (hidden settings not tied to fields)
     *   2. Field-level `default` values (keyed by `setting_key`)
     *   3. Dynamic defaults requiring runtime values
     *
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        $defaults = [];

        foreach ($this->getDefinitions() as $tab) {
            // Tab-level defaults (hidden settings not tied to fields)
            if (!empty($tab['defaults'])) {
                $defaults = array_merge($defaults, $tab['defaults']);
            }

            // Field-level defaults
            foreach ($tab['cards'] ?? [] as $card) {
                foreach ($card['fields'] ?? [] as $field) {
                    if (!empty($field['setting_key']) && array_key_exists('default', $field)) {
                        $defaults[$field['setting_key']] = $field['default'];
                    }
                }
            }
        }

        // Dynamic defaults requiring runtime values
        $defaults['email_list'] = Environment::getAdminEmail();

        return $defaults;
    }
}
