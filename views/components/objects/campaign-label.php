<?php
/**
 * Campaign of a visit, from the UTM tags on its entry page address.
 *
 * Two looks, one set of data:
 *  - "pill" (default): a small purple pill with the utm_campaign. Hover or focus shows a tooltip
 *    with the campaign block below. The pill has the same box as the "Bounced" pill and never
 *    shrinks, so in a narrow column the page title cuts first.
 *  - "block": the campaign block on its own, for the visitor card and the visitor profile.
 *    A head ("Campaign" + report link) and one row per UTM tag that is set.
 *
 * The report link opens the campaign's report when the Marketing add-on is active; without it,
 * the link opens the premium popup for Marketing.
 *
 * Args:
 *  - query   (string) Query string of the page address, with or without the leading "?".
 *  - variant (string) "pill" or "block". Default "pill".
 *  - surface (string) Where it is shown, for the upsell link. Default "campaign-label".
 *
 * Example:
 *  View::load('components/objects/campaign-label', ['query' => $page['query']]);
 *  View::load('components/objects/campaign-label', ['query' => $page['query'], 'variant' => 'block']);
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\LicenseManagement\UpsellLink;
use WP_Statistics\Utils\Url;

$query = isset($query) ? ltrim((string)$query, '?') : '';
if ($query === '') return;

$wpsUtm = [];
foreach ([
    'utm_campaign' => __('Name', 'wp-statistics'),
    'utm_source'   => __('Source', 'wp-statistics'),
    'utm_medium'   => __('Medium', 'wp-statistics'),
    'utm_content'  => __('Content', 'wp-statistics'),
    'utm_term'     => __('Term', 'wp-statistics'),
] as $wpsKey => $wpsLabel) {
    $wpsValue = Url::getParam('?' . $query, $wpsKey);
    $wpsValue = is_string($wpsValue) ? trim(Helper::plainText(sanitize_text_field($wpsValue))) : '';

    if ($wpsValue !== '') {
        $wpsUtm[$wpsKey] = ['label' => $wpsLabel, 'value' => $wpsValue];
    }
}

if (empty($wpsUtm['utm_campaign'])) return;

$wpsCampaign = $wpsUtm['utm_campaign']['value'];
$wpsVariant  = isset($variant) && $variant === 'block' ? 'block' : 'pill';
$wpsSurface  = !empty($surface) ? (string)$surface : 'campaign-label';

if (!function_exists('is_plugin_active')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
// The campaign report (referrals, type=single-campaign) comes with the Marketing add-on.
$wpsUnlocked = is_plugin_active('wp-statistics-marketing/wp-statistics-marketing.php');

$wpsTag = '<svg class="wps-campaign__tag" width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M1.5 1.5h6l7 7-6 6-7-7z"/><circle cx="5" cy="5" r="1.1" fill="currentColor" stroke="none"/></svg>';

$wpsBlock = function () use ($wpsUtm, $wpsCampaign, $wpsUnlocked, $wpsSurface, $wpsTag) {
    ?>
    <span class="wps-campaign-block">
        <span class="wps-campaign-block__head">
            <span class="wps-campaign-block__title"><?php echo $wpsTag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup ?><?php esc_html_e('Campaign', 'wp-statistics'); ?></span>
            <?php if ($wpsUnlocked) :
                $report = Menus::admin_url('referrals', array_filter([
                    'type'         => 'single-campaign',
                    'utm_campaign' => rawurlencode($wpsCampaign),
                    'utm_source'   => isset($wpsUtm['utm_source']) ? rawurlencode($wpsUtm['utm_source']['value']) : null,
                    'utm_medium'   => isset($wpsUtm['utm_medium']) ? rawurlencode($wpsUtm['utm_medium']['value']) : null,
                ])); ?>
                <a class="wps-campaign-block__link" href="<?php echo esc_url($report); ?>"><span class="wps-campaign-block__label"><?php esc_html_e('Campaign report', 'wp-statistics'); ?></span><span aria-hidden="true">&rarr;</span></a>
            <?php else : ?>
                <a class="wps-campaign-block__link wps-campaign-block__link--locked js-wps-openPremiumModal" href="<?php echo esc_url(UpsellLink::addon('wp-statistics-marketing', $wpsSurface)); ?>"<?php echo UpsellLink::popupAttributes('wp-statistics-marketing', $wpsSurface); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper ?> title="<?php esc_attr_e('Campaign reports need the Marketing add-on', 'wp-statistics'); ?>">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    <span class="wps-campaign-block__label"><?php esc_html_e('Report in Marketing', 'wp-statistics'); ?></span>
                </a>
            <?php endif; ?>
        </span>
        <span class="wps-campaign-block__rows">
            <?php foreach ($wpsUtm as $row) : ?>
                <span class="wps-campaign-block__key"><?php echo esc_html($row['label']); ?></span>
                <span class="wps-campaign-block__value" dir="auto"><?php echo esc_html($row['value']); ?></span>
            <?php endforeach; ?>
        </span>
    </span>
    <?php
};

if ($wpsVariant === 'block') {
    $wpsBlock();
    return;
}

$wpsTipId = function_exists('wp_unique_id') ? wp_unique_id('wps-campaign-tip-') : uniqid('wps-campaign-tip-');
?>
<span class="wps-campaign-label wps-tooltip" tabindex="0" data-tooltip-content="#<?php echo esc_attr($wpsTipId); ?>" data-tooltip-interactive="1">
    <?php echo $wpsTag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup ?>
    <span class="screen-reader-text"><?php esc_html_e('Campaign:', 'wp-statistics'); ?> </span>
    <span class="wps-campaign-label__text" dir="auto"><?php echo esc_html($wpsCampaign); ?></span>
</span>
<span class="wps-campaign-tip__source" hidden>
    <span id="<?php echo esc_attr($wpsTipId); ?>" class="wps-campaign-tip"><?php $wpsBlock(); ?></span>
</span>
