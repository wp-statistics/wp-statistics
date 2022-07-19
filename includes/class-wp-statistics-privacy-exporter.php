<?php

namespace WP_STATISTICS;

class PrivacyExporter
{
    /**
     * Finds and collect visitors' data for exporting by email address.
     *
     * @param string $email_address The user email address.
     * @param int $page
     *
     * @return array An array of personal data in name value pairs
     *
     * @since 13.2.5
     */
    public function visitorsDataExporter($email_address, $page = 1)
    {
        $data_to_export = array();
        $done           = true;

        return array(
            'data' => $data_to_export,
            'done' => $done,
        );
    }
}