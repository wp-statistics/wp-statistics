<?php

namespace WP_Statistics\Service\Admin\Metabox;

use WP_STATISTICS\Install;
use WP_Screen;


class MetaboxManager
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerMetaboxes']);
        add_action('admin_init', [$this, 'defaultHiddenMetaBoxes']);
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
     * Hides default dashboard metaboxes for the current user on fresh installs.
     *
     * Hooked into 'wp_dashboard_setup'. Runs only once on fresh installs.
     * Excludes the 'wp-statistics-quickstats-widget' from being hidden.
     *
     * @return void
     */
    public function defaultHiddenMetaBoxes()
    {
        if (!Install::isFresh()) {
            return;
        }

        $userId      = get_current_user_id();
        $metaKey     = 'metaboxhidden_dashboard';
        $initFlagKey = 'wps_metaboxhidden_dashboard_initialized';

        if (get_user_meta($userId, $initFlagKey, true)) {
            return;
        }

        $hidden = [];

        $metaboxes = MetaboxHelper::getActiveMetaboxes();

        foreach ($metaboxes as $metabox) {
            $key = $metabox->getKey();

            if ($key === 'wp-statistics-quickstats-widget') {
                continue;
            }

            $hidden[] = $key;
        }

        if (empty($hidden)) {
            return;
        }

        $existingHidden = get_user_meta($userId, $metaKey, true);

        if (!is_array($existingHidden)) {
            update_user_meta($userId, $metaKey, $hidden);
        } elseif (array_diff($hidden, $existingHidden)) {
            $mergedHidden = array_unique(array_merge($existingHidden, $hidden));
            update_user_meta($userId, $metaKey, $mergedHidden);
        }

        update_user_meta($userId, $initFlagKey, 1);
    }
}