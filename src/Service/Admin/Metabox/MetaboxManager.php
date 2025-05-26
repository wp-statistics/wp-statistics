<?php

namespace WP_Statistics\Service\Admin\Metabox;

use WP_STATISTICS\Install;
use WP_Screen;


class MetaboxManager
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerMetaboxes']);
        add_filter('default_hidden_meta_boxes', [$this, 'defaultHiddenMetaBoxes'], 10, 2);
    }

    /**
     * Registers active metaboxes.
     *
     * @return void
     */
    public function registerMetaboxes()
    {
        $metaboxes = MetaboxHelper::getActiveMetaboxes();

        foreach ($metaboxes as $metabox) {
            $metabox->register();
        }
    }

    /**
     * Filters the default hidden meta boxes on the WordPress dashboard screen.
     *
     * This method hides all active metaboxes on the dashboard except for
     * the 'wp-statistics-quickstats-widget'.
     *
     * @param array $hidden Array of IDs of meta boxes to hide by default.
     * @param WP_Screen $screen Current screen object.
     *
     * @return array Modified array of hidden meta box IDs.
     */
    public function defaultHiddenMetaBoxes($hidden, $screen)
    {
        // Only apply hiding logic on fresh installs and on the dashboard screen
        if ($screen->base !== 'dashboard') {
            return $hidden;
        }

        // Get all active metaboxes
        $metaboxes = MetaboxHelper::getActiveMetaboxes();

        foreach ($metaboxes as $metabox) {
            $key = $metabox->getKey();

            // Skip the specific metabox
            if ($key === 'wp-statistics-quickstats-widget') {
                continue;
            }

            // Avoid duplicates
            if (!in_array($key, $hidden, true)) {
                $hidden[] = $key;
            }
        }

        return $hidden;
    }
}