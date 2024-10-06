<div id="referral-filter-popup" dir="<?php echo(is_rtl() ? 'rtl' : 'ltr') ?>" style="display:none;">
    <form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="get" id="wps-referrals-filter-form">
        <input type="hidden" name="page" value="<?php echo esc_attr($pageName); ?>">
        <div id="wps-referral-filter-div">
            <!-- DO JS -->
        </div>
    </form>
</div>
