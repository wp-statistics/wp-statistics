<?php

namespace WP_STATISTICS;

class PrivacyErasers
{
    /**
     * Finds and erases visitors' data by email address.
     *
     * @param string $email_address The user email address.
     * @param int $page Page.
     *
     * @return array An array of personal data in name value pairs
     *
     * @since 13.2.5
     */
    public function visitorsDataEraser($email_address, $page = 1)
    {
        $response = array(
            'items_removed'  => false,
            'items_retained' => false,
            'messages'       => array(),
            'done'           => true,
        );

        return $response;
    }
}