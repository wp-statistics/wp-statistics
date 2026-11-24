<?php

namespace WP_Statistics\Service\Admin\Metabox;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_STATISTICS\User;
use WP_STATISTICS\UserOnline;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\Visitors;

/**
 * 14.17: Latest Visitors, Most Active Visitors and Currently Online became one Visitors card.
 *
 * Carries each user's screen choices over to the new card, on the Overview page and the
 * WP Dashboard. Runs once per user, on their first admin page load after the update.
 *
 *  - Order (`meta-box-order_{screen}`): the new card takes the place of the first old card;
 *    the other two are dropped. Without this, MetaboxHelper::getScreenMetaboxes() drops the
 *    unknown ids and the new card would go to the end of its column.
 *  - Hidden (`metaboxhidden_{screen}`): hidden only if all old cards were hidden (Currently
 *    Online counts as hidden while the Online feature is off: it was not on the screen).
 *    When some were hidden, the card opens on the first list the user still had visible.
 *  - Collapsed (`closedpostboxes_{screen}`): collapsed if the first old card was.
 *  - Date range (`wp_statistics_metabox_date_filter`): Most Active's range goes to the new key.
 */
class VisitorsCardMigration
{
    const NEW_ID = 'wp-statistics-visitors-widget';

    /**
     * Old ids in their default order on the screen, with the segment each one became.
     */
    const OLD_IDS = [
        'wp-statistics-top-visitors-widget' => Visitors::SEGMENT_ACTIVE,
        'wp-statistics-recent-widget'       => Visitors::SEGMENT_LATEST,
        'wp-statistics-useronline-widget'   => Visitors::SEGMENT_ONLINE,
    ];

    const DONE_META_KEY = 'wp_statistics_visitors_card_migrated';

    /**
     * Hooked into 'admin_init' (before the screens read the user meta).
     *
     * @return void
     */
    public static function maybeMigrateCurrentUser()
    {
        $userId = get_current_user_id();

        if (!$userId || !User::Access('read')) {
            return;
        }

        self::maybeMigrate($userId);
    }

    /**
     * Migrate one user, once.
     *
     * @param int $userId
     *
     * @return bool True when this call ran the migration.
     */
    public static function maybeMigrate($userId)
    {
        if (get_user_meta($userId, self::DONE_META_KEY, true)) {
            return false;
        }

        self::migrateUser($userId);
        update_user_meta($userId, self::DONE_META_KEY, 1);

        return true;
    }

    /**
     * Screens that had the old cards: screen id => segment context.
     *
     * @return array
     */
    public static function getScreens()
    {
        return [
            Menus::get_action_menu_slug('overview') => 'overview',
            'dashboard'                             => 'dashboard',
        ];
    }

    /**
     * Migrate all screens and the date filter of one user. Safe to run again.
     *
     * @param int       $userId
     * @param bool|null $onlineActive Defaults to UserOnline::active().
     *
     * @return void
     */
    public static function migrateUser($userId, $onlineActive = null)
    {
        $onlineActive = $onlineActive === null ? UserOnline::active() : (bool)$onlineActive;

        foreach (self::getScreens() as $screen => $context) {
            self::migrateScreen($userId, $screen, $context, $onlineActive);
        }

        self::migrateDateFilter($userId);
    }

    /**
     * @param int    $userId
     * @param string $screen  Screen id (meta key suffix).
     * @param string $context Segment context: 'overview' or 'dashboard'.
     * @param bool   $onlineActive
     *
     * @return void
     */
    public static function migrateScreen($userId, $screen, $context, $onlineActive)
    {
        $oldIds = array_keys(self::OLD_IDS);

        // 1. Order: the new id where the first old id was.
        $order    = get_user_meta($userId, "meta-box-order_{$screen}", true);
        $sequence = []; // Old ids in the order the user had them.

        if (is_array($order) && !empty($order)) {
            $placed   = false;
            $newOrder = [];

            foreach ($order as $column => $ids) {
                $list = [];

                foreach (array_filter(explode(',', (string)$ids), 'strlen') as $id) {
                    if (in_array($id, $oldIds, true) || $id === self::NEW_ID) {
                        if (in_array($id, $oldIds, true)) {
                            $sequence[] = $id;
                        }

                        if (!$placed) {
                            $list[] = self::NEW_ID;
                            $placed = true;
                        }

                        continue;
                    }

                    $list[] = $id;
                }

                $newOrder[$column] = implode(',', $list);
            }

            if ($newOrder !== $order) {
                update_user_meta($userId, "meta-box-order_{$screen}", $newOrder);
            }
        }

        // Old cards the saved order did not list keep their default order.
        $sequence = array_values(array_unique(array_merge($sequence, $oldIds)));

        // 2. Hidden: only if every old card was hidden.
        $hidden = get_user_meta($userId, "metaboxhidden_{$screen}", true);

        if (is_array($hidden) && !empty($hidden)) {
            $wasHidden = function ($id) use ($hidden, $onlineActive) {
                return in_array($id, $hidden, true) || ($id === 'wp-statistics-useronline-widget' && !$onlineActive);
            };

            $allHidden  = count(array_filter($oldIds, $wasHidden)) === count($oldIds);
            $anyHidden  = count(array_intersect($oldIds, $hidden)) > 0;
            $newHidden  = array_values(array_diff($hidden, $oldIds));

            if ($allHidden && !in_array(self::NEW_ID, $newHidden, true)) {
                $newHidden[] = self::NEW_ID;
            }

            if ($newHidden !== $hidden) {
                update_user_meta($userId, "metaboxhidden_{$screen}", $newHidden);
            }

            // Some hidden, some not: open on the first list the user still had visible.
            if ($anyHidden && !$allHidden && Visitors::getSavedSegment($userId, $context) === '') {
                foreach ($sequence as $id) {
                    if (!$wasHidden($id)) {
                        Visitors::saveSegment($userId, $context, self::OLD_IDS[$id]);
                        break;
                    }
                }
            }
        }

        // 3. Collapsed: as the first old card was.
        $closed = get_user_meta($userId, "closedpostboxes_{$screen}", true);

        if (is_array($closed) && !empty($closed)) {
            $collapsed = in_array($sequence[0], $closed, true) || in_array(self::NEW_ID, $closed, true);
            $newClosed = array_values(array_diff($closed, $oldIds, [self::NEW_ID]));

            if ($collapsed) {
                $newClosed[] = self::NEW_ID;
            }

            if ($newClosed !== $closed) {
                update_user_meta($userId, "closedpostboxes_{$screen}", $newClosed);
            }
        }
    }

    /**
     * Copy Most Active's saved date range to the new card (if it has none yet).
     *
     * @param int $userId
     *
     * @return void
     */
    public static function migrateDateFilter($userId)
    {
        $filters = get_user_meta($userId, User::$dateFilterMetaKey, true);

        if (!is_array($filters) || empty($filters['wp-statistics-top-visitors-widget']) || !empty($filters[self::NEW_ID])) {
            return;
        }

        $filters[self::NEW_ID] = $filters['wp-statistics-top-visitors-widget'];
        update_user_meta($userId, User::$dateFilterMetaKey, $filters);
    }
}
