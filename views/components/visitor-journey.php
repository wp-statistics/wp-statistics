<?php
/**
 * Single visitor journey: every visit from the same IP or user, newest first, and the steps of each visit
 * newest first too (exit on top, entry and "From <referrer>" at the bottom). The opened visit is expanded,
 * the others fold (native <details>, no JS) and the oldest sit behind "Show all".
 *
 * Args:
 *  - visits  (array) VisitorJourney::buildVisits() result.
 *  - visitor (VisitorDecorator) The opened visitor row (browser / location shown on other visits only when they differ).
 *  - show    (int) Folded visits listed before "Show all". Default 3.
 */

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorJourney;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$visits = isset($visits) && is_array($visits) ? $visits : [];
$show   = isset($show) ? max(0, (int)$show) : 3;

/** @var VisitorDecorator|null $visitor */
$current = isset($visitor) && $visitor instanceof VisitorDecorator ? $visitor : null;

$wpsJourneyTime = function ($date) {
    return $date ? DateTime::format($date, ['date_format' => DateTime::getTimeFormat()]) : '';
};

$wpsJourneyDate = function ($date) {
    if (empty($date)) return '';

    return DateTime::format($date, [
        'short_month'  => true,
        'exclude_year' => DateTime::format($date, ['date_format' => 'Y']) === DateTime::get('now', 'Y'),
    ]);
};

// "Firefox · Málaga" for visits whose browser or place differ from the opened visit.
$wpsJourneyDiff = function (VisitorDecorator $row) use ($current) {
    $parts = [];

    if (!$current || $row->getBrowser()->getName() !== $current->getBrowser()->getName()) {
        if (!Admin_Template::isUnknown($row->getBrowser()->getName())) {
            $parts[] = sprintf('<img src="%s" alt="" width="14" height="14"> %s', esc_url($row->getBrowser()->getLogo()), esc_html($row->getBrowser()->getName()));
        }
    }

    if (!$current || $row->getLocation()->getCity() !== $current->getLocation()->getCity() || $row->getLocation()->getCountryCode() !== $current->getLocation()->getCountryCode()) {
        $city = $row->getLocation()->getCity();
        $name = !Admin_Template::isUnknown($city) && $city !== __('(not set)', 'wp-statistics') ? $city : $row->getLocation()->getCountryName();

        if (!Admin_Template::isUnknown($name) && $name !== __('(not set)', 'wp-statistics')) {
            $parts[] = sprintf('<img src="%s" alt="" width="16" height="12"> %s', esc_url($row->getLocation()->getCountryFlag()), esc_html($name));
        }
    }

    return implode('<span class="wps-journey__sep" aria-hidden="true">·</span>', $parts);
};

// "6 views · 15 min" (+ "from google.de" on folded visits).
$wpsJourneyMeta = function ($visit, $withSource = false) {
    /* translators: %s: Number of views. */
    $parts = [sprintf(_n('%s view', '%s views', (int)$visit['views'], 'wp-statistics'), number_format_i18n((int)$visit['views']))];

    if (!empty($visit['duration'])) {
        $parts[] = VisitorJourney::formatDuration($visit['duration']);
    }

    if (!empty($visit['events'])) {
        /* translators: %s: Number of clicks and downloads. */
        $parts[] = sprintf(_n('%s action', '%s actions', (int)$visit['events'], 'wp-statistics'), number_format_i18n((int)$visit['events']));
    }

    if ($withSource) {
        $referral = $visit['visitor']->getReferral();
        $parts[]  = $referral->getRawReferrer() ? $referral->getRawReferrer() : ($referral->getRawSourceChannel() !== 'direct' && $referral->getSourceName() ? $referral->getSourceName() : __('Direct', 'wp-statistics'));
    }

    return implode(' · ', array_map('esc_html', $parts));
};

// A click or download row. Example rows (the Data Plus teaser) print the same markup with a fixed time label and
// a plain ↗ icon instead of a link: they are static samples, never site data.
$wpsJourneyEvent = function ($step, $example = false) use ($wpsJourneyTime) {
    $kind = $step['kind'] ?? 'custom';
    ?>
    <li class="wps-journey__step wps-journey__step--event wps-journey__step--<?php echo esc_attr($kind); ?>">
        <span class="wps-journey__time"><?php echo $example ? (isset($step['time']) ? wp_kses_post($step['time']) : '') : esc_html($wpsJourneyTime($step['date'])); ?></span>
        <div class="wps-journey__body">
            <?php if ($kind === 'download') : ?>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 4v11"/><path d="M7 10l5 5 5-5"/><path d="M5 20h14"/></svg>
            <?php else : ?>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 9l11 4-5 2-2 5z"/><path d="M5 5l2 2M12 3v2M3 12h2"/></svg>
            <?php endif; ?>
            <?php // dir="auto" isolates data (event names, domains) so quotes and full stops stay in place in RTL. ?>
            <span dir="auto"><?php echo esc_html($step['label']); ?></span>
            <?php if (!empty($step['host']) && $kind === 'click' && strpos($step['label'], $step['host']) === false) : ?>
                <span class="wps-journey__muted"><span aria-hidden="true"><?php echo is_rtl() ? '←' : '→'; ?></span> <span dir="auto"><?php echo esc_html($step['host']); ?></span></span>
            <?php endif; ?>
            <?php if ($example) : ?>
                <span class="wps-new-tab-link"><svg viewBox="0 0 12 12" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4.5 2.5h-2v7h7v-2"/><path d="M7 1.5h3.5V5"/><path d="M10.5 1.5 5.5 6.5"/></svg></span>
            <?php elseif (!empty($step['target'])) :
                View::load('components/objects/new-tab-link', ['url' => $step['target'], 'label' => __('Open link in a new tab', 'wp-statistics')]);
            endif; ?>
        </div>
    </li>
    <?php
};

$wpsJourneySteps = function ($visit) use ($wpsJourneyTime, $wpsJourneyEvent) {
    /** @var VisitorDecorator $row */
    $row      = $visit['visitor'];
    $referral = $row->getReferral();
    // Direct visits carry the source name "Direct": that is no referrer.
    $source   = $referral->getRawReferrer() ?: ($referral->getRawSourceChannel() === 'direct' ? '' : $referral->getSourceName());
    ?>
    <ol class="wps-journey__steps">
        <?php foreach ($visit['steps'] as $step) : ?>
            <?php if ($step['type'] === 'event') : ?>
                <?php $wpsJourneyEvent($step); ?>
            <?php else :
                $page  = is_array($step['page']) ? $step['page'] : [];
                $title = !empty($page['title']) ? $page['title'] : __('Unknown page', 'wp-statistics');
                ?>
                <li class="wps-journey__step wps-journey__step--page">
                    <span class="wps-journey__time"><?php echo esc_html($wpsJourneyTime($step['date'])); ?></span>
                    <div class="wps-journey__body">
                        <span class="wps-journey__page wps-title-cell">
                            <?php if (!empty($page['report'])) : ?>
                                <a href="<?php echo esc_url($page['report']); ?>"<?php echo !empty($page['sub_page']) ? ' title="' . esc_attr($page['sub_page']) . '"' : ''; ?>><span dir="auto"><?php echo esc_html($title); ?></span></a>
                            <?php else : ?>
                                <span dir="auto"><?php echo esc_html($title); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($page['link'])) {
                                View::load('components/objects/new-tab-link', ['url' => $page['link']]);
                            } ?>
                        </span>
                        <?php if (!empty($step['is_entry'])) : ?>
                            <span class="wps-journey__badge wps-journey__badge--entry"><?php esc_html_e('Entry', 'wp-statistics'); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($step['is_exit'])) : ?>
                            <span class="wps-journey__badge wps-journey__badge--exit"><?php esc_html_e('Exit', 'wp-statistics'); ?></span>
                        <?php endif; ?>
                        <?php if ($step['time_on_page'] !== null) : ?>
                            <small class="wps-journey__stay" title="<?php esc_attr_e('Time on page', 'wp-statistics'); ?>"><?php echo esc_html(VisitorJourney::formatDuration($step['time_on_page'])); ?></small>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php // Where the visit came from: the first thing that happened, so it closes the list. ?>
        <li class="wps-journey__step wps-journey__step--from">
            <span class="wps-journey__time"><?php echo esc_html($wpsJourneyTime($visit['start'])); ?></span>
            <div class="wps-journey__body">
                <?php if ($source) : ?>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    <?php
                    $from = $referral->getReferrer()
                        ? sprintf('<a href="%s" target="_blank" rel="noopener noreferrer"><span dir="auto">%s</span></a>', esc_url($referral->getReferrer()), esc_html($source))
                        : '<b><span dir="auto">' . esc_html($source) . '</span></b>';
                    /* translators: %s: Referrer domain or source name. */
                    echo wp_kses_post(sprintf(__('From %s', 'wp-statistics'), $from));
                    if ($referral->getSourceChannel() && $referral->getRawSourceChannel() !== 'unassigned') {
                        echo ' <span class="wps-journey__muted">· ' . esc_html($referral->getSourceChannel()) . '</span>';
                    }
                    ?>
                <?php else : ?>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                    <?php esc_html_e('Direct visit, no referrer', 'wp-statistics'); ?>
                <?php endif; ?>
            </div>
        </li>
    </ol>
    <?php if (!empty($visit['truncated'])) : ?>
        <p class="wps-journey__empty" dir="auto"><?php
            /* translators: 1: number of page views listed, 2: page views of the visit. */
            echo esc_html(sprintf(__('Showing the latest %1$s of %2$s page views in this visit.', 'wp-statistics'), number_format_i18n((int)$visit['views_shown']), number_format_i18n((int)$visit['views'])));
        ?></p>
    <?php endif; ?>
    <?php if (empty($visit['steps'])) : ?>
        <p class="wps-journey__empty" dir="auto"><?php esc_html_e('The pages of this visit were not recorded.', 'wp-statistics'); ?></p>
    <?php endif;
};

$wpsJourneyFolded = function ($visit) use ($wpsJourneyDate, $wpsJourneyMeta, $wpsJourneyDiff, $wpsJourneySteps) {
    ?>
    <details class="wps-journey__visit wps-journey__visit--folded">
        <summary class="wps-journey__visit-head">
            <svg class="wps-journey__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6"/></svg>
            <b><?php echo esc_html($wpsJourneyDate($visit['start'])); ?></b>
            <span class="wps-journey__muted"><?php echo $wpsJourneyMeta($visit, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            <span class="wps-journey__aside"><?php echo $wpsJourneyDiff($visit['visitor']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        </summary>
        <div class="wps-journey__visit-body">
            <?php $wpsJourneySteps($visit); ?>
            <a class="wps-journey__open" href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visit['id']])); ?>"><?php esc_html_e('Open this visit', 'wp-statistics'); ?></a>
        </div>
    </details>
    <?php
};

$opened = null;
$folded = [];

foreach ($visits as $visit) {
    if ($opened === null && !empty($visit['is_current'])) {
        $opened = $visit;
    } else {
        $folded[] = $visit;
    }
}

if ($opened === null && !empty($folded)) {
    $opened = array_shift($folded);
}
?>
<div class="wps-card wps-journey">
    <?php View::load('components/objects/card-header', [
        'title'   => __('Journey', 'wp-statistics'),
        'tooltip' => __('Every visit from this IP or user, newest first. Time on page runs to the next view, so exit pages have none.', 'wp-statistics'),
        'context' => 'card',
    ]); ?>

    <?php if (empty($opened)) : ?>
        <?php View::load('components/objects/empty-state', ['message' => __('No pages were recorded for this visitor.', 'wp-statistics'), 'range' => false, 'icon' => 'activity']); ?>
    <?php else : ?>
        <div class="wps-journey__visit wps-journey__visit--open">
            <div class="wps-journey__visit-head">
                <b><?php echo esc_html($wpsJourneyDate($opened['start'])); ?></b>
                <?php
                View::load('components/objects/pill', [
                    'text'    => !empty($opened['is_latest']) ? __('Latest', 'wp-statistics') : __('This visit', 'wp-statistics'),
                    'variant' => 'brand',
                ]);
                ?>
                <span class="wps-journey__muted"><?php echo $wpsJourneyMeta($opened); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            </div>
            <?php $wpsJourneySteps($opened); ?>
            <?php
            // Clicks and downloads come from Data Plus. Without it, one quiet closable line.
            // Nothing prints while Data Plus runs or prompts are off.
            View::load('components/lock-sections/inline-teaser', [
                'id'         => 'dp-journey',
                'addon'      => 'wp-statistics-data-plus',
                'addon_name' => __('Data Plus', 'wp-statistics'),
                'message'    => __('Clicks and downloads show up here with Data Plus.', 'wp-statistics'),
                'campaign'   => 'dp-journey',
                'icons'      => ['download', 'click'],
                'class'      => 'wps-journey__teaser',
            ]);
            ?>
        </div>

        <?php
        $visible = array_slice($folded, 0, $show);
        $hidden  = array_slice($folded, $show);

        foreach ($visible as $visit) {
            $wpsJourneyFolded($visit);
        }
        ?>

        <?php if (!empty($hidden)) : ?>
            <details class="wps-journey__more">
                <summary class="wps-journey__visit-head">
                    <svg class="wps-journey__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6"/></svg>
                    <span class="wps-journey__muted"><?php
                        /* translators: %s: Number of older visits. */
                        echo esc_html(sprintf(_n('%s older visit', '%s older visits', count($hidden), 'wp-statistics'), number_format_i18n(count($hidden))));
                        ?></span>
                    <span class="wps-journey__aside wps-journey__show-all"><?php esc_html_e('Show all', 'wp-statistics'); ?></span>
                </summary>
                <?php foreach ($hidden as $visit) {
                    $wpsJourneyFolded($visit);
                } ?>
            </details>
        <?php endif; ?>
    <?php endif; ?>
</div>
