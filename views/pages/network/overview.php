<?php
/**
 * Network Admin > Statistics: one table row per site with visitors and views of the last
 * 7 days, the change against the 7 days before, a 7-day line of views, the top page and a
 * link to the site's Overview. A totals row sums every site.
 *
 * Sorting, search and pages (10 rows each) run in the browser over these rows
 * (assets/dev/javascript/network-overview.js). Without the script every row shows,
 * sorted by visitors.
 *
 * Args:
 *  - sites (array[]) From WP_STATISTICS\Network::getSitesOverview().
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Network;
use WP_Statistics\Components\View;

$sites = isset($sites) && is_array($sites) ? $sites : [];

// Visitors high to low; sites without the plugin go last, then by name.
usort($sites, function ($a, $b) {
    $va = !empty($a['active']) && $a['visitors'] !== null ? (int)$a['visitors'] : -1;
    $vb = !empty($b['active']) && $b['visitors'] !== null ? (int)$b['visitors'] : -1;

    return $vb <=> $va ?: strcasecmp((string)$a['name'], (string)$b['name']);
});

$totals  = Network::getTotals($sites);
$newChip = '<span class="wps-change wps-change--flat wps-change--new" title="' . esc_attr__('No visitors in the 7 days before', 'wp-statistics') . '">' . esc_html__('New', 'wp-statistics') . '</span>';
$perPage = 10;

/**
 * 7-day line as inline SVG. Red when the site went down, brand blue otherwise.
 */
$sparkline = function ($values, $down) {
    $values = array_map('intval', (array)$values);
    $count  = count($values);
    if ($count < 2) {
        return '';
    }

    $width  = 80;
    $height = 24;
    $max    = max(1, max($values));
    $step   = $width / ($count - 1);
    $points = [];

    foreach ($values as $i => $value) {
        $points[] = round($i * $step, 1) . ',' . round($height - 2 - ($value / $max) * ($height - 4), 1);
    }

    $line = implode(' ', $points);
    $area = '0,' . $height . ' ' . $line . ' ' . $width . ',' . $height;

    return sprintf(
        '<svg class="wps-network-table__spark%1$s" width="%2$d" height="%3$d" viewBox="0 0 %2$d %3$d" preserveAspectRatio="none" aria-hidden="true" focusable="false"><polygon points="%4$s"/><polyline points="%5$s"/></svg>',
        $down ? ' is-down' : '',
        $width,
        $height,
        esc_attr($area),
        esc_attr($line)
    );
};

$arrow = '<svg class="wps-network-table__arrow" viewBox="0 0 16 16" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 8h10M9 4l4 4-4 4"/></svg>';

$sortHeader = function ($key, $label, $class = '', $sorted = '') {
    printf(
        '<th scope="col" class="%1$s"%2$s><button type="button" class="wps-network-table__sort" data-sort="%3$s">%4$s<span class="wps-network-table__sort-icon" aria-hidden="true"></span></button></th>',
        esc_attr(trim('wps-network-table__h-' . $key . ' ' . $class)),
        $sorted ? ' aria-sort="' . esc_attr($sorted) . '"' : '',
        esc_attr($key),
        esc_html($label)
    );
};

$changeSort = function ($site) {
    if (empty($site['active']) || $site['visitors'] === null) {
        return -1e9;
    }
    if ($site['change'] === null) {
        return (int)$site['visitors'] > 0 ? 1e9 : 0;
    }
    return (float)$site['change'];
};
?>
<?php // No .wps-wrap: its old table and .button rules (inline-block !important links, 49px buttons) fight this table. ?>
<div class="wrap wps-network-overview">
    <div class="wps-network-overview__head">
        <div>
            <h1 class="wps-network-overview__title"><?php esc_html_e('Statistics', 'wp-statistics'); ?></h1>
            <p class="wps-network-overview__intro" dir="auto"><?php esc_html_e('All sites, last 7 days compared to the 7 days before.', 'wp-statistics'); ?></p>
        </div>
        <?php if (count($sites) > 1) : ?>
            <label class="wps-network-overview__search">
                <svg viewBox="0 0 16 16" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="7" cy="7" r="4.5"/><path d="M10.5 10.5 14 14"/></svg>
                <span class="screen-reader-text"><?php esc_html_e('Search sites', 'wp-statistics'); ?></span>
                <input type="search" class="wps-network-overview__search-input" placeholder="<?php esc_attr_e('Search sites', 'wp-statistics'); ?>" autocomplete="off" data-network-search>
            </label>
        <?php endif; ?>
    </div>

    <div class="wps-card wps-network-table-wrap" data-network-table data-per-page="<?php echo (int)$perPage; ?>">
        <?php if (empty($sites)) : ?>
            <?php View::load('components/objects/empty-state', ['message' => __('No sites found.', 'wp-statistics'), 'range' => false]); ?>
        <?php else : ?>
            <table class="wps-network-table">
                <caption class="screen-reader-text"><?php esc_html_e('Visitors and views of each site in the last 7 days', 'wp-statistics'); ?></caption>
                <thead>
                <tr>
                    <?php
                    $sortHeader('name', __('Site', 'wp-statistics'));
                    $sortHeader('visitors', __('Visitors', 'wp-statistics'), 'is-num', 'descending');
                    $sortHeader('views', __('Views', 'wp-statistics'), 'is-num');
                    $sortHeader('change', __('Change', 'wp-statistics'), 'is-num');
                    ?>
                    <th scope="col" class="wps-network-table__h-spark"><?php esc_html_e('7 days', 'wp-statistics'); ?></th>
                    <th scope="col" class="wps-network-table__h-top"><?php esc_html_e('Top page', 'wp-statistics'); ?></th>
                    <th scope="col" class="wps-network-table__h-open"><span class="screen-reader-text"><?php esc_html_e('Open', 'wp-statistics'); ?></span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($sites as $site) :
                    $active   = !empty($site['active']);
                    $hasData  = $active && $site['visitors'] !== null;
                    $name     = $site['name'] !== '' ? $site['name'] : $site['url'];
                    $address  = untrailingslashit(preg_replace('#^https?://#', '', (string)$site['url']));
                    $quiet    = !$hasData || (int)$site['visitors'] === 0;
                    $classes  = 'wps-network-table__row' . ($quiet ? ' is-quiet' : '') . (!$active ? ' is-inactive' : '');
                    ?>
                    <tr class="<?php echo esc_attr($classes); ?>"
                        data-name="<?php echo esc_attr(function_exists('mb_strtolower') ? mb_strtolower($name) : strtolower($name)); ?>"
                        data-search="<?php echo esc_attr(function_exists('mb_strtolower') ? mb_strtolower($name . ' ' . $address) : strtolower($name . ' ' . $address)); ?>"
                        data-visitors="<?php echo esc_attr($hasData ? (int)$site['visitors'] : -1); ?>"
                        data-views="<?php echo esc_attr($hasData ? (int)$site['views'] : -1); ?>"
                        data-change="<?php echo esc_attr($changeSort($site)); ?>">
                        <td class="wps-network-table__site">
                            <?php if ($active) : ?>
                                <a class="wps-network-table__name" href="<?php echo esc_url($site['overview_url']); ?>" title="<?php echo esc_attr($name); ?>"><bdi><?php echo esc_html($name); ?></bdi></a>
                            <?php else : ?>
                                <span class="wps-network-table__name-line">
                                    <span class="wps-network-table__name" title="<?php echo esc_attr($name); ?>"><bdi><?php echo esc_html($name); ?></bdi></span>
                                    <span class="wps-pill wps-pill--tag"><?php esc_html_e('Not active', 'wp-statistics'); ?></span>
                                </span>
                            <?php endif; ?>
                            <a class="wps-network-table__url" href="<?php echo esc_url($site['url']); ?>" target="_blank" rel="noopener"><bdi dir="ltr"><?php echo esc_html($address); ?></bdi><span class="screen-reader-text"><?php esc_html_e('(opens in a new tab)', 'wp-statistics'); ?></span></a>
                        </td>

                        <?php if (!$active) : ?>
                            <td class="is-num wps-network-table__visitors">&ndash;</td>
                            <td class="is-num wps-network-table__views">&ndash;</td>
                            <td class="is-num wps-network-table__change"><span class="wps-network-table__muted">&ndash;</span></td>
                            <td class="wps-network-table__spark-cell"><span class="wps-network-table__muted"><?php esc_html_e('Not tracked', 'wp-statistics'); ?></span></td>
                            <td class="wps-network-table__top"></td>
                            <td class="is-num wps-network-table__open-cell">
                                <a class="wps-network-table__open" href="<?php echo esc_url($site['activate_url']); ?>"><span class="wps-network-table__open-text"><?php esc_html_e('Activate', 'wp-statistics'); ?></span><?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?><span class="screen-reader-text"><?php echo esc_html(sprintf(/* translators: %s: Site name. */ __('Activate WP Statistics on %s', 'wp-statistics'), $name)); ?></span></a>
                            </td>
                        <?php else : ?>
                            <td class="is-num wps-network-table__visitors"><?php echo $hasData ? esc_html(number_format_i18n($site['visitors'])) : '&ndash;'; ?></td>
                            <td class="is-num wps-network-table__views"><?php echo $hasData ? esc_html(number_format_i18n($site['views'])) : '&ndash;'; ?></td>
                            <td class="is-num wps-network-table__change">
                                <?php
                                if (!$hasData) {
                                    echo '<span class="wps-network-table__muted">&ndash;</span>';
                                } elseif ($site['change'] !== null) {
                                    View::load('components/objects/change-chip', ['change' => $site['change']]);
                                } elseif ((int)$site['visitors'] > 0) {
                                    echo $newChip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped where built
                                } else {
                                    View::load('components/objects/change-chip', ['change' => 0]);
                                }
                                ?>
                            </td>
                            <td class="wps-network-table__spark-cell">
                                <?php
                                if ($hasData && (int)$site['views'] > 0) {
                                    echo $sparkline($site['daily'], $site['change'] !== null && $site['change'] < 0); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built and escaped above
                                } else {
                                    echo '<span class="wps-network-table__muted">' . esc_html__('No visits', 'wp-statistics') . '</span>';
                                }
                                ?>
                            </td>
                            <td class="wps-network-table__top">
                                <?php if (!empty($site['top_page'])) : ?>
                                    <a class="wps-network-table__top-link" href="<?php echo esc_url($site['top_page']['url']); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr($site['top_page']['title']); ?>"><bdi><?php echo esc_html($site['top_page']['title']); ?></bdi><span class="screen-reader-text"><?php esc_html_e('(opens in a new tab)', 'wp-statistics'); ?></span></a>
                                <?php else : ?>
                                    <span class="wps-network-table__muted">&ndash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="is-num wps-network-table__open-cell">
                                <a class="wps-network-table__open" href="<?php echo esc_url($site['overview_url']); ?>"><span class="wps-network-table__open-text"><?php esc_html_e('Open', 'wp-statistics'); ?></span><?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?><span class="screen-reader-text"><?php echo esc_html(sprintf(/* translators: %s: Site name. */ __('Overview of %s', 'wp-statistics'), $name)); ?></span></a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <tr class="wps-network-table__no-match" hidden>
                    <td colspan="7"><?php View::load('components/objects/empty-state', ['message' => __('No sites match your search.', 'wp-statistics'), 'range' => false, 'icon' => 'none']); ?></td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th scope="row" class="wps-network-table__site"><?php echo esc_html(sprintf(/* translators: %s: number of sites. */ _n('All %s site', 'All %s sites', count($sites), 'wp-statistics'), number_format_i18n(count($sites)))); ?></th>
                    <td class="is-num wps-network-table__visitors"><?php echo esc_html(number_format_i18n($totals['visitors'])); ?></td>
                    <td class="is-num wps-network-table__views"><?php echo esc_html(number_format_i18n($totals['views'])); ?></td>
                    <td class="is-num wps-network-table__change">
                        <?php
                        if ($totals['change'] !== null) {
                            View::load('components/objects/change-chip', ['change' => $totals['change']]);
                        } elseif ($totals['visitors'] > 0) {
                            echo $newChip; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped where built
                        }
                        ?>
                    </td>
                    <td class="wps-network-table__spark-cell"></td>
                    <td class="wps-network-table__top"></td>
                    <td class="wps-network-table__open-cell"></td>
                </tr>
                </tfoot>
            </table>

            <div class="wps-network-table__pager" data-network-pager>
                <span class="wps-network-table__pager-text" aria-live="polite" data-network-pager-text><?php echo esc_html(sprintf(/* translators: %s: number of sites. */ _n('%s site', '%s sites', count($sites), 'wp-statistics'), number_format_i18n(count($sites)))); ?></span>
                <span class="wps-network-table__pager-buttons" hidden data-network-pager-buttons>
                    <button type="button" class="wps-network-table__page-btn" data-network-page="-1"><?php esc_html_e('Previous', 'wp-statistics'); ?></button>
                    <button type="button" class="wps-network-table__page-btn" data-network-page="1"><?php esc_html_e('Next', 'wp-statistics'); ?></button>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>
