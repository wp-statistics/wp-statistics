<?php

namespace WP_Statistics\Service\Admin\Metabox;

use WP_STATISTICS\Install;
use WP_Screen;


class MetaboxManager
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerMetaboxes']);
        add_action('admin_init', [$this, 'hideDashboardMetaboxes']);
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
     * Hooked into 'admin_init'. Runs only once on fresh installs.
     * Excludes the 'wp-statistics-quickstats-widget' from being hidden.
     *
     * @return void
     */
    public function hideDashboardMetaboxes()
    {
        $userId             = get_current_user_id();
        $hiddenMetaboxesKey = 'metaboxhidden_dashboard';
        $metaboxInitFlagKey = 'wp_statistics_metaboxhidden_dashboard_initialized';

        if (get_user_meta($userId, $metaboxInitFlagKey, true)) {
            return;
        }

        if (!Install::isFresh() && get_user_meta($userId, $hiddenMetaboxesKey, true)) {
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

        $existingHidden = get_user_meta($userId, $hiddenMetaboxesKey, true);

        if (!is_array($existingHidden)) {
            update_user_meta($userId, $hiddenMetaboxesKey, $hidden);
        } elseif (array_diff($hidden, $existingHidden)) {
            $mergedHidden = array_unique(array_merge($existingHidden, $hidden));
            update_user_meta($userId, $hiddenMetaboxesKey, $mergedHidden);
        }

        update_user_meta($userId, $metaboxInitFlagKey, 1);
    }
}