<?php

namespace WP_Statistics\Service\Admin\NoticeHandler;

class Notice
{
    private static $adminNotices = array();

    public static function addNotice($message, $class = 'info', $is_dismissible = true)
    {
        self::$adminNotices[] = array(
            'id'             => uniqid('', true),
            'message'        => $message,
            'class'          => $class,
            'is_dismissible' => (bool)$is_dismissible,
        );
    }

    public static function displayNotices()
    {
        $dismissedNotices = get_option('wp_statistics_dismissed_notices', array());

        foreach ((array)self::$adminNotices as $notice) :
            if (in_array($notice['id'], $dismissedNotices)) {
                continue;
            }

            $dismissible = $notice['is_dismissible'] ? 'is-dismissible' : '';
            ?>
            <div class="notice notice-<?php echo esc_attr($notice['class']); ?> <?php echo esc_attr($dismissible); ?>" data-notice-id="<?php echo esc_attr($notice['id']); ?>">
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

// todo
/*function wp_statistics_enqueue_scripts()
{
wp_enqueue_script('wp-statistics-notice-handler', plugin_dir_url(__FILE__) . 'assets/js/notice-handler.js', array('jquery'), null, true);
wp_localize_script('wp-statistics-notice-handler', 'wp_statistics_notice_params', array(
'nonce' => wp_create_nonce('wp_statistics_dismiss_notice')
));
}
add_action('admin_enqueue_scripts', 'wp_statistics_enqueue_scripts');*/

/*jQuery(document).ready(function($) {
$(document).on('click', '.notice.is-dismissible', function() {
var $this = $(this);
var noticeId = $this.data('notice-id');

$.ajax({
url: ajaxurl,
method: 'POST',
data: {
action: 'dismiss_wp_statistics_notice',
notice_id: noticeId,
nonce: wp_statistics_notice_params.nonce
},
success: function(response) {
if (response.success) {
$this.fadeOut();
}
}
});
});
});
*/
