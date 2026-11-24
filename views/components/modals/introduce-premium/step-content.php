<?php
/**
 * One Premium popup step: picture left, headline + 3 benefits + buttons + guarantee right.
 *
 * Args (from step-details.php):
 *  - id_prefix  (string)   Element id prefix.
 *  - slug       (string)   "first-step" or an add-on slug.
 *  - name       (string)   Add-on name (picture alt).
 *  - title      (string)   Headline.
 *  - benefits   (string[]) Three short benefits.
 *  - image      (string)   Picture URL, loaded when the popup opens.
 *  - state      (string)   upgrade | addons-page | activate | active | premium.
 *  - activate_url (string) Plugins screen narrowed to the add-on (state "activate"); empty when the user can't activate plugins.
 *  - notice     (string)   Optional license notice (trusted, may hold <b>/<a>).
 *  - notice_warning (bool) Warning look for the notice.
 *  - is_premium (bool)     Site has Premium.
 *  - addons_url (string)   Add-ons page URL.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$stepId  = $id_prefix . '-step-' . sanitize_html_class($slug);
$titleId = $stepId . '-title';
?>
<section class="wps-premium-popup__step js-wps-premiumModalStep" id="<?php echo esc_attr($stepId); ?>" data-step="<?php echo esc_attr($slug); ?>"<?php echo $slug !== 'first-step' ? ' role="tabpanel"' : ''; ?> aria-labelledby="<?php echo esc_attr($titleId); ?>" hidden>
    <div class="wps-premium-popup__media">
        <img class="wps-premium-popup__image js-wps-premiumModalImage" data-src="<?php echo esc_url($image); ?>" alt="<?php
        /* translators: %s: Add-on name. */
        echo esc_attr(sprintf(__('%s preview', 'wp-statistics'), $name)); ?>" width="1588" height="909">
    </div>
    <div class="wps-premium-popup__body">
        <h2 class="wps-premium-popup__title js-wps-premium-step__title" id="<?php echo esc_attr($titleId); ?>"><?php echo esc_html($title); ?></h2>
        <?php View::load('components/upsell/benefits', ['items' => $benefits, 'class' => 'wps-premium-popup__benefits']); ?>

        <?php if (!empty($notice)) : ?>
            <div class="wps-premium-popup__notice<?php echo !empty($notice_warning) ? ' wps-premium-popup__notice--warning' : ''; ?>">
                <?php echo wp_kses($notice, ['b' => [], 'a' => ['href' => []]]); ?>
            </div>
        <?php endif; ?>

        <?php if ($state === 'upgrade') : ?>
            <?php View::load('components/upsell/actions', ['surface' => 'popup', 'addon' => $slug === 'first-step' ? '' : $slug, 'class' => 'wps-premium-popup__actions']); ?>
            <?php View::load('components/upsell/guarantee'); ?>
        <?php elseif ($state === 'activate') : ?>
            <?php if (!empty($activate_url)) : ?>
                <div class="wps-upsell-actions wps-premium-popup__actions">
                    <a class="wps-upsell-btn wps-upsell-btn--primary" href="<?php echo esc_url($activate_url); ?>"><?php
                        /* translators: %s: Add-on name. */
                        echo esc_html(sprintf(__('Activate %s', 'wp-statistics'), $name)); ?></a>
                </div>
            <?php endif; ?>
        <?php elseif ($state === 'addons-page') : ?>
            <div class="wps-upsell-actions wps-premium-popup__actions">
                <a class="wps-upsell-btn wps-upsell-btn--primary" href="<?php echo esc_url($addons_url); ?>"><?php esc_html_e('Go to Add-ons Page', 'wp-statistics'); ?></a>
            </div>
        <?php else : ?>
            <p class="wps-premium-popup__status">
                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" fill="currentColor" opacity=".14"/><path d="m7.8 12.4 2.8 2.8 5.6-5.9" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <?php echo $state === 'premium' ? esc_html__('Premium Activated', 'wp-statistics') : esc_html__('Add-on Activated', 'wp-statistics'); ?>
            </p>
        <?php endif; ?>
    </div>
</section>
