<?php
/**
 * Premium popup body: close button, add-on switcher ({@see switcher.php}, kept separate so it can be swapped)
 * and one step per add-on plus the intro step. Each step: picture left, headline + 3 short benefits +
 * buttons + guarantee right. Copy: {@see AddonCatalog}.
 *
 * Args:
 *  - id_prefix (string) Prefix for element ids, so two popups on one page don't clash. Default "wps-premium".
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$idPrefix      = !empty($id_prefix) ? sanitize_html_class($id_prefix) : 'wps-premium';
$pluginHandler = new PluginHandler();
$hasAnyLicense = LicenseHelper::isValidLicenseAvailable();
$isPremium     = LicenseHelper::isPremiumLicenseAvailable();
$addonsPageUrl = admin_url('admin.php?page=wps_plugins_page');

// Intro step, by license.
if ($isPremium) {
    $intro = [
        'title'    => __('You\'re all set with WP Statistics Premium', 'wp-statistics'),
        'benefits' => [
            __('Every add-on is included in your plan', 'wp-statistics'),
            __('Thanks for supporting WP Statistics', 'wp-statistics'),
            __('Pick an add-on above to see what it does', 'wp-statistics'),
        ],
        'state'    => 'premium',
    ];
} elseif ($hasAnyLicense) {
    $intro = [
        'title'    => __('You already have some Premium add-ons', 'wp-statistics'),
        'benefits' => [
            __('Upgrade to get all 8 add-ons in one plan', 'wp-statistics'),
            __('Clicks, campaigns, live visitors and more', 'wp-statistics'),
            __('Pick an add-on above to see what it does', 'wp-statistics'),
        ],
        'state'    => 'upgrade',
    ];
} else {
    $intro = [
        'title'    => __('Unlock WP Statistics Premium', 'wp-statistics'),
        'benefits' => [
            __('All 8 add-ons in one plan', 'wp-statistics'),
            __('Clicks, campaigns, live visitors and more', 'wp-statistics'),
            __('Pick an add-on above to see what it does', 'wp-statistics'),
        ],
        'state'    => 'upgrade',
    ];
}

$steps = [
    array_merge($intro, [
        'slug'      => 'first-step',
        'name'      => __('WP Statistics Premium', 'wp-statistics'),
        'image'     => WP_STATISTICS_URL . 'assets/images/premium-widget.svg',
        'notice'    => '',
    ]),
];

$switcher = [];

foreach (AddonCatalog::SLUGS as $slug) {
    $copy        = AddonCatalog::get($slug);
    $isActive    = $pluginHandler->isPluginActive($slug);
    $isInstalled = $pluginHandler->isPluginInstalled($slug);
    $hasLicense  = LicenseHelper::isPluginLicenseValid($slug);
    $notice      = '';
    $badge       = '';
    $activateUrl = '';

    // Same reading as the inline teasers and the lock page, so every surface agrees on "installed but turned off".
    $situation = LicenseHelper::getAddonSituation($slug, $pluginHandler);

    if ($situation === LicenseHelper::SITUATION_INACTIVE) {
        $state  = 'activate';
        $badge  = __('Turned off', 'wp-statistics');
        $notice = sprintf(
            /* translators: %s: Add-on name. */
            __('The %s add-on isn\'t activated.', 'wp-statistics'),
            esc_html($copy['name'])
        );

        if (current_user_can('activate_plugins')) {
            $activateUrl = LicenseHelper::getAddonPluginsPageUrl($slug);
        } else {
            $notice .= ' ' . esc_html(sprintf(
                /* translators: %s: Add-on name. */
                __('Ask a site admin to turn on %s.', 'wp-statistics'),
                $copy['name']
            ));
        }
    } elseif ($hasLicense && $isActive) {
        $state = 'active';
    } elseif (($hasLicense && !$isActive) || (!$hasLicense && $isInstalled)) {
        $state = 'addons-page';
    } else {
        $state = 'upgrade';
    }

    if ($state === 'activate') {
        // Notice and badge set above.
    } elseif ($hasLicense && !$isActive) {
        $notice = sprintf(
            /* translators: %s: Add-on name. */
            __('Your license includes the %s, but it’s not installed yet. Go to the Add-ons page to install and activate it, so you can start using all its features.', 'wp-statistics'),
            esc_html($copy['name'])
        );
        $badge = $isInstalled ? __('Not activated', 'wp-statistics') : __('Not Installed', 'wp-statistics');
    } elseif (!$hasLicense && $isInstalled) {
        $notice = sprintf(
            /* translators: %s: Add-ons page URL. */
            __('This add-on has <b>no active license</b>, so it won\'t get updates or security fixes. <a href="%s">Activate your license</a>.', 'wp-statistics'),
            esc_url($addonsPageUrl)
        );
    } elseif ($state === 'active') {
        $badge = __('Active', 'wp-statistics');
    }

    $steps[] = [
        'slug'      => $slug,
        'name'      => $copy['name'],
        'title'     => $copy['headline'],
        'benefits'  => $copy['benefits'],
        'image'     => WP_STATISTICS_URL . 'assets/images/premium-modal/' . $slug . '.png',
        'state'     => $state,
        'notice'    => $notice,
        'notice_warning' => $state === 'activate' || (!$hasLicense && $isInstalled),
        'activate_url' => $activateUrl,
    ];

    $switcher[$slug] = ['name' => $copy['name'], 'state' => $state, 'badge' => $badge];
}
?>
<div class="wps-premium-popup">
    <button type="button" class="wps-premium-popup__close js-wps-premiumModalClose" aria-label="<?php esc_attr_e('Close', 'wp-statistics'); ?>">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </button>
    <p class="wps-premium-popup__eyebrow"><?php
        /* translators: %d: number of add-ons. */
        echo esc_html(sprintf(__('WP Statistics Premium · %d add-ons', 'wp-statistics'), count(AddonCatalog::SLUGS))); ?></p>

    <?php View::load('components/modals/introduce-premium/switcher', ['items' => $switcher, 'id_prefix' => $idPrefix]); ?>

    <div class="wps-premium-popup__steps">
        <?php foreach ($steps as $step) {
            View::load('components/modals/introduce-premium/step-content', array_merge($step, [
                'id_prefix'  => $idPrefix,
                'is_premium' => $isPremium,
                'addons_url' => $addonsPageUrl,
            ]));
        } ?>
    </div>
</div>
