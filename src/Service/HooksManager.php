<?php
namespace WP_Statistics\Service;

class HooksManager
{
    public function __construct()
    {
        add_filter('kses_allowed_protocols', [$this, 'updateAllowedProtocols']);
    }

    /**
     * Modifies the list of allowed protocols.
     *
     * @param array $protocols The list of allowed protocols.
     */
    public function updateAllowedProtocols($defaultProtocols)
    {
        $customProtocols = [
            'android-app'
        ];

        $customProtocols = apply_filters('wp_statistics_allowed_protocols', $customProtocols);

        return array_merge($defaultProtocols, $customProtocols);
    }
}