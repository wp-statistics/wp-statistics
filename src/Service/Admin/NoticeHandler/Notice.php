<?php

namespace WP_Statistics\Service\Admin\NoticeHandler;

use WP_Statistics\Components\View;
use WP_Statistics\Utils\Request;
use WP_Statistics\Service\Admin\Notice\NoticeManager;
use WP_Statistics\Service\Admin\Notice\NoticeItem;

/**
 * Legacy Notice Handler.
 *
 * @deprecated 15.0.0 Use NoticeManager instead.
 * @see \WP_Statistics\Service\Admin\Notice\NoticeManager
 */
class Notice
{
    private static $adminNotices = array();

    /**
     * List Of Dismissed Notices.
     *
     * @var array
     * @static
     * @access private
     */
    private static $dismissedNotices = [];

    /**
     * Add an admin notice.
     *
     * @deprecated 15.0.0 Use NoticeManager::add() instead.
     *
     * @param string $message       Notice message (supports HTML).
     * @param string $id            Unique notice ID.
     * @param string $class         Notice type: 'info', 'warning', 'error', 'success'.
     * @param bool   $isDismissible Whether the notice can be dismissed.
     * @return void
     */
    public static function addNotice($message, $id, $class = 'info', $isDismissible = true)
    {
        // Trigger deprecation notice in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            _deprecated_function(__METHOD__, '15.0.0', 'NoticeManager::add()');
        }

        // Store in legacy array for backward compatibility with displayNotices()
        $notice = array(
            'message'        => $message,
            'id'             => $id,
            'class'          => $class,
            'is_dismissible' => (bool)$isDismissible,
        );

        self::$adminNotices[$id] = $notice;

        // Also register with the new NoticeManager for React pages
        NoticeManager::add(new NoticeItem([
            'id'          => $id,
            'message'     => $message,
            'type'        => $class,
            'dismissible' => (bool)$isDismissible,
        ]));
    }

    /**
     * Add a temporary flash notice.
     *
     * Flash notices are displayed once and then automatically removed.
     *
     * @deprecated 15.0.0 Use NoticeManager::add() instead.
     *
     * @param string $message       Notice message.
     * @param string $class         Notice type: 'info', 'warning', 'error', 'success'.
     * @param bool   $isDismissible Whether the notice can be dismissed.
     * @return void
     */
    public static function addFlashNotice($message, $class = 'info', $isDismissible = true)
    {
        // Trigger deprecation notice in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            _deprecated_function(__METHOD__, '15.0.0', 'NoticeManager::add()');
        }

        // Add flash notice using transient
        $flashNotices = get_transient('wp_statistics_flash_notices');
        if (!$flashNotices) {
            $flashNotices = array();
        }

        $flashNotices[] = array(
            'message'        => $message,
            'class'          => $class,
            'is_dismissible' => (bool)$isDismissible,
        );

        set_transient('wp_statistics_flash_notices', $flashNotices, 3); // Keep for 3 second
    }

    /**
     * Display all registered notices.
     *
     * @deprecated 15.0.0 Use NoticeManager::renderWordPressNotices() instead.
     * @return void
     */
    public static function displayNotices()
    {
        $dismissedNotices = self::getDismissedNotices();

        foreach (self::$adminNotices as $id => $notice) {
            if (in_array($id, $dismissedNotices, true)) {
                continue; // Skip dismissed notices
            }

            $dismissible = $notice['is_dismissible'] ? ' is-dismissible' : '';
            $dismissUrl  = self::getDismissUrl($id, $notice);

            self::renderNoticeInternal($notice, $dismissible, $dismissUrl);
        }

        // Display flash notices
        $flashNotices = get_transient('wp_statistics_flash_notices');

        if ($flashNotices) {
            foreach ($flashNotices as $flashNotice) {
                $dismissible = $flashNotice['is_dismissible'] ? ' is-dismissible' : '';
                self::renderNoticeInternal($flashNotice, $dismissible, '');
            }

            delete_transient('wp_statistics_flash_notices');
        }
    }

    private static function getDismissUrl($id, $notice)
    {
        if ($notice['is_dismissible']) {
            return add_query_arg(array(
                'action'    => 'wp_statistics_dismiss_notice',
                'notice_id' => $id,
                'nonce'     => wp_create_nonce('wp_statistics_dismiss_notice'),
            ), sanitize_url(wp_unslash($_SERVER['REQUEST_URI'])));
        }

        return '';
    }

    /**
     * Render a notice immediately.
     *
     * @deprecated 15.0.0 Use NoticeManager::add() instead.
     *
     * @param string $message       Notice message.
     * @param string $id            Notice ID.
     * @param string $class         Notice type.
     * @param bool   $isDismissible Whether dismissible.
     * @param string $type          Template type ('simple' or other).
     * @return void
     */
    public static function renderNotice($message, $id, $class = 'info', $isDismissible = false, $type = 'simple')
    {
        $notice = array(
            'message'        => $message,
            'id'             => $id,
            'class'          => $class,
            'is_dismissible' => $isDismissible,
            'type'           => $type,
        );

        $dismissible = $notice['is_dismissible'] ? ' is-dismissible' : '';
        $dismissUrl  = self::getDismissUrl($notice['id'], $notice);

        self::renderNoticeInternal($notice, $dismissible, $dismissUrl, $type);
    }

    /**
     *
     * @param $notice
     * @param $dismissible
     * @param $dismissUrl
     * @return void
     */
    private static function renderNoticeInternal($notice, $dismissible, $dismissUrl, $type = 'simple')
    {
        $args = [
            'notice'      => $notice,
            'dismissible' => $dismissible,
            'dismissUrl'  => $dismissUrl,
        ];
        View::load("components/notices/{$type}-notice", $args);
    }

    public static function handleDismissNotice()
    {
        if (
            Request::compare('action', 'wp_statistics_dismiss_notice') &&
            Request::has('action') && Request::has('notice_id')
        ) {
            check_admin_referer('wp_statistics_dismiss_notice', 'nonce');

            $noticeId         = sanitize_text_field($_GET['notice_id']);
            $dismissedNotices = self::getDismissedNotices();

            if (!in_array($noticeId, $dismissedNotices, true)) {
                $dismissedNotices[] = $noticeId;
                update_option('wp_statistics_dismissed_notices', $dismissedNotices);
                unset(self::$adminNotices[$noticeId]);
            }

            wp_redirect(remove_query_arg(array('nonce', 'action', 'notice_id')));
            exit;
        }
    }

    public static function handleGeneralNotices()
    {
        $generalNotices = new GeneralNotices();
        $generalNotices->init();
    }

    /**
     * Get list of dismissed notice IDs.
     *
     * @deprecated 15.0.0 Use NoticeManager::getDismissedIds() instead.
     * @return array Array of dismissed notice IDs.
     */
    public static function getDismissedNotices()
    {
        if (empty(self::$dismissedNotices)) {
            self::$dismissedNotices = get_option('wp_statistics_dismissed_notices', []);
        }

        return self::$dismissedNotices;
    }

    /**
     * Check if a notice has been dismissed.
     *
     * @deprecated 15.0.0 Use NoticeManager::isDismissed() instead.
     *
     * @param string $noticeId Notice ID to check.
     * @return bool|null True if dismissed, null if invalid.
     */
    public static function isNoticeDismissed($noticeId)
    {
        if (empty($noticeId)) {
            return null;
        }

        $dismissedNotices = self::getDismissedNotices();

        return in_array($noticeId, $dismissedNotices, true);
    }
}
