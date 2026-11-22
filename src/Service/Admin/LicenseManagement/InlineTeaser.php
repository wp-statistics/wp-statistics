<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Utils\Request;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Small "this needs <add-on>" teasers inside core pages ({@see views/components/lock-sections/inline-teaser.php}).
 * A user can close a teaser for good; the closed ids are kept in that user's meta.
 */
class InlineTeaser
{
    const USER_META    = 'wp_statistics_dismissed_teasers';
    const NONCE_ACTION = 'wp_statistics_dismiss_teaser';
    const AJAX_ACTION  = 'dismiss_teaser';

    /**
     * Registers the dismiss AJAX action (logged-in users only).
     *
     * @return void
     */
    public function register()
    {
        Ajax::register(self::AJAX_ACTION, [$this, 'dismissAction'], false);
    }

    /**
     * Normalizes a teaser id: lowercase letters, digits and dashes.
     *
     * @param string $id
     * @return string
     */
    public static function sanitizeId($id)
    {
        return substr(preg_replace('/[^a-z0-9\-]/', '', strtolower((string)$id)), 0, 64);
    }

    /**
     * Ids the given user closed.
     *
     * @param int|null $userId Defaults to the current user.
     * @return string[]
     */
    public static function getDismissed($userId = null)
    {
        $userId = $userId ?: get_current_user_id();

        if (!$userId) {
            return [];
        }

        $ids = get_user_meta($userId, self::USER_META, true);

        return is_array($ids) ? array_values(array_filter(array_map([self::class, 'sanitizeId'], $ids))) : [];
    }

    /**
     * @param string $id
     * @param int|null $userId
     * @return bool
     */
    public static function isDismissed($id, $userId = null)
    {
        return in_array(self::sanitizeId($id), self::getDismissed($userId), true);
    }

    /**
     * Saves the teaser as closed for the user.
     *
     * @param string $id
     * @param int|null $userId
     * @return bool False for an empty id or a logged-out user.
     */
    public static function dismiss($id, $userId = null)
    {
        $id     = self::sanitizeId($id);
        $userId = $userId ?: get_current_user_id();

        if ($id === '' || !$userId) {
            return false;
        }

        $ids = self::getDismissed($userId);

        if (!in_array($id, $ids, true)) {
            $ids[] = $id;
            update_user_meta($userId, self::USER_META, $ids);
        }

        return true;
    }

    /**
     * AJAX: closes a teaser for the current user. Needs the teaser nonce.
     *
     * @return void
     */
    public function dismissAction()
    {
        check_ajax_referer(self::NONCE_ACTION, 'wps_nonce');

        if (!is_user_logged_in() || !current_user_can('read')) {
            Ajax::error(esc_html__('Unauthorized.', 'wp-statistics'), null, 403);
        }

        if (!self::dismiss(Request::get('teaser_id'))) {
            Ajax::error(esc_html__('Unknown tip.', 'wp-statistics'));
        }

        Ajax::success();
    }
}
