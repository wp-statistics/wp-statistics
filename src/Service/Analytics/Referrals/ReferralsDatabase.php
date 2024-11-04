<?php

namespace WP_Statistics\Service\Analytics\Referrals;

use Exception;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;

class ReferralsDatabase
{
    public $databaseFileName = 'source-channels.json';

    public function getDownloadUrl()
    {
        return 'https://cdn.jsdelivr.net/gh/wp-statistics/Referral-Channels@main/source-channels.json';
    }

    /**
     * Downloads source channels database and stores it in the WordPress uploads folder
     *
     * @see https://github.com/wp-statistics/Referral-Channels/blob/main/source-channels.json
     * @return bool returns true if the download was successful, false otherwise
     */
    public function download()
    {
        try {
            $response = wp_remote_get($this->getDownloadUrl(), ['timeout' => 60]);

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $referralsList = wp_remote_retrieve_body($response);

            $fileSaved = file_put_contents(self::getFilePath(), $referralsList);

            if ($fileSaved === false) {
                throw new Exception(esc_html__('Failed to save the referrals database file.', 'wp-statistics'));
            }

            $this->updateLastDownloadTimestamp();

            return true;
        } catch (Exception $e) {
            \WP_Statistics::log(esc_html__('Cannot download referrals database.', 'wp-statistics'), 'error');
            return false;
        }
    }

    /**
     * Retrieves the referrals list from the stored file.
     * If the file does not exist, it downloads the list first.
     *
     * @return array The referrals list in array format.
     */
    public function getList()
    {
        try {
            $file = $this->getFilePath();

            if (!file_exists($file) || empty(file_get_contents($file))) {
                $this->download();
            }

            $referralsList = file_get_contents($file);
            $referralsList = json_decode($referralsList, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(esc_html__('Failed to parse the referrals database file.', 'wp-statistics'));
            }

            return $referralsList;
        } catch (Exception $e) {
            \WP_Statistics::log(esc_html__('Cannot download referrals database.', 'wp-statistics'), 'error');
            return [];
        }
    }

    /**
     * Retrieves the file path for the 'source-channels.json' file in the WordPress uploads directory.
     *
     * @return string The absolute file path for the 'source-channels.json' file.
     */
    protected function getFilePath()
    {
        return Helper::get_uploads_dir(WP_STATISTICS_UPLOADS_DIR . '/' . $this->databaseFileName);
    }

    /**
     * Update the last download timestamp.
     */
    public function getLastDownloadTimestamp()
    {
        return Option::get('last_referrals_list_dl');
    }

    /**
     * Update the last download timestamp.
     */
    public function updateLastDownloadTimestamp()
    {
        Option::update('last_referrals_list_dl', time());
    }
}