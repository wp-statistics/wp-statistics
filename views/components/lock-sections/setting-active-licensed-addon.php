<div class="wps-premium-feature__head">
    <h1>
        <?php esc_html_e('You Have Access to This Add-On!', 'wp-statistics')?>
    </h1>
</div>
<div class="wps-premium-feature__info wps-premium-feature__info--premium-user">
     <?php echo sprintf(esc_attr__('Your license includes the %s, but it’s not installed yet. Go to the Add-Ons page to install and activate it, so you can start using all its features.', 'wp-statistics'),esc_html($addon_title)); ?>
</div>
<a class="button button-primary button-primary-addons"  href="<?php echo esc_url(admin_url('admin.php?page=wps_plugins_page')) ?>" ><?php esc_html_e('Go to Add-Ons Page', 'wp-statistics') ?></a>
