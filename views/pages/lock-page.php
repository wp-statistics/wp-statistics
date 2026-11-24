<?php
/**
 * Lock page: a report that needs an add-on. Split layout, text on the left (one headline, three benefits,
 * "Upgrade to Premium" (pricing page) / "Learn more" (add-on page), both new tab and never the popup, then one
 * line: guarantee · "Included in Premium") and the preview slider on the right. Stacks on narrow screens.
 *
 * Args:
 *  - page_title  (string)   Required. One short headline.
 *  - addon_name  (string)   Required. Add-on name.
 *  - addon_slug  (string)   Required. Add-on slug.
 *  - campaign    (string)   Kept for callers; utm_campaign comes from addon_slug ({@see UpsellLink}).
 *  - benefits    (string[]) Three short benefits.
 *  - description (string)   Optional one-line fallback when there are no benefits.
 *  - images      (string[]) Preview pictures in assets/images/locked/.
 *
 * Notices that stay above the headline: "installed but turned off" and "included in your license".
 * White label (or a hidden Add-ons menu): a neutral line, no upsell.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\User;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$pluginHandler  = new PluginHandler();
$hasLicense     = LicenseHelper::isPluginLicenseValid($addon_slug) ? true : false;
$isActive       = $pluginHandler->isPluginActive($addon_slug);
$isInstalled    = $pluginHandler->isPluginInstalled($addon_slug);
$situation      = LicenseHelper::getAddonSituation($addon_slug, $pluginHandler);
$canManage      = User::Access('manage');
$benefits       = !empty($benefits) && is_array($benefits) ? $benefits : [];
$images         = !empty($images) && is_array($images) ? $images : [];
$showUpsell     = !$hasLicense && !$isActive && !$isInstalled && $canManage;

// White label (or a hidden Add-ons menu): no upsell, only a neutral line, like the inline teasers.
if ($situation === LicenseHelper::SITUATION_HIDDEN) : ?>
    <div class="wps-wrap__main wps-lock-page__main">
        <div class="wps-lock-page wps-lock-page--container wps-lock-page--unavailable">
            <div class="wps-lock-page__head"><?php esc_html_e('This feature is not available', 'wp-statistics'); ?></div>
            <div class="wps-lock-page__description"><?php esc_html_e('Ask a site admin if you need this report.', 'wp-statistics'); ?></div>
        </div>
    </div>
    <?php
    return;
endif;
?>

<div class="wps-wrap__main wps-lock-page__main">
    <div class="wps-lock-page wps-lock-page--container wps-lock-page--split">
        <?php if ($isInstalled && !$isActive) : ?>
            <div class="wps-notice-settings wps-notice-settings--warning">
                <div>
                    <div class="wps-notice-settings__desc">
                        <?php
                        /* translators: %s: Add-on name. */
                        echo esc_html(sprintf(__('The %s add-on isn\'t activated.', 'wp-statistics'), $addon_name));
                        if ($canManage && current_user_can('activate_plugins')) :
                            ?>
                            <a href="<?php echo esc_url(LicenseHelper::getAddonPluginsPageUrl($addon_slug)); ?>"><?php
                                /* translators: %s: Add-on name. */
                                echo esc_html(sprintf(__('Activate %s', 'wp-statistics'), $addon_name)); ?></a>
                        <?php elseif (!$canManage) : ?>
                            <?php /* translators: %s: Add-on name. */ echo esc_html(sprintf(__('Ask a site admin to turn on %s.', 'wp-statistics'), $addon_name)); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($hasLicense && !$isActive && !$isInstalled && $canManage) : ?>
            <div class="wps-premium-feature wps-premium-feature--premium-user">
                <?php
                View::load("components/lock-sections/setting-active-licensed-addon", ['addon_title' => $addon_name]);
                ?>
            </div>
        <?php endif ?>

        <div class="wps-lock-page__split">
            <div class="wps-lock-page__text">
                <h1 class="wps-lock-page__head"><?php echo esc_html($page_title ?? ''); ?></h1>

                <?php if (!empty($benefits)) : ?>
                    <?php View::load('components/upsell/benefits', ['items' => $benefits, 'class' => 'wps-lock-page__benefits']); ?>
                <?php elseif (!empty($description)) : ?>
                    <p class="wps-lock-page__description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>

                <?php if ($showUpsell) : ?>
                    <?php View::load('components/upsell/actions', ['surface' => 'lock-page', 'addon' => $addon_slug, 'class' => 'wps-lock-page__actions']); ?>
                    <?php View::load('components/upsell/guarantee', ['extra' => __('Included in Premium with all 8 add-ons', 'wp-statistics'), 'class' => 'wps-lock-page__included']); ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($images)) : ?>
                <div class="wps-lock-page__slider wps-lock-page__media">
                    <div class="wps-slider">
                        <?php foreach ($images as $index => $image) : ?>
                            <div class="wps-slider__slide">
                                <img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/locked/' . $image); ?>" alt="<?php
                                /* translators: 1: Add-on name, 2: picture number. */
                                echo esc_attr(sprintf(__('%1$s preview, picture %2$d', 'wp-statistics'), $addon_name, $index + 1)); ?>" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="wps-slider__dots" data-dot-label="<?php
                    /* translators: %d: picture number. Keep the %d. */
                    esc_attr_e('Show picture %d', 'wp-statistics'); ?>"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
