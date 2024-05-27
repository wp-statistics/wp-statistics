jQuery(document).ready(function ($) {
    $(document).on('click', '.wp-statistics-notice.is-dismissible .notice-dismiss', function () {
        var $this = $(this);
        var noticeId = $this.closest('.wp-statistics-notice').data('notice-id');

        $.ajax({
            url: wps_js.global.admin_url + 'admin-ajax.php',
            method: 'POST',
            data: {
                action: 'wp_statistics_dismiss_notice',
                notice_id: noticeId,
                nonce: wps_js.global.dismiss_notice_nonce
            },
            success: function (response) {
                if (response.success) {
                    $this.closest('.wp-statistics-notice').hide();
                }
            }
        });
    });
});
