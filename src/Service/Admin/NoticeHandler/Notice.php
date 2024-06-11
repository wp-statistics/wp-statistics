<?php

namespace WP_Statistics\Service\Admin\NoticeHandler;

use WP_STATISTICS\Admin_Template;

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
            $dismissUrl  = '';

            if ($notice['is_dismissible']) {
                $dismissUrl = add_query_arg(array(
                    'action'    => 'wp_statistics_dismiss_notice',
                    'notice_id' => $id,
                    'nonce'     => wp_create_nonce('wp_statistics_dismiss_notice'),
                ), sanitize_url(wp_unslash($_SERVER['REQUEST_URI'])));
            }

            Admin_Template::get_template('notice', [
                'notice'      => $notice,
                'dismissible' => $dismissible,
                'dismissUrl'  => $dismissUrl
            ]);
        }

        // Display flash notices
        $flashNotices = get_transient('wp_statistics_flash_notices');

        if ($flashNotices) {
            foreach ($flashNotices as $flashNotice) {

                $dismissible = $flashNotice['is_dismissible'] ? ' is-dismissible' : '';

                Admin_Template::get_template('notice', [
                    'notice'      => $flashNotice,
                    'dismissible' => $dismissible,
                    'dismissUrl'  => ''
                ]);
            }

            delete_transient('wp_statistics_flash_notices');
        }
    }

    public static function handleDismissNotice()
    {
        if (
            isset($_GET['action']) && $_GET['action'] === 'wp_statistics_dismiss_notice' &&
            isset($_GET['nonce']) && isset($_GET['notice_id'])
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
