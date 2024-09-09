<?php

namespace WP_Statistics\Service\Analytics\Referrals;

use Exception;
use WP_STATISTICS\Helper;

class ReferralsDb
{
    public static $dbLink = 'https://cdn.jsdelivr.net/gh/wp-statistics/Referral-Channels@main/source-channels.json';

    /**
     * Downloads source channels database and stores it in the WordPress uploads folder
     *
     * @see https://github.com/wp-statistics/Referral-Channels/blob/main/source-channels.json
     * @return bool
     */
    public static function download()
    {
        try {
            $response = wp_remote_get(self::$dbLink, ['timeout' => 60]);

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $referralsList = wp_remote_retrieve_body($response);

            $fileSaved = file_put_contents(self::getFilePath(), $referralsList);

            if ($fileSaved === false) {
                throw new Exception(esc_html__('Failed to save the file', 'wp-statistics'));
            }

            return true;
        } catch (Exception $e) {
            \WP_Statistics::log(esc_html__('Cannot download referrals database', 'wp-statistics'), 'Error');
            return false;
        }
    }

    public static function getFilePath()
    {
        $fileName = 'source-channels.json';
        return Helper::get_uploads_dir(WP_STATISTICS_UPLOADS_DIR . '/' . $fileName);
    }
}