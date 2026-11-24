<?php
/**
 * Go Premium box on the Overview ("Picture first"): the sketch, the title, one line, a solid "See what's included"
 * button that opens the Premium popup (the add-ons list on wp-statistics.com stays the href for no-JS), the guarantee and ×.
 * Who sees it: {@see \WP_Statistics\Service\Admin\Metabox\MetaboxHelper::shouldHideGoPremium()}.
 */

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\UpsellLink;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="o-wrap wps-about-widget wps-about-widget__premium wps-go-premium">
    <a class="wps-go-premium__close" href="<?php echo esc_url(add_query_arg([
        'action'    => 'wp_statistics_dismiss_widget',
        'nonce'     => wp_create_nonce('wp_statistics_dismiss_widget'),
        'widget_id' => $widget_id
    ])) ?>" aria-label="<?php esc_attr_e('Dismiss go premium widget', 'wp-statistics'); ?>" title="<?php esc_attr_e('Close', 'wp-statistics'); ?>">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </a>
    <div class="wps-go-premium__media">
        <img class="wps-go-premium__img wps-go-premium__img--side" src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/premium-widget.svg'); ?>" alt="" width="330" height="222">
        <img class="wps-go-premium__img wps-go-premium__img--wide" src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/premium-widget-wide.svg'); ?>" alt="" width="396" height="210">
    </div>
    <div class="wps-go-premium__body">
        <h2 class="wps-go-premium__title hndle ui-sortable-handle"><?php esc_html_e('Get More with Premium Analytics', 'wp-statistics'); ?></h2>
        <p class="wps-go-premium__text"><?php esc_html_e('Upgrade to see clicks, downloads, live visitors and goals.', 'wp-statistics'); ?></p>
        <a class="wps-upsell-btn wps-upsell-btn--primary wps-go-premium__button js-wps-openPremiumModal" href="<?php echo esc_url(UpsellLink::addons('go-premium-box')); ?>"<?php echo UpsellLink::popupAttributes('first-step', 'go-premium-box'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper ?>><?php esc_html_e("See what's included", 'wp-statistics'); ?></a>
        <?php View::load('components/upsell/guarantee', ['class' => 'wps-go-premium__guarantee']); ?>
    </div>
</div>
