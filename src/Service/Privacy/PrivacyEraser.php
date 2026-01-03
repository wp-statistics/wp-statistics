<?php

namespace WP_Statistics\Service\Privacy;

use WP_Statistics\Service\Database\DatabaseSchema;

/**
 * Privacy Eraser for WP Statistics v15.
 *
 * Erases visitor data for WordPress Privacy API (GDPR compliance).
 * Used in Tools > Erase Personal Data.
 *
 * @since 15.0.0
 */
class PrivacyEraser
{
    /**
     * Erase visitor data for a user by email address.
     *
     * @param string $emailAddress The user email address.
     * @param int    $page         Page number for pagination.
     * @return array Erase response with status.
     */
    public function erase($emailAddress, $page = 1)
    {
        $response = [
            'items_removed'  => false,
            'items_retained' => false,
            'messages'       => [],
            'done'           => true,
        ];

        $user = get_user_by('email', $emailAddress);

        if (!$user) {
            return $response;
        }

        $deleted = $this->deleteVisitorsByUserId($user->ID);

        if ($deleted) {
            $response['items_removed'] = true;
            $response['messages'][]    = sprintf(
                __('Visitor data deleted for %s.', 'wp-statistics'),
                $emailAddress
            );
        }

        return $response;
    }

    /**
     * Delete all visitor records for a user ID.
     *
     * @param int $userId WordPress user ID.
     * @return int|false Number of rows deleted or false on error.
     */
    private function deleteVisitorsByUserId($userId)
    {
        global $wpdb;

        $visitorTable = DatabaseSchema::table('visitor');

        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$visitorTable} WHERE `user_id` = %d",
                $userId
            )
        );
    }
}
