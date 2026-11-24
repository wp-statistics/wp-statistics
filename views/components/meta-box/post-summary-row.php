<?php
/**
 * One labelled row of the post summary: views · visitors, then the top referrer.
 *
 * @var string     $label         Row label, e.g. "All time".
 * @var int|string $views         Views in the period.
 * @var int|string $visitors      Visitors in the period.
 * @var string     $referrerUrl   Top referrer URL (optional).
 * @var int|string $referrerCount Referrals from the top referrer (optional).
 */

if (!defined('ABSPATH')) exit;

$views         = intval($views ?? 0);
$visitors      = intval($visitors ?? 0);
$referrerCount = intval($referrerCount ?? 0);

// Keep the host only, without "www."
$referrerHost = '';
if (!empty($referrerUrl)) {
    $referrerHost = wp_parse_url(trim($referrerUrl), PHP_URL_HOST);
    $referrerHost = $referrerHost ? preg_replace('/^www\./i', '', $referrerHost) : '';
}
?>
<div class="wps-post-summary__row">
    <span class="wps-post-summary__label"><?php echo esc_html($label); ?></span>
    <span class="wps-post-summary__value">
        <?php
        echo wp_kses(sprintf(
            // translators: %s: Number of views.
            _n('%s view', '%s views', $views, 'wp-statistics'),
            '<b>' . number_format_i18n($views) . '</b>'
        ), ['b' => []]);
        ?><span class="wps-post-summary__sep" aria-hidden="true">·</span><?php
        echo wp_kses(sprintf(
            // translators: %s: Number of visitors.
            _n('%s visitor', '%s visitors', $visitors, 'wp-statistics'),
            '<b>' . number_format_i18n($visitors) . '</b>'
        ), ['b' => []]);
        ?>
    </span>
    <?php if ($referrerCount > 0 && !empty($referrerHost)) : ?>
        <span class="wps-post-summary__value wps-post-summary__referrer">
            <?php
            echo wp_kses(sprintf(
                // translators: 1: Referrer link - 2: Number of referrals from it.
                __('Top referrer %1$s (%2$s)', 'wp-statistics'),
                '<a href="' . esc_url($referrerUrl) . '" target="_blank" rel="noreferrer nofollow">' . esc_html($referrerHost) . '</a>',
                number_format_i18n($referrerCount)
            ), ['a' => ['href' => [], 'target' => [], 'rel' => []]]);
            ?>
        </span>
    <?php endif; ?>
</div>
