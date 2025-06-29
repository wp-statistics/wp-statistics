<?php

/** @var WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginDecorator $addOn */
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
                <?php echo wp_kses($addOn->getShortDescription(), 'data'); ?>
            </p>
        </div>
    </div>
    <div class="wps-addon__download__item--actions">
        <?php if (in_array($addOn->getSlug(), $selectedAddOns) && (!$addOn->isInstalled() || $addOn->isUpdateAvailable())) : ?>
            <span class="wps-postbox-addon__status wps-postbox-addon__status--danger "><?php esc_html_e('Failed', 'wp-statistics'); ?></span>
        <?php elseif ($addOn->isActivated()) : ?>
            <span class="wps-postbox-addon__status wps-postbox-addon__status--success "><?php esc_html_e('Activated', 'wp-statistics'); ?></span>
        <?php endif; ?>

        <div class="wps-postbox-addon__buttons">
            <?php if (in_array($addOn->getSlug(), $selectedAddOns) && (!$addOn->isInstalled() || $addOn->isUpdateAvailable())) : ?>
                <a class="wps-postbox-addon__button button-retry-addon-download js-addon-retry-btn" data-slug="<?php echo esc_attr($addOn->getSlug()); ?>" aria-label="<?php esc_html_e('Retry', 'wp-statistics'); ?>"><?php esc_html_e('Retry', 'wp-statistics'); ?></a>
            <?php elseif ($addOn->isInstalled() && !$addOn->isActivated() ) : ?>
                <a class="wps-postbox-addon__button button-activate-addon js-addon-active-plugin-btn" data-slug="<?php echo esc_attr($addOn->getSlug()); ?>" aria-label="<?php esc_html_e('Active', 'wp-statistics'); ?>"><?php esc_html_e('Active', 'wp-statistics'); ?></a>
            <?php endif; ?>
        </div>


        <div class="wps-addon--actions <?php echo !$addOn->isInstalled() ? 'wps-hide' : ''; ?>">
            <span class="wps-addon--actions--show-more js-addon-show-more"></span>
            <ul class="wps-addon--submenus">
                <?php if ($addOn->isActivated()) : ?>
                    <li><a target="_blank" href="<?php echo esc_url($addOn->getSettingsUrl()); ?>" class="wps-addon--submenu wps-addon--submenu__settings"><?php esc_html_e('Settings', 'wp-statistics'); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($addOn->getProductUrl())) : ?>
                    <li><a href="<?php echo esc_url($addOn->getProductUrl()); ?>/?utm_source=wp-statistics&utm_medium=link&utm_campaign=<?php echo rawurlencode($addOn->getUtmCampaign()); ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Add-on Details', 'wp-statistics'); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($addOn->getChangelogUrl())) : ?>
                    <li><a href="<?php echo esc_url($addOn->getChangelogUrl()); ?>&?utm_source=wp-statistics&utm_medium=link&utm_campaign=<?php echo rawurlencode($addOn->getUtmCampaign()); ?>&releases=<?php echo rawurlencode($addOn->getUtmCampaign()); ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Changelog', 'wp-statistics'); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($addOn->getDocumentationUrl())) : ?>
                    <li><a href="<?php echo esc_url($addOn->getDocumentationUrl()); ?>/?utm_source=wp-statistics&utm_medium=link&utm_campaign=<?php echo rawurlencode($addOn->getUtmCampaign()); ?>" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Documentation', 'wp-statistics'); ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>