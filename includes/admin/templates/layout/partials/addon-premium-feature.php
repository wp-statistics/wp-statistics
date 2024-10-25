<?php

use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
$isPremium          = LicenseHelper::isPremiumLicenseAvailable() ? true : false;

?>

<div class="wps-premium-feature wps-premium-feature--premium-user">
    <div class="wps-premium-feature__head">
        <?php if ($isPremium): ?>
            <h1>
                <?php esc_html_e('You Have the Premium Version!', 'wp-statistics')?>
             </h1>
        <?php else:?>
            <h1>
                <?php esc_html_e('Unlock Premium Features with', 'wp-statistics')?>
                <span><?php echo esc_html($addon_title); ?></span>
            </h1>
            <?php if (!empty($addon_description)): ?>
                <p><?php echo esc_html($addon_description); ?></p>
            <?php endif; ?>
        <?php endif ?>
    </div>

    <?php if ($isPremium): ?>
        <div class="wps-premium-feature__info wps-premium-feature__info--premium-user">
            <?php echo sprintf(esc_attr__('Your WP Statistics Premium includes the %s add-on, but it\'s not installed yet. Visit the Add-Ons page to install and activate it, unlocking its full features.', 'wp-statistics'),esc_html($addon_title)); ?>
        </div>
        <a class="button button-primary button-primary-addons"  href="<?php echo esc_url(admin_url('admin.php?page=wps_plugins_page')) ?>" ><?php esc_html_e('Go to Add-Ons Page', 'wp-statistics') ?></a>

    <?php else:?>
        <?php if (!empty($addon_features)): ?>
            <div class="wps-premium-feature__items <?php echo esc_html($addon_title); ?>">
                <?php foreach ($addon_features as $feature): ?>
                    <div class="wps-premium-feature__item"><?php echo esc_html($feature); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($addon_info)): ?>
        <div class="wps-premium-feature__addon_info">
            <?php echo esc_html($addon_info); ?>
            <?php if (!empty($addon_documentation_title) && !empty($addon_documentation_slug)): ?>
                <a href="<?php echo esc_url($addon_documentation_slug) ?>" target="_blank" title="<?php echo esc_html($addon_documentation_title); ?>"><?php echo esc_html($addon_documentation_title); ?></a>.
            <?php endif; ?>
        </div>
        <?php endif;
        ?>

        <div class="wps-premium-feature__info">
            <?php echo esc_html_e('To unlock every premium feature in WP Statistics, upgrade to Premium.', 'wp-statistics'); ?>
        </div>
        <div class="wps-premium-feature__buttons">
            <a class="button button-primary" target="_blank" href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings'); ?>" ><?php esc_html_e('Unlock Everything with Premium', 'wp-statistics') ?></a>
            <a class="wps-show-premium-modal button  js-wps-openPremiumModal" data-target="<?php echo esc_html($addon_modal_target)?>" data-name="<?php echo esc_html($addon_title)?>"  href="<?php echo esc_url($addon_slug) ?>"><?php esc_html_e('Learn More', 'wp-statistics') ?></a>
        </div>
    <?php endif ?>


</div>

