<?php

namespace WP_STATISTICS;

class PrivacyExporter
{
    /**
     * Finds and collect visitors' data for exporting by email address.
     *
     * @param string $emailAddress The user email address.
     * @param int $page
     *
     * @return array An array of personal data in name value pairs
     *
     * @since 13.2.5
     */
    public static function visitorsDataExporter($emailAddress, $page = 1)
    {
        $response = array(
            'data' => array(),
            'done' => true,
        );

        global $wpdb;

        $visitor_table = DB::table('visitor');
        $user          = get_user_by('email', $emailAddress);

        if (!$user) {
            return $response;
        }

        $visitors = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$visitor_table} WHERE `user_id` = %s", $user->ID));

        foreach ($visitors as $visitor) {
            $user_data_to_export = array();

            foreach ($visitor as $key => $value) {
                $user_data_to_export[] = array(
                    'name'  => $key,
                    'value' => $value,
                );
            }

            $response['data'][] = array(
                'group_id'          => 'wp_statistics_visitors',
                'group_label'       => __('Statistics Data', 'wp-statistics'),
                'group_description' => sprintf(__('Visitor\'s data for user ID #%s', 'wp-statistics'), $visitor->user_id),
                'item_id'           => "visitor-id-{$visitor->ID}",
                'data'              => $user_data_to_export,
            );
        }

        return $response;
    }
}