<?php

/** @var \WP_Statistics\Service\Admin\LicenseManagement\ProductDecorator $addOn */
if (!defined('ABSPATH') || empty($addOn)) {
    exit;
}

?>
<div class="wps-postbox-addon__item">
    <div>
        <div class="wps-postbox-addon__item--info">
            <div class="wps-postbox-addon__item--info__img">
                <img src="<?php echo esc_url($addOn->getIcon()); ?>" alt="<?php echo esc_html($addOn->getName()); ?>" />
            </div>
            <div class="wps-postbox-addon__item--info__text">
                <div class="wps-postbox-addon__item--info__title">
                    <?php echo esc_html($addOn->getName()); ?>
                    <span class="wps-postbox-addon__version">v<?php echo esc_html($addOn->getVersion()); ?></span>
                    <?php if (!empty($addOn->getLabel())) : ?>
                        <span class="wps-postbox-addon__label wps-postbox-addon__label--<?php echo esc_attr($addOn->getLabelClass()); ?>"><?php echo esc_html($addOn->getLabel()); ?></span>
                    <?php endif; ?>
                </div>
                <p class="wps-postbox-addon__item--info__desc">
                    <?php echo wp_kses($addOn->getShortDescription(), 'data'); ?>
                </p>
            </div>
        </div>
        <div class="wps-postbox-addon__item--actions">
            <span class="wps-postbox-addon__status wps-postbox-addon__status--<?php echo esc_attr($addOn->getStatusClass()); ?> "><?php echo esc_html($addOn->getStatusLabel()); ?></span>

            <a class="wps-postbox-addon__button js-wps-addon-license-button"><?php echo esc_html__('License', 'wp-statistics'); ?></a>

            <?php if ($addOn->isInstalled() && !$addOn->isActivated()) : ?>
                <a class="wps-postbox-addon__button" href="#" data-slug="<?php echo esc_attr($addOn->getSlug()); ?>" title="<?php echo esc_html__('Activate', 'wp-statistics'); ?>"><?php echo esc_html__('Activate', 'wp-statistics'); ?></a>
            <?php endif; ?>

            <div class="wps-addon--actions">
                <span class="wps-addon--actions--show-more js-addon-show-more"></span>
                <ul class="wps-addon--submenus">
                    <?php if ($addOn->isActivated()) : ?>
                        <li><a href="<?php echo esc_url($addOn->getSettingsUrl()); ?>" class="wps-addon--submenu wps-addon--submenu__settings"><?php esc_html_e('Settings', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo esc_url($addOn->getProductUrl()); ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Add-On Detail', 'wp-statistics'); ?></a></li>
                    <?php if (!empty($addOn->getChangelogUrl())) : ?>
                        <li><a href="<?php echo esc_url($addOn->getChangelogUrl()); ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Changelog', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($addOn->getDocumentationUrl())) : ?>
                        <li><a href="<?php echo esc_url($addOn->getDocumentationUrl()); ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Documentation', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="wps-addon__item__license js-wps-addon-license">
        <div class="wps-addon__item__update_license">
            <input type="text" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
            <button><?php echo esc_html__('Update License', 'wp-statistics'); ?></button>
        </div>
        <?php if (isset($alert_text)) : ?>
            <div class="wps-alert wps-alert--<?php echo $alert_class; ?>">
                <span class="icon"></span>
                <div>
                    <p><?php echo $alert_text ?></p>
                    <?php if (isset($alert_link_text)) : ?>
                        <div>
                            <a href="<?php echo $alert_link ?>" class="js-wps-addon-check-box" title="<?php echo $alert_link_text ?>"><?php echo $alert_link_text ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>