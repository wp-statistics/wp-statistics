<?php

namespace WP_Statistics\Service\Admin\NoticeHandler;

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Utils\Request;

class Notice
{
    private static $adminNotices = array();

    public static function addNotice($message, $id, $class = 'info', $isDismissible = true)
    {
        $notice = array(
            'message'        => $message,
            'id'             => $id,
            'class'          => $class,
            'is_dismissible' => (bool)$isDismissible,
        );

        self::$adminNotices[$id] = $notice;
    }

    /**
     * @param $message
     * @param $class
     * @param $isDismissible
     * @return void
     */
    public static function addFlashNotice($message, $class = 'info', $isDismissible = true)
    {
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

        set_transient('wp_statistics_flash_notices', $flashNotices, 1); // Keep for 1 second
    }

    public static function displayNotices()
    {
        $dismissedNotices = get_option('wp_statistics_dismissed_notices', array());

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

    public static function renderNotice($message, $id, $class = 'info')
    {
        $notice = array(
            'message'        => $message,
            'id'             => $id,
            'class'          => $class,
            'is_dismissible' => false,
        );

        $dismissible = $notice['is_dismissible'] ? ' is-dismissible' : '';
        $dismissUrl  = self::getDismissUrl($notice['id'], $notice);

        self::renderNoticeInternal($notice, $dismissible, $dismissUrl);
    }

    private static function renderNoticeInternal($notice, $dismissible, $dismissUrl)
    {
        Admin_Template::get_template('notice', [
            'notice'      => $notice,
            'dismissible' => $dismissible,
            'dismissUrl'  => $dismissUrl
        ]);
    }

    public static function handleDismissNotice()
    {
        if (
            Request::compare('action', 'wp_statistics_dismiss_notice') &&
            Request::has('action') && Request::has('notice_id')
        ) {
            check_admin_referer('wp_statistics_dismiss_notice', 'nonce');

            $noticeId         = sanitize_text_field($_GET['notice_id']);
            $dismissedNotices = get_option('wp_statistics_dismissed_notices', array());

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
}
