<?php

/** @var \WP_Statistics\Service\Admin\LicenseManagement\ProductDecorator $addOn */
if (!defined('ABSPATH') || empty($addOn)) {
    exit;
}

?>
<div class="wps-addon__download__item">
    <div class="wps-addon__download__item--info">
        <div class="wps-addon__download__item--info__img">
            <img src="<?php echo esc_url($addOn->getIcon()); ?>" alt="<?php echo esc_attr($addOn->getName()); ?>">
        </div>
        <div class="wps-addon__download__item--info__text">
            <div class="wps-addon__download__item--info__title">
                <?php echo esc_html($addOn->getName()); ?>
            </div>
            <p class="wps-addon__download__item--info__desc">
                <?php echo wp_kses($addOn->getDescription(), 'data'); ?>
            </p>
        </div>
    </div>
    <div class="wps-addon__download__item--actions">
        <?php if (!$addOn->isInstalled() || $addOn->isUpdateAvailable()) : ?>
            <span class="wps-postbox-addon__status wps-postbox-addon__status--danger "><?php esc_html_e('Failed', 'wp-statistics'); ?></span>
            <a class="wps-postbox-addon__button" href="<?php echo esc_url($retry_link); ?>" title="<?php esc_html_e('Retry', 'wp-statistics'); ?>"><?php esc_html_e('Retry', 'wp-statistics'); ?></a>
        <?php elseif ($addOn->isActivated()) : ?>
            <span class="wps-postbox-addon__status wps-postbox-addon__status--success "><?php esc_html_e('Activated', 'wp-statistics'); ?></span>
        <?php elseif ($addOn->isInstalled()) : ?>
            <a class="wps-postbox-addon__button" href="<?php echo esc_url($active_link); ?>" title="<?php esc_html_e('Active', 'wp-statistics'); ?>"><?php esc_html_e('Active', 'wp-statistics'); ?></a>
        <?php endif; ?>

        <?php if ($addOn->isActivated() || !empty($addOn->getProductUrl()) || !empty($addOn->getChangelogUrl()) || !empty($addOn->getDocumentationUrl())) : ?>
            <div class="wps-addon--actions">
                <span class="wps-addon--actions--show-more js-addon-show-more"></span>
                <ul class="wps-addon--submenus">
                    <?php if ($addOn->isActivated()) : ?>
                        <li><a href="<?php echo esc_url($addOn->getSettingsUrl()); ?>" class="wps-addon--submenu wps-addon--submenu__settings"><?php esc_html_e('Settings', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($addOn->getProductUrl())) : ?>
                        <li><a href="<?php echo esc_url($addOn->getProductUrl()); ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Add-On Detail', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($addOn->getChangelogUrl())) : ?>
                        <li><a href="<?php echo esc_url($addOn->getChangelogUrl()); ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Changelog', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($addOn->getDocumentationUrl())) : ?>
                        <li><a href="<?php echo esc_url($addOn->getDocumentationUrl()); ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Documentation', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>