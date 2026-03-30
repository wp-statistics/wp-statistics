<?php

namespace WP_Statistics\Service\Admin\Tools;

use WP_Statistics\Utils\OptionValueFormatter;

/**
 * Inspects WP Statistics options, transients, and user meta for debugging.
 *
 * Provides structured access to all plugin-related data stored in WordPress.
 * Third-party plugins can extend via filters.
 *
 * @since 15.0.0
 */
class OptionInspectionService
{
    /**
     * Get all WP Statistics options, grouped by category.
     *
     * @return array[] Each entry: key, value, group (main|db|jobs|cache|version).
     */
    public function getOptions(): array
    {
        $optionsList = [];

        // Main plugin options
        $mainOptions = get_option('wp_statistics', []);

        if (is_array($mainOptions)) {
            foreach ($mainOptions as $key => $value) {
                $optionsList[] = [
                    'key'   => $key,
                    'value' => OptionValueFormatter::format($value),
                    'group' => 'main',
                ];
            }
        }

        // Grouped options
        $groupedOptions = $this->getGroupedOptionNames();

        foreach ($groupedOptions as $optionName => $group) {
            $groupData = get_option($optionName, []);
            if (is_array($groupData)) {
                foreach ($groupData as $key => $value) {
                    $optionsList[] = [
                        'key'   => $key,
                        'value' => OptionValueFormatter::format($value),
                        'group' => $group,
                    ];
                }
            }
        }

        // Version/installation options
        $versionOptions = [
            'wp_statistics_plugin_version'    => get_option('wp_statistics_plugin_version', '-'),
            'wp_statistics_db_version'        => get_option('wp_statistics_db_version', '-'),
            'wp_statistics_is_fresh'          => get_option('wp_statistics_is_fresh', '-'),
            'wp_statistics_installation_time' => get_option('wp_statistics_installation_time', '-'),
        ];

        foreach ($versionOptions as $key => $value) {
            $optionsList[] = [
                'key'   => str_replace('wp_statistics_', '', $key),
                'value' => OptionValueFormatter::format($value),
                'group' => 'version',
            ];
        }

        return $optionsList;
    }

    /**
     * Get WP Statistics transients.
     *
     * @return array[] Each entry: name, value.
     */
    public function getTransients(): array
    {
        global $wpdb;

        $transients    = [];
        $transientRows = $wpdb->get_results(
            "SELECT option_name, option_value
             FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_wp_statistics%'
             OR option_name LIKE '_transient_wps_%'
             LIMIT 100"
        );

        foreach ($transientRows as $row) {
            $name = str_replace('_transient_', '', $row->option_name);
            $transients[] = [
                'name'  => $name,
                'value' => OptionValueFormatter::format(maybe_unserialize($row->option_value)),
            ];
        }

        return $transients;
    }

    /**
     * Get WP Statistics user meta for a specific user.
     *
     * @param int $userId WordPress user ID.
     * @return array[] Each entry: key, value, exists, isLegacy.
     */
    public function getUserMeta(int $userId): array
    {
        $metaKeys = $this->getUserMetaKeys();
        $userMeta = [];

        foreach ($metaKeys as $key => $isLegacy) {
            $value = get_user_meta($userId, $key, true);
            $userMeta[] = [
                'key'      => $key,
                'value'    => OptionValueFormatter::format($value),
                'exists'   => !empty($value),
                'isLegacy' => $isLegacy,
            ];
        }

        return $userMeta;
    }

    /**
     * Get grouped option names and their group labels.
     *
     * @return array<string, string> Option name => group label.
     */
    private function getGroupedOptionNames(): array
    {
        $groups = [
            'wp_statistics_db'    => 'db',
            'wp_statistics_jobs'  => 'jobs',
            'wp_statistics_cache' => 'cache',
        ];

        /**
         * Filter grouped option names for inspection.
         *
         * Allows premium plugins to add their own option groups.
         *
         * @param array<string, string> $groups Option name => group label.
         */
        return apply_filters('wp_statistics_option_inspection_groups', $groups);
    }

    /**
     * Get user meta keys to inspect.
     *
     * @return array<string, bool> Key => isLegacy flag.
     */
    private function getUserMetaKeys(): array
    {
        $keys = [
            // Modern preferences (v15+)
            'wp_statistics_dashboard_preferences' => false,
            // Legacy (deprecated in v15)
            'wp_statistics'                       => true,
            'wp_statistics_metabox_date_filter'   => true,
            'wp_statistics_user_date_filter'      => true,
        ];

        /**
         * Filter user meta keys for inspection.
         *
         * Allows premium plugins to add their own user meta keys.
         *
         * @param array<string, bool> $keys Key => isLegacy flag.
         */
        return apply_filters('wp_statistics_user_meta_inspection_keys', $keys);
    }
}
