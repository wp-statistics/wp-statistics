<?php
namespace WP_Statistics\Service;

use WP_STATISTICS\User;

class HooksManager
{
    public function __construct()
    {
        add_filter('kses_allowed_protocols', [$this, 'updateAllowedProtocols']);

        add_action('wp_statistics_after_welcome_modal', [$this, 'storeWelcomeModalInMeta']);
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

    /**
     * Saves a flag in the user meta indicating that the welcome modal has been displayed.
     */
    public function storeWelcomeModalInMeta()
    {
        User::saveMeta('wp_statistics_welcome_modal_displayed', true);
    }
}