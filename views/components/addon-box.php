<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/** @var WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginDecorator $addOn */
if (empty($addOn)) {
    return;
}

?>
<?php
$statusClass = $addOn->getStatusClass();
$statusIcons = [
    'success' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><circle cx="12" cy="12" r="9.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m8 12.4 2.8 2.8L16 9.6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'danger'  => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><circle cx="12" cy="12" r="9.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m9 9 6 6m0-6-6 6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>',
    'primary' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><circle cx="12" cy="12" r="9.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 7.5v5l3 1.8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'disable' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><circle cx="12" cy="12" r="9.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 11v5.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/><circle cx="12" cy="7.6" r="1.2" fill="currentColor"/></svg>',
];
?>
<div class="wps-postbox-addon__item">
    <div>
        <div class="wps-postbox-addon__item--info">
            <div class="wps-postbox-addon__item--info__img">
                 <img src="<?php echo esc_url($addOn->getIcon()); ?>" alt="<?php echo esc_html($addOn->getName()); ?>" />
             </div>
            <div class="wps-postbox-addon__item--info__text">
                <div class="wps-postbox-addon__item--info__title">
                    <a href="<?php echo esc_url($addOn->getProductUrl()); ?>?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon" target="_blank">
                        <?php echo esc_html($addOn->getName())?><span class="wps-postbox-addon__version">v<?php echo esc_html($addOn->getVersion())?></span>
                    </a>
                    <?php if (!empty($addOn->getLabel())) : ?>
                        <span class="wps-postbox-addon__label wps-postbox-addon__label--<?php echo esc_attr($addOn->getLabelClass()); ?>"><?php echo esc_html($addOn->getLabel()); ?></span>
                    <?php endif; ?>

                    <span class="wps-postbox-addon__status wps-postbox-addon__status--<?php echo esc_attr($statusClass); ?>"><?php echo isset($statusIcons[$statusClass]) ? $statusIcons[$statusClass] : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed markup above ?><?php echo esc_html($addOn->getStatusLabel()); ?></span>
                    <?php if ($addOn->isLicenseValid() && $addOn->isUpdateAvailable()) : ?>
                        <span class="wps-postbox-addon__label wps-postbox-addon__label--updated"><?php esc_html_e('Update Available', 'wp-statistics'); ?></span>
                    <?php endif; ?>
                </div>
                <p class="wps-postbox-addon__item--info__desc">
                    <?php echo wp_kses($addOn->getShortDescription(), 'data'); ?>
                </p>
            </div>
        </div>
        <div class="wps-postbox-addon__item--actions">
            <div class="wps-postbox-addon__buttons">
                <?php if ($addOn->isInstalled() && !$addOn->isActivated()) : ?>
                    <a class="wps-postbox-addon__button js-addon-active-plugin-btn" data-slug="<?php echo esc_attr($addOn->getSlug()); ?>" aria-label="<?php esc_html_e('Active', 'wp-statistics'); ?>"><?php esc_html_e('Active', 'wp-statistics'); ?></a>
                <?php endif; ?>
                <?php if ($addOn->isInstalled()) : ?>
                    <a class="wps-postbox-addon__button js-wps-addon-license-button"><?php esc_html_e('License', 'wp-statistics'); ?></a>
                <?php endif; ?>
            </div>
            <div class="wps-addon--actions">
                <span class="wps-addon--actions--show-more js-addon-show-more"></span>
                <ul class="wps-addon--submenus">
                    <?php if ($addOn->isActivated()) : ?>
                        <li><a href="<?php echo esc_url($addOn->getSettingsUrl()); ?>" class="wps-addon--submenu wps-addon--submenu__settings" target="_blank"><span><?php esc_html_e('Settings', 'wp-statistics'); ?></span></a></li>
                        <li><span class="wps-separator"></span></li>
                    <?php endif; ?>
                    <li><a href="<?php echo esc_url($addOn->getProductUrl()); ?>?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Add-on Details', 'wp-statistics'); ?></a></li>
                    <?php if (!empty($addOn->getChangelogUrl())) : ?>
                        <li><a href="<?php echo esc_url($addOn->getChangelogUrl()); ?>&utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Changelog', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($addOn->getDocumentationUrl())) : ?>
                        <li><a href="<?php echo esc_url($addOn->getDocumentationUrl()); ?>?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon" class="wps-addon--submenu" target="_blank"><?php esc_html_e('Documentation', 'wp-statistics'); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="wps-addon__item__license js-wps-addon-license">
        <div class="wps-addon__item__update_license">
            <input aria-label="<?php esc_html_e('License', 'wp-statistics'); ?>" id="<?php echo esc_attr($addOn->getSlug()) ?>-input" data-addon-slug="<?php echo esc_attr($addOn->getSlug()) ?>" type="text" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" value="<?php echo esc_attr($addOn->getLicenseKey()) ?>">
            <button><?php esc_html_e('Update License', 'wp-statistics'); ?></button>
        </div>
        <?php if (isset($alert_text)) : ?>
            <div class="wps-alert wps-alert--<?php echo esc_attr($alert_class); ?>">
                <span class="icon"></span>
                <div>
                    <p><?php echo esc_html($alert_text); ?></p>
                    <?php if (isset($alert_link_text)) : ?>
                        <div>
                            <a href="<?php echo esc_url($alert_link); ?>" class="js-wps-addon-check-box" title="<?php echo esc_attr($alert_link_text); ?>"><?php echo esc_html($alert_link_text); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
     </div>

</div>