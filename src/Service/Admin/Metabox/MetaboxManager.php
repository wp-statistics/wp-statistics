<?php

namespace WP_Statistics\Service\Admin\Metabox;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Screen;
use WP_Statistics\Core\CoreFactory;
use WP_Statistics\Service\Admin\Posts\EditorPanel;
use WP_STATISTICS\User;

class MetaboxManager
{
    public function __construct()
    {
        // 14.17: the three visitor widgets became one Visitors card; carry each user's order, hidden and collapsed state over (once per user).
        add_action('admin_init', [VisitorsCardMigration::class, 'maybeMigrateCurrentUser'], 5);
        add_action('admin_init', [$this, 'registerMetaboxes']);
        add_action('admin_init', [$this, 'hideDashboardMetaboxes']);

        // Block editor sidebar panel (replaces the "Statistics Summary" meta box there)
        new EditorPanel();
    }

    /**
     * Registers active metaboxes.
     *
     * @return void
     */
    public function registerMetaboxes()
    {
        if (!User::Access('read')) {
            return;
        }

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
        if (!User::Access('read')) {
            return;
        }

        $userId             = get_current_user_id();
        $hiddenMetaboxesKey = 'metaboxhidden_dashboard';
        $metaboxInitFlagKey = 'wp_statistics_metaboxhidden_dashboard_initialized';

        if (get_user_meta($userId, $metaboxInitFlagKey, true)) {
            return;
        }

        if (!CoreFactory::isFresh() && get_user_meta($userId, $hiddenMetaboxesKey, true)) {
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
