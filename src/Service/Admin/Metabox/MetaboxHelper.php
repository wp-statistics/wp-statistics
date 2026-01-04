<?php

namespace WP_Statistics\Service\Admin\Metabox;

use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\PostSummary;

/**
 * Helper class for managing metaboxes.
 *
 * In v15, most dashboard metaboxes have been replaced by the React SPA.
 * Only PostSummary (post editor metabox) remains as a core metabox.
 *
 * Add-ons can register their own metaboxes using the 'wp_statistics_metabox_list' filter.
 *
 * @since 15.0.0
 */
class MetaboxHelper
{
    /**
     * Core metaboxes list.
     *
     * @var array
     */
    public static $metaboxes = [
        PostSummary::class,
    ];

    /**
     * Retrieves a list of all metaboxes.
     *
     * @return BaseMetabox[]|string[]
     */
    public static function getMetaboxes()
    {
        return apply_filters('wp_statistics_metabox_list', self::$metaboxes);
    }

    /**
     * Get list of active metaboxes.
     *
     * @return BaseMetabox[]
     */
    public static function getActiveMetaboxes()
    {
        $activeMetaboxes = [];
        $metaboxes       = self::getMetaboxes();

        foreach ($metaboxes as $metabox) {
            if (!class_exists($metabox)) {
                continue;
            }

            $metabox = new $metabox();

            if (!$metabox->isActive()) {
                continue;
            }

            $activeMetaboxes[$metabox->getKey()] = $metabox;
        }

        return $activeMetaboxes;
    }

    /**
     * Returns a list of metaboxes which are specific to the current screen grouped by context.
     *
     * @return array
     */
    public static function getScreenMetaboxes()
    {
        $metaboxes = [];

        // Return early if there is no current screen
        if (!function_exists('get_current_screen')) {
            return $metaboxes;
        }

        $currentScreen = get_current_screen()->id;

        // Get screen metaboxes that belong to the current screen
        $screenMetaboxes = [];
        foreach (self::getActiveMetaboxes() as $metabox) {
            if (in_array($currentScreen, $metabox->getScreen()) && !$metabox->isStatic()) {
                $screenMetaboxes[$metabox->getKey()] = $metabox;
            }
        }

        // Get the stored metaboxes for current user
        $userMetaboxes = get_user_meta(get_current_user_id(), "meta-box-order_$currentScreen", true);

        // If there are stored metaboxes, use them
        if (!empty($userMetaboxes)) {
            $contexts = array_keys($userMetaboxes);

            foreach ($contexts as $context) {
                // Get the metaboxes for the current context
                $contextMetaboxes = explode(',', $userMetaboxes[$context]);

                // Remove any non wp-statistics metaboxes
                $contextMetaboxes = array_filter($contextMetaboxes, function ($metabox) use ($screenMetaboxes) {
                    return strpos($metabox, 'wp-statistics') !== false && isset($screenMetaboxes[$metabox]);
                });

                if (!empty($contextMetaboxes)) {
                    $metaboxes[$context] = array_values($contextMetaboxes);
                }
            }
        }

        // If there are no stored metaboxes, use the default
        foreach ($screenMetaboxes as $metabox) {
            $key     = $metabox->getKey();
            $context = $metabox->getContext();

            if (!isset($metaboxes[$context]) || !in_array($key, $metaboxes[$context])) {
                $metaboxes[$context][] = $metabox->getKey();
            }
        }

        return $metaboxes;
    }
}
