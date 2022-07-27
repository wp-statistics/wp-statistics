<?php

namespace WP_STATISTICS;

class PrivacyErasers
{
    /**
     * Finds and erases visitors' data by email address.
     *
     * @param string $emailAddress The user email address.
     * @param int $page Page.
     *
     * @return array An array of personal data in name value pairs
     *
     * @since 13.2.5
     */
    public static function visitorsDataEraser($emailAddress, $page = 1)
    {
        $response = array(
            'items_removed'  => false,
            'items_retained' => false,
            'messages'       => array(),
            'done'           => true,
        );

        global $wpdb;

        $visitor_table = DB::table('visitor');
        $user          = get_user_by('email', $emailAddress);

        if (!$user) {
            return $response;
        }

        $visitors = $wpdb->query($wpdb->prepare("DELETE FROM {$visitor_table} WHERE `user_id` = %s", $user->ID));

        if ($visitors) {
            $response['messages']      = array(sprintf(__('Visitor data deleted for %s.', 'wp-statistics'), $emailAddress));
            $response['items_removed'] = true;
        }

        return $response;
    }
}