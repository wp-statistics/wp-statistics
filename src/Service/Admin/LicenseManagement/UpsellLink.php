<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

/**
 * Builds every link from the plugin to wp-statistics.com that sells Premium, so no template writes a
 * query string by hand and every click carries the same tracking.
 *
 * The words say where you go:
 *  - "Upgrade to Premium" = {@see pricing()}, the pricing page, new tab.
 *  - "Learn more"         = {@see addon()}, that add-on's page (or {@see addons()} when it is not about
 *                           one add-on), new tab.
 *  - "See what's included" opens the Premium popup (small pointers only: tooltips, locked tabs and buttons,
 *    teasers, the Go Premium box, the showcase info icons). Those triggers carry `data-surface`.
 *
 * Tracking scheme:
 *  - utm_source   = wp-statistics
 *  - utm_medium   = the surface the link sits on: header, popup, settings-card, lock-page, teaser,
 *                   go-premium-box, showcase, notice, admin-bar, email, plugins-list, add-ons-page,
 *                   help-center, ...
 *  - utm_campaign = the add-on without the "wp-statistics-" prefix (data-plus, marketing, mini-chart,
 *                   advanced-reporting, realtime-stats, widgets, customization, rest-api) or "premium".
 *  - utm_content  = the button: upgrade | learn-more (other links name themselves: changelog, docs, ...).
 *  - utm_term     = popup links only: the surface that opened the popup (premium-modal.js copies the
 *                   trigger's data-surface into it), e.g. utm_medium=popup&utm_term=locked-tab.
 */
class UpsellLink
{
    const SOURCE = 'wp-statistics';

    /**
     * utm_campaign for an add-on slug: "wp-statistics-data-plus" → "data-plus", empty → "premium".
     *
     * @param string $addon Add-on slug or empty.
     *
     * @return string
     */
    public static function campaign($addon = '')
    {
        $addon = (string)$addon;

        if ($addon === '' || $addon === 'first-step' || $addon === 'premium') {
            return 'premium';
        }

        return str_replace('wp-statistics-', '', $addon);
    }

    /**
     * Adds the utm tags to any wp-statistics.com URL (keeps its own query string).
     *
     * @param string $url      Full URL.
     * @param string $surface  utm_medium.
     * @param string $campaign utm_campaign (an add-on slug is shortened).
     * @param string $content  utm_content. Optional.
     *
     * @return string Raw URL (escape on output).
     */
    public static function tag($url, $surface, $campaign = 'premium', $content = '')
    {
        $args = [
            'utm_source'   => self::SOURCE,
            'utm_medium'   => sanitize_key($surface),
            'utm_campaign' => self::campaign($campaign),
        ];

        if ($content !== '') {
            $args['utm_content'] = sanitize_key($content);
        }

        return add_query_arg($args, $url);
    }

    /**
     * Pricing page: the only target of "Upgrade to Premium".
     *
     * @param string $surface utm_medium.
     * @param string $addon   Add-on slug the surface is about, or empty for Premium as a whole.
     * @param string $content utm_content. Default "upgrade".
     *
     * @return string
     */
    public static function pricing($surface, $addon = '', $content = 'upgrade')
    {
        return self::tag(WP_STATISTICS_SITE_URL . '/pricing/', $surface, $addon, $content);
    }

    /**
     * One add-on's page: the target of "Learn more" on an add-on surface.
     *
     * @param string $addon   Add-on slug, e.g. "wp-statistics-data-plus".
     * @param string $surface utm_medium.
     * @param string $content utm_content. Default "learn-more".
     *
     * @return string
     */
    public static function addon($addon, $surface, $content = 'learn-more')
    {
        $addon = (string)$addon;

        if ($addon === '' || $addon === 'first-step') {
            return self::addons($surface, $content);
        }

        return self::tag(WP_STATISTICS_SITE_URL . '/add-ons/' . rawurlencode($addon) . '/', $surface, $addon, $content);
    }

    /**
     * The add-ons list on wp-statistics.com: "Learn more" when the surface is about Premium as a whole.
     *
     * @param string $surface utm_medium.
     * @param string $content utm_content. Default "learn-more".
     *
     * @return string
     */
    public static function addons($surface, $content = 'learn-more')
    {
        return self::tag(WP_STATISTICS_SITE_URL . '/add-ons/', $surface, 'premium', $content);
    }

    /**
     * Attributes for a small pointer that opens the Premium popup ("See what's included"): the add-on step
     * and the surface, which the popup copies into its links as utm_term.
     *
     * @param string $addon   Add-on slug, or "first-step".
     * @param string $surface Surface name.
     *
     * @return string ` data-target="..." data-surface="..."` (escaped).
     */
    public static function popupAttributes($addon, $surface)
    {
        return sprintf(
            ' data-target="%s" data-surface="%s"',
            esc_attr($addon !== '' ? $addon : 'first-step'),
            esc_attr(sanitize_key($surface))
        );
    }
}
