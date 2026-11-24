<?php
/**
 * Shared visitor table: one fixed column order everywhere,
 *
 *   Visitor | Referrer | Journey | Views | Last view
 *
 * Only the sort changes between tables: the sorted column header is bold with the ↓ arrow
 * (Most Active / Top Visitors sort by Views, Latest / Online / Logged-in / Referred by Last view).
 * Used by the Overview widgets (Most Active Visitors, Latest Visitors, Currently Online),
 * the Visitor Insights tables (Visitors, Top Visitors, Online, Logged-in Users), Referred
 * Visitors and the WP Dashboard visitor widgets.
 *
 * Column widths are fixed (table-layout: fixed, widths on the header cells in
 * component/_visitor-table.scss), so tables that show different visitors (the Visitors card
 * tabs Latest / Most active / Online now) keep the same columns. Journey takes the free width.
 *
 * Responsive by its own width (container query on .wps-visitor-table-wrap, works in any card):
 *  - under 700px: the Referrer column folds under the visitor pill, dates use the narrow form
 *  - under 470px: the number that is not sorted (Views or Last view) hides
 *  - under 380px: only the pill, the journey and the sorted number
 *
 * Args:
 *  - visitors     (VisitorDecorator[]) Rows (`data` is read too).
 *  - sort         (string) 'last' (default) or 'views'.
 *  - show_pages   (bool)   Default true. Journey cell: views/components/objects/visitor-pages.php.
 *  - show_views   (bool)   Default true.
 *  - show_date    (bool)   Default true.
 *  - date_source  (string) 'last_view' (default) or 'page_view' (single post lists).
 *  - date_label   (string) Header of the date column. Default "Last view".
 *  - exit_label   (string) Accessible name of the exit line (Online: "Current page").
 *  - views_hint   (string) Tooltip text after the "Views" header (full report pages).
 *  - target       (string) Link target for the views link ('_blank' in editor side panels).
 *  - empty        (array|false) Args for components/objects/empty-state when there are no rows;
 *                 false prints nothing (the caller shows its own empty state).
 *  - class        (string) Extra classes on the wrapper.
 *
 * Example:
 *  View::load('components/tables/visitor-table', ['visitors' => $data, 'sort' => 'views']);
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;

$rows       = isset($visitors) ? $visitors : (isset($data) ? $data : []);
$rows       = is_array($rows) ? array_values(array_filter($rows, function ($row) { return $row instanceof VisitorDecorator; })) : [];
$sort       = isset($sort) && $sort === 'views' ? 'views' : 'last';
$showPages  = !isset($show_pages) || $show_pages;
$showViews  = !isset($show_views) || $show_views;
$showDate   = !isset($show_date) || $show_date;
$dateSource = isset($date_source) && $date_source === 'page_view' ? 'page_view' : 'last_view';
$dateLabel  = !empty($date_label) ? (string)$date_label : __('Last view', 'wp-statistics');
$linkTarget = !empty($target) ? (string)$target : '';
$extraClass = !empty($class) ? ' ' . trim((string)$class) : '';

if (empty($rows)) {
    if (!isset($empty) || $empty !== false) {
        View::load('components/objects/empty-state', isset($empty) && is_array($empty) ? $empty : []);
    }
    return;
}

// Online-now dots: one query at most, only for rows whose list query had no last_view.
VisitorDecorator::primeOnlineStatus($rows);

$sortedHeader = function ($label, $isSorted, $hint = '') {
    if (!$isSorted) {
        return esc_html($label) . ($hint ? ' <span class="wps-tooltip" title="' . esc_attr($hint) . '"><i class="wps-tooltip-icon"></i></span>' : '');
    }

    return '<span class="wps-order">' . esc_html($label) . ($hint ? ' <span class="wps-tooltip" title="' . esc_attr($hint) . '"><i class="wps-tooltip-icon"></i></span>' : '') . '</span>';
};
?>
<div class="o-table-wrapper wps-visitor-table-wrap<?php echo esc_attr($extraClass); ?>">
    <table width="100%" class="o-table wps-new-table wps-visitor-table wps-visitor-table--sort-<?php echo esc_attr($sort); ?><?php echo $showPages ? '' : ' wps-visitor-table--no-pages'; ?>">
        <thead>
        <tr>
            <th scope="col" class="wps-pd-l wps-visitor-table__visitor"><?php esc_html_e('Visitor', 'wp-statistics'); ?></th>
            <th scope="col" class="wps-pd-l wps-visitor-table__referrer"><?php esc_html_e('Referrer', 'wp-statistics'); ?></th>
            <?php if ($showPages) : ?>
                <th scope="col" class="wps-pd-l wps-visitor-table__pages"><?php echo esc_html_x('Journey', 'visitor table column: entry and exit page', 'wp-statistics'); ?></th>
            <?php endif; ?>
            <?php if ($showViews) : ?>
                <th scope="col" class="wps-pd-l wps-col-num wps-visitor-table__views<?php echo $sort === 'views' ? ' is-sorted' : ''; ?>"<?php echo $sort === 'views' ? ' aria-sort="descending"' : ''; ?>>
                    <?php echo $sortedHeader(__('Views', 'wp-statistics'), $sort === 'views', isset($views_hint) ? (string)$views_hint : ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </th>
            <?php endif; ?>
            <?php if ($showDate) : ?>
                <th scope="col" class="wps-pd-l wps-col-date wps-visitor-table__last<?php echo $sort === 'last' ? ' is-sorted' : ''; ?>"<?php echo $sort === 'last' ? ' aria-sort="descending"' : ''; ?>>
                    <?php echo $sortedHeader($dateLabel, $sort === 'last'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </th>
            <?php endif; ?>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($rows as $visitor) : ?>
            <?php
            /** @var VisitorDecorator $visitor */
            $referral    = $visitor->getReferral();
            $refText     = $referral->getRawReferrer() ?: $referral->getSourceNameWithoutReferrer();
            $channel     = $referral->getSourceChannel();
            $inlineRef   = implode(' · ', array_filter([(string)$refText, (string)$channel], 'strlen'));
            $dateParts   = $dateSource === 'page_view' ? $visitor->getPageViewParts() : $visitor->getLastViewParts();
            $journeyUrl  = Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()]);
            ?>
            <tr>
                <td class="wps-pd-l wps-visitor-table__visitor">
                    <?php View::load('components/visitor-information', ['visitor' => $visitor]); ?>
                    <?php if ($inlineRef !== '') : ?>
                        <span class="wps-visitor-table__ref-inline" title="<?php echo esc_attr($inlineRef); ?>"><?php echo esc_html($inlineRef); ?></span>
                    <?php endif; ?>
                </td>

                <td class="wps-pd-l wps-visitor-table__referrer">
                    <?php
                    View::load('components/objects/referrer-link', [
                        'label'       => $channel,
                        'url'         => $referral->getReferrer(),
                        'title'       => $referral->getRawReferrer(),
                        'source_name' => $referral->getSourceNameWithoutReferrer(),
                    ]);
                    ?>
                </td>

                <?php if ($showPages) : ?>
                    <td class="wps-pd-l wps-visitor-table__pages">
                        <?php View::load('components/objects/visitor-pages', ['visitor' => $visitor, 'exit_label' => isset($exit_label) ? $exit_label : '']); ?>
                    </td>
                <?php endif; ?>

                <?php if ($showViews) : ?>
                    <td class="wps-pd-l wps-col-num wps-visitor-table__views<?php echo $sort === 'views' ? ' is-sorted' : ''; ?>">
                        <a<?php echo $linkTarget ? ' target="' . esc_attr($linkTarget) . '"' : ''; ?> href="<?php echo esc_url($journeyUrl); ?>"><?php echo esc_html($visitor->getHits()); ?></a>
                    </td>
                <?php endif; ?>

                <?php if ($showDate) : ?>
                    <td class="wps-pd-l wps-col-date wps-visitor-table__last<?php echo $sort === 'last' ? ' is-sorted' : ''; ?>">
                        <?php if (!empty($dateParts)) : ?>
                            <time class="wps-visitor-table__date<?php echo $dateParts['narrow'] !== $dateParts['wide'] ? ' has-narrow' : ''; ?>" datetime="<?php echo esc_attr($dateParts['iso']); ?>" title="<?php echo esc_attr($dateParts['full']); ?>">
                                <span class="wps-visitor-table__date-wide"><?php echo esc_html($dateParts['wide']); ?></span><?php if ($dateParts['narrow'] !== $dateParts['wide']) : ?><span class="wps-visitor-table__date-narrow"><?php echo esc_html($dateParts['narrow']); ?></span><?php endif; ?>
                            </time>
                        <?php else : ?>
                            <?php echo Admin_Template::UnknownColumn(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
