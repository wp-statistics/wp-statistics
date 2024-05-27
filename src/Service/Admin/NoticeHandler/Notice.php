<?php

namespace WP_Statistics\Service\Admin\NoticeHandler;

class Notice
{
    private static $adminNotices = array();

    public static function addNotice($message, $id, $class = 'info', $isDismissible = true)
    {
        self::$adminNotices[] = array(
            'message'        => $message,
            'id'             => $id,
            'class'          => $class,
            'is_dismissible' => (bool)$isDismissible,
        );
    }

    public static function displayNotices()
    {
        $dismissedNotices = get_option('wp_statistics_dismissed_notices', array());

        foreach ((array)self::$adminNotices as $notice) :
            if (in_array($notice['id'], $dismissedNotices)) {
                continue;
            }

            $dismissible = $notice['is_dismissible'] ? ' is-dismissible' : '';
            ?>
            <div class="notice notice-<?php echo esc_attr($notice['class']); ?> wp-statistics-notice<?php echo esc_attr($dismissible); ?>" data-notice-id="<?php echo esc_attr($notice['id']); ?>">
                <p><?php echo wp_kses_post($notice['message']); ?></p>
            </div>
        <?php
        endforeach;
    }

    public static function dismissNotice()
    {
        check_ajax_referer('wp_statistics_dismiss_notice', 'nonce');

        if (isset($_POST['notice_id'])) {
            $noticeId         = sanitize_text_field($_POST['notice_id']);
            $dismissedNotices = get_option('wp_statistics_dismissed_notices', array());

            if (!in_array($noticeId, $dismissedNotices)) {
                $dismissedNotices[] = $noticeId;
                update_option('wp_statistics_dismissed_notices', $dismissedNotices);
            }

            wp_send_json_success();
        }

        wp_send_json_error();
    }
}