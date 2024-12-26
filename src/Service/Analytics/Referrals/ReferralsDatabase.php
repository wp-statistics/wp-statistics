<?php

namespace WP_Statistics\Service\Analytics\Referrals;

use Exception;

class ReferralsDatabase
{
    public $databaseFileName = 'source-channels.json';

    /**
     * Retrieves the file path for the 'source-channels.json' file in the WordPress uploads directory.
     *
     * @return string The absolute file path for the 'source-channels.json' file.
     */
    protected function getFilePath()
    {
        return __DIR__ . '/DB/' . $this->databaseFileName;
    }

    /**
     * Retrieves the referrals list from the stored file.
     *
     * @return array
     */
    public function getList()
    {
        $result = [];

        try {
            $file = $this->getFilePath();

            $referralsList = file_get_contents($file);
            $referralsList = json_decode($referralsList, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(esc_html__('Failed to parse the referrals database file.', 'wp-statistics'));
            }

            $result = $referralsList;
        } catch (Exception $e) {
            WP_Statistics()->log($e->getMessage(), 'error');

            $result = [];
        }

        return apply_filters('wp_statistics_referrals_list', $result);
    }
}