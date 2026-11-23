<?php
/**
 * Inline teaser: one small, closable note inside a working core page, where a part of the page needs an add-on
 * (for example the clicks and downloads Data Plus adds to the visitor journey). Shows a static, greyed
 * "Example" preview of that part plus one short line that follows the site's situation:
 *
 *  - add-on active or prompts off (Customization white label): nothing is printed;
 *  - not installed: the value line and "Learn about <add-on>", which opens the premium modal on that add-on
 *    (the add-on page on wp-statistics.com stays the href for no-JS);
 *  - installed but turned off: "<add-on> is installed but turned off." and "Activate", a link to the Plugins screen
 *    narrowed to the add-on (users who can activate plugins; the link never activates anything itself);
 *  - in the site's plan but not installed: "Included in your plan." and "Install" (license managers);
 *  - users without the manage capability: no buttons, "Ask a site admin…";
 *  - closed by this user: nothing is printed.
 *
 * The preview is static markup from the caller, never site data. Exports and print leave the teaser out
 * (it is HTML only and hidden in print).
 *
 * Args:
 *  - id         (string) Required. Teaser id for the per-user close, e.g. "dp-journey".
 *  - addon      (string) Required. Add-on slug, e.g. "wp-statistics-data-plus".
 *  - addon_name (string) Required. Add-on name, e.g. "Data Plus".
 *  - message    (string) Value line for sites without the add-on.
 *  - campaign   (string) utm_campaign of the "Learn about" link. Default: the id.
 *  - preview    (string) Static example markup (trusted HTML from the calling template). Optional.
 *  - class      (string) Extra classes.
 */

use WP_STATISTICS\User;
use WP_Statistics\Service\Admin\LicenseManagement\InlineTeaser;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (empty($id) || empty($addon) || empty($addon_name)) {
    return;
}

$teaserId  = InlineTeaser::sanitizeId($id);
$situation = LicenseHelper::getAddonSituation($addon);

if (in_array($situation, [LicenseHelper::SITUATION_ACTIVE, LicenseHelper::SITUATION_HIDDEN], true) || InlineTeaser::isDismissed($teaserId)) {
    return;
}

$canManage = User::Access('manage');
$line      = '';
$action    = null; // ['url' => ..., 'label' => ..., 'external' => bool]

switch ($situation) {
    case LicenseHelper::SITUATION_INACTIVE:
        /* translators: %s: Add-on name. */
        $line = sprintf(__('%s is installed but turned off.', 'wp-statistics'), $addon_name);

        if ($canManage && current_user_can('activate_plugins')) {
            $action = [
                'url'   => LicenseHelper::getAddonPluginsPageUrl($addon),
                /* translators: %s: Add-on name. */
                'label' => sprintf(__('Activate %s', 'wp-statistics'), $addon_name),
            ];
        }
        break;

    case LicenseHelper::SITUATION_IN_PLAN:
        /* translators: %s: Add-on name. */
        $line = sprintf(__('%s is included in your plan.', 'wp-statistics'), $addon_name);

        if ($canManage && LicenseHelper::canManageLicenses() && current_user_can('install_plugins')) {
            $action = [
                'url'   => LicenseHelper::getAddonInstallUrl($addon),
                /* translators: %s: Add-on name. */
                'label' => sprintf(__('Install %s', 'wp-statistics'), $addon_name),
            ];
        }
        break;

    default:
        /* translators: %s: Add-on name. */
        $line = !empty($message) ? $message : sprintf(__('This part comes with %s.', 'wp-statistics'), $addon_name);

        if ($canManage) {
            $action = [
                'url'      => add_query_arg([
                    'utm_source'   => 'wp-statistics',
                    'utm_medium'   => 'link',
                    'utm_campaign' => !empty($campaign) ? $campaign : $teaserId,
                ], WP_STATISTICS_SITE_URL . '/add-ons/' . $addon . '/'),
                /* translators: %s: Add-on name. */
                'label'    => sprintf(__('Learn about %s', 'wp-statistics'), $addon_name),
                'external' => true,
                'modal'    => $addon,
            ];
        }
}

if (!$action && !$canManage) {
    /* translators: %s: Add-on name. */
    $line .= ' ' . sprintf(__('Ask a site admin to turn on %s.', 'wp-statistics'), $addon_name);
} elseif (!$action) {
    /* translators: %s: Add-on name. */
    $line .= ' ' . sprintf(__('Ask a site admin to set up %s.', 'wp-statistics'), $addon_name);
}

$classes = trim('wps-inline-teaser js-wps-inline-teaser ' . ($class ?? ''));
?>
<div class="<?php echo esc_attr($classes); ?>" data-teaser-id="<?php echo esc_attr($teaserId); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce(InlineTeaser::NONCE_ACTION)); ?>" data-situation="<?php echo esc_attr($situation); ?>">
    <?php if (!empty($preview)) : ?>
        <div class="wps-inline-teaser__preview">
            <span class="wps-inline-teaser__example"><?php esc_html_e('Example', 'wp-statistics'); ?></span>
            <div class="wps-inline-teaser__sample" aria-hidden="true">
                <?php echo $preview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup from the calling template ?>
            </div>
        </div>
    <?php endif; ?>
    <p class="wps-inline-teaser__note">
        <svg class="wps-inline-teaser__lock" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
        <span class="wps-inline-teaser__line"><?php echo esc_html(trim($line)); ?></span>
        <?php if ($action) : ?>
            <a class="wps-inline-teaser__link<?php echo !empty($action['modal']) ? ' js-wps-openPremiumModal' : ''; ?>" href="<?php echo esc_url($action['url']); ?>"<?php echo !empty($action['modal']) ? ' data-target="' . esc_attr($action['modal']) . '"' : ''; ?><?php echo !empty($action['external']) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html($action['label']); ?></a>
        <?php endif; ?>
        <button type="button" class="wps-inline-teaser__close js-wps-inline-teaser-close" aria-label="<?php esc_attr_e('Hide this tip', 'wp-statistics'); ?>" title="<?php esc_attr_e('Hide this tip', 'wp-statistics'); ?>"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
    </p>
</div>
