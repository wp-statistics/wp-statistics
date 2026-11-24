<?php

namespace WP_Statistics\Service\Admin\VisitorInsights;

use WP_Statistics\Decorators\VisitorDecorator;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Turns visitor rows, page views and events into the visits shown on the single visitor page:
 * newest visit first, each with its steps (pages and events) newest first: exit on top, entry at the bottom.
 *
 * Pure data shaping, no queries: {@see VisitorInsightsDataProvider::getVisitorData()} feeds it.
 */
class VisitorJourney
{
    /**
     * @param VisitorDecorator[] $visitors Visitor rows (the opened one and its other visits).
     * @param array $views Rows with visitor_id, date, page_id (any order).
     * @param array $events Normalized events ({@see normalizeEvent()}).
     * @param int $currentId The visitor row the page was opened for.
     * @param callable|null $pageResolver fn(int $pageId): array with title, link, report.
     * @return array[] Visits, newest first.
     */
    public static function buildVisits(array $visitors, array $views, array $events, $currentId, $pageResolver = null)
    {
        $viewsByVisitor  = [];
        $eventsByVisitor = [];

        foreach ($views as $view) {
            $view = (object)$view;
            $viewsByVisitor[(int)$view->visitor_id][] = $view;
        }

        foreach ($events as $event) {
            if (empty($event['visitor_id'])) continue;
            $eventsByVisitor[(int)$event['visitor_id']][] = $event;
        }

        $visits = [];

        foreach ($visitors as $visitor) {
            if (!$visitor instanceof VisitorDecorator) continue;

            $id          = (int)$visitor->getId();
            $visitViews  = $viewsByVisitor[$id] ?? [];
            $visitEvents = $eventsByVisitor[$id] ?? [];

            $visits[] = self::buildVisit($visitor, $visitViews, $visitEvents, $id === (int)$currentId, $pageResolver);
        }

        usort($visits, function ($a, $b) {
            $cmp = strcmp((string)$b['sort'], (string)$a['sort']);
            return $cmp !== 0 ? $cmp : $b['id'] - $a['id'];
        });

        if (!empty($visits)) {
            $visits[0]['is_latest'] = true;
        }

        return $visits;
    }

    /**
     * One visit: its steps (newest first), start, end, duration and counts.
     *
     * Time on page is worked out in time order (gap to the next view), then the steps are reversed
     * so the list reads like the visits list: latest on top.
     */
    public static function buildVisit(VisitorDecorator $visitor, array $views, array $events, $isCurrent = false, $pageResolver = null)
    {
        usort($views, function ($a, $b) {
            return strcmp($a->date, $b->date);
        });

        usort($events, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        $steps     = [];
        $pageCount = count($views);
        // The visit's own view count; only its newest views may be loaded (see getVisitorsJourneys()).
        $hits      = max($pageCount, (int)$visitor->getHits(true));
        $truncated = $pageCount > 0 && $hits > $pageCount;

        foreach ($views as $i => $view) {
            $next = $views[$i + 1] ?? null;

            // Time on page = gap to the next view; the last page has none.
            $timeOnPage = null;
            if ($next) {
                $gap        = strtotime($next->date) - strtotime($view->date);
                $timeOnPage = $gap >= 0 ? $gap : null;
            }

            $steps[] = [
                'type'         => 'page',
                'date'         => $view->date,
                'page_id'      => (int)$view->page_id,
                'page'         => $pageResolver ? call_user_func($pageResolver, (int)$view->page_id) : null,
                'time_on_page' => $timeOnPage,
                // The entry page is only known when the first view was loaded.
                'is_entry'     => $i === 0 && !$truncated,
                'is_exit'      => $i === $pageCount - 1 && $pageCount > 1,
            ];
        }

        // Each event goes after the last page viewed at or before it.
        foreach ($events as $event) {
            $position = 0;

            foreach ($steps as $index => $step) {
                if (strcmp($step['date'], $event['date']) <= 0) {
                    $position = $index + 1;
                }
            }

            array_splice($steps, $position, 0, [array_merge($event, ['type' => 'event'])]);
        }

        $start = $pageCount && !$truncated ? $views[0]->date : ($visitor->getFirstView(true) ?: ($pageCount ? $views[0]->date : $visitor->getLastView(true)));
        $end   = $pageCount ? $views[$pageCount - 1]->date : $visitor->getLastView(true);

        $duration = null;
        if ($pageCount > 1) {
            $duration = max(0, strtotime($end) - strtotime($start));
        }

        return [
            'id'         => (int)$visitor->getId(),
            'visitor'    => $visitor,
            'is_current' => (bool)$isCurrent,
            'is_latest'  => false,
            'start'      => $start,
            'end'        => $end,
            // Visits are listed by last view, newest first (same order as the latest-visitor lists).
            'sort'       => (string)($end ?: ($start ?: '')),
            'duration'   => $duration,
            'views'      => $hits,
            // Fewer steps than views: the oldest views of this visit are not listed.
            'views_shown' => $pageCount,
            'truncated'  => $truncated,
            'events'     => count($events),
            'steps'      => array_reverse($steps),
        ];
    }

    /**
     * Totals for the profile card: visits, views, first and last seen.
     *
     * @param VisitorDecorator[] $visitors
     * @return array
     */
    public static function summarize(array $visitors)
    {
        $summary = [
            'visits'     => 0,
            'views'      => 0,
            'first_seen' => null,
            'last_seen'  => null,
        ];

        foreach ($visitors as $visitor) {
            if (!$visitor instanceof VisitorDecorator) continue;

            $summary['visits']++;
            $summary['views'] += (int)$visitor->getHits(true);

            $first = $visitor->getFirstView(true) ?: $visitor->getLastView(true);
            $last  = $visitor->getLastView(true);

            if ($first && ($summary['first_seen'] === null || strcmp($first, $summary['first_seen']) < 0)) {
                $summary['first_seen'] = $first;
            }

            if ($last && ($summary['last_seen'] === null || strcmp($last, $summary['last_seen']) > 0)) {
                $summary['last_seen'] = $last;
            }
        }

        return $summary;
    }

    /**
     * Event row (statistics_events) to a journey step. Returns null for rows that cannot be shown.
     *
     * @param object|array $row Row with visitor_id, date, event_name, event_data (JSON).
     * @return array|null visitor_id, date, name, label, target, host
     */
    public static function normalizeEvent($row)
    {
        $row = (array)$row;

        if (empty($row['date']) || empty($row['event_name'])) {
            return null;
        }

        // Already normalized (for example by a `wp_statistics_visitor_journey_events` callback).
        if (isset($row['label'])) {
            return array_merge(['visitor_id' => 0, 'name' => $row['event_name'], 'target' => '', 'host' => ''], $row);
        }

        $data = is_array($row['event_data'] ?? null) ? $row['event_data'] : json_decode((string)($row['event_data'] ?? ''), true);
        $data = is_array($data) ? $data : [];

        $target = isset($data['target_url']) ? (string)$data['target_url'] : '';
        $host   = $target ? (string)wp_parse_url($target, PHP_URL_HOST) : '';
        $host   = preg_replace('/^www\./i', '', $host);
        $text   = isset($data['element']['value']) ? trim(wp_strip_all_tags((string)$data['element']['value'])) : '';

        if (mb_strlen($text) > 80) {
            $text = mb_substr($text, 0, 79) . '…';
        }

        switch ($row['event_name']) {
            case 'file_download':
                $file = '';
                if (!empty($data['file_details']['file_name'])) {
                    $file = $data['file_details']['file_name'] . (!empty($data['file_details']['file_extension']) ? '.' . $data['file_details']['file_extension'] : '');
                } elseif ($target) {
                    $file = basename((string)wp_parse_url($target, PHP_URL_PATH));
                }

                /* translators: %s: File name. */
                $label = $file ? sprintf(__('Downloaded %s', 'wp-statistics'), $file) : __('Downloaded a file', 'wp-statistics');
                $kind  = 'download';
                break;

            case 'click':
                if ($text !== '') {
                    /* translators: %s: Link or button text. */
                    $label = sprintf(__('Clicked "%s"', 'wp-statistics'), $text);
                } elseif ($host) {
                    /* translators: %s: Domain. */
                    $label = sprintf(__('Clicked a link to %s', 'wp-statistics'), $host);
                } else {
                    $label = __('Clicked a link', 'wp-statistics');
                }
                $kind = 'click';
                break;

            default:
                $label = (string)$row['event_name'];
                $kind  = 'custom';
        }

        return [
            'visitor_id' => (int)($row['visitor_id'] ?? 0),
            'date'       => (string)$row['date'],
            'name'       => (string)$row['event_name'],
            'kind'       => $kind,
            'label'      => $label,
            'target'     => $target,
            'host'       => $host,
        ];
    }

    /**
     * Short duration: "15 s", "2 min 39 s", "1 h 5 min".
     *
     * @param int|null $seconds
     * @return string '' for null.
     */
    public static function formatDuration($seconds)
    {
        if ($seconds === null || $seconds === '') {
            return '';
        }

        $seconds = max(0, (int)$seconds);

        if ($seconds < 60) {
            /* translators: %s: Number of seconds. */
            return sprintf(__('%s s', 'wp-statistics'), number_format_i18n($seconds));
        }

        if ($seconds < 3600) {
            $minutes = intdiv($seconds, 60);
            $rest    = $seconds % 60;

            return $rest
                /* translators: 1: Minutes, 2: Seconds. */
                ? sprintf(__('%1$s min %2$s s', 'wp-statistics'), number_format_i18n($minutes), number_format_i18n($rest))
                /* translators: %s: Number of minutes. */
                : sprintf(__('%s min', 'wp-statistics'), number_format_i18n($minutes));
        }

        $hours   = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return $minutes
            /* translators: 1: Hours, 2: Minutes. */
            ? sprintf(__('%1$s h %2$s min', 'wp-statistics'), number_format_i18n($hours), number_format_i18n($minutes))
            /* translators: %s: Number of hours. */
            : sprintf(__('%s h', 'wp-statistics'), number_format_i18n($hours));
    }
}
