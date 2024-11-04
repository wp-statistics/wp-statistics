<?php

namespace WP_Statistics\Service\Analytics\DeviceDetection;

class UserAgent
{
    /**
     * Get HTTP User Agent
     *
     * @return string
     */
    public static function getHttpUserAgent()
    {
        return apply_filters('wp_statistics_user_http_agent', (isset($_SERVER['HTTP_USER_AGENT']) ? wp_unslash($_SERVER['HTTP_USER_AGENT']) : ''));
    }

    /**
     * Get parsed User Agent using UserAgentService
     *
     * @return UserAgentService
     */
    public static function getUserAgent()
    {
        return new UserAgentService();
    }
}
