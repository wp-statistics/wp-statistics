<?php

namespace WP_Statistics\Service\Privacy;

use WP_STATISTICS\DB;

/**
 * Privacy Exporter for WP Statistics v15.
 *
 * Exports visitor data for WordPress Privacy API (GDPR compliance).
 * Used in Tools > Export Personal Data.
 *
 * @since 15.0.0
 */
class PrivacyExporter
{
    /**
     * Export visitor data for a user by email address.
     *
     * @param string $emailAddress The user email address.
     * @param int    $page         Page number for pagination.
     * @return array Export response with data.
     */
    public function export($emailAddress, $page = 1)
    {
        $response = [
            'data' => [],
            'done' => true,
        ];

        $user = get_user_by('email', $emailAddress);

        if (!$user) {
            return $response;
        }

        $visitors = $this->getVisitorsByUserId($user->ID);

        foreach ($visitors as $visitor) {
            $response['data'][] = $this->formatVisitorData($visitor);
        }

        return $response;
    }

    /**
     * Get all visitor records for a user ID.
     *
     * @param int $userId WordPress user ID.
     * @return array Visitor records.
     */
    private function getVisitorsByUserId($userId)
    {
        global $wpdb;

        $visitorTable = DB::table('visitor');

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$visitorTable} WHERE `user_id` = %d",
                $userId
            )
        );
    }

    /**
     * Format visitor record for WordPress Privacy API.
     *
     * @param object $visitor Visitor database record.
     * @return array Formatted export data.
     */
    private function formatVisitorData($visitor)
    {
        $userData = [];

        foreach ($visitor as $key => $value) {
            $userData[] = [
                'name'  => $this->getFieldLabel($key),
                'value' => $this->sanitizeValue($key, $value),
            ];
        }

        return [
            'group_id'          => 'wp_statistics_visitors',
            'group_label'       => __('Statistics Data', 'wp-statistics'),
            'group_description' => sprintf(
                __('Visitor data for user ID #%s', 'wp-statistics'),
                $visitor->user_id
            ),
            'item_id'           => "visitor-id-{$visitor->ID}",
            'data'              => $userData,
        ];
    }

    /**
     * Get human-readable label for a field.
     *
     * @param string $key Field key.
     * @return string Field label.
     */
    private function getFieldLabel($key)
    {
        $labels = [
            'ID'         => __('Visitor ID', 'wp-statistics'),
            'ip'         => __('IP Address', 'wp-statistics'),
            'last_visit' => __('Last Visit', 'wp-statistics'),
            'first_view' => __('First View', 'wp-statistics'),
            'hits'       => __('Page Views', 'wp-statistics'),
            'city'       => __('City', 'wp-statistics'),
            'region'     => __('Region', 'wp-statistics'),
            'country'    => __('Country', 'wp-statistics'),
            'continent'  => __('Continent', 'wp-statistics'),
            'user_agent' => __('User Agent', 'wp-statistics'),
            'platform'   => __('Platform', 'wp-statistics'),
            'device'     => __('Device', 'wp-statistics'),
            'browser'    => __('Browser', 'wp-statistics'),
            'model'      => __('Model', 'wp-statistics'),
            'user_id'    => __('User ID', 'wp-statistics'),
            'referred'   => __('Referrer', 'wp-statistics'),
        ];

        return isset($labels[$key]) ? $labels[$key] : $key;
    }

    /**
     * Sanitize value for export display.
     *
     * @param string $key   Field key.
     * @param mixed  $value Field value.
     * @return string Sanitized value.
     */
    private function sanitizeValue($key, $value)
    {
        if (empty($value)) {
            return __('(empty)', 'wp-statistics');
        }

        // Format dates nicely
        if (in_array($key, ['last_visit', 'first_view'])) {
            return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($value));
        }

        return esc_html($value);
    }
}
