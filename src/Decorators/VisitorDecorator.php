<?php

namespace WP_Statistics\Decorators;

use WP_STATISTICS\IP;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_STATISTICS\Visitor;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Utils\Query;

class VisitorDecorator
{
    /**
     * @var mixed
     */
    private $visitor;

    /**
     * last_counter / last_view / last_page loaded by primeOnlineStatus() for rows whose query
     * did not select last_view. Null = not loaded, false = the visitor is not online.
     *
     * @var object|false|null
     */
    private $onlineRow = null;

    /**
     * Memoized getLastPage() result, so the pill tooltip and the journey cell share one lookup.
     *
     * @var array|null|false
     */
    private $lastPage = false;

    /**
     * VisitorDecorator constructor.
     * @param mixed $visitor
     */
    public function __construct($visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * Get the visitor's ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->visitor->ID ?? null;
    }

    /**
     * Returns the visitor's location.
     *
     * @return LocationDecorator
     */
    public function getLocation()
    {
        return new LocationDecorator($this->visitor);
    }

    /**
     * Get the visitor's browser.
     *
     * @return BrowserDecorator
     */
    public function getBrowser()
    {
        return new BrowserDecorator($this->visitor);
    }

    /**
     * Get the platform (operating system)
     *
     * @return OsDecorator
     */
    public function getOs()
    {
        return new OsDecorator($this->visitor);
    }

    /**
     * Get the device used by the visitor.
     *
     * @return DeviceDecorator
     */
    public function getDevice()
    {
        return new DeviceDecorator($this->visitor);
    }

    /**
     * Get the visitor's user agent string.
     *
     * @return string|null
     */
    public function getUserAgent()
    {
        return $this->visitor->UAString ?? null;
    }

    /**
     * Get the visitor's IP address.
     *
     * @return string|null
     */
    public function getIP()
    {
        return $this->isHashedIP() ? '#' . substr($this->visitor->ip, 6, 8) : $this->visitor->ip;
    }

    /**
     * Returns the raw IP address of the visitor.
     *
     * @return string The raw IP address of the visitor.
     */
    public function getRawIP()
    {
        return $this->visitor->ip;
    }

    /**
     * Is the visitor's IP hashed?
     *
     * @return string|null
     */
    public function isHashedIP()
    {
        return IP::IsHashIP($this->visitor->ip);
    }

    /**
     * Checks if the visitor's IP address is anonymized.
     *
     * @return bool True if the IP address is anonymized, false otherwise.
     */
    public function isIpAnonymized()
    {
        return IP::isIpAnonymized($this->visitor->ip);
    }

    /**
     * Whether the stored IP may be shown as a raw IP in reports.
     *
     * Only when "Hash IP Addresses" is off and the stored value really is an IP. While hashing is on,
     * no raw IP is shown anywhere, also not for rows saved before hashing was switched on (those still
     * hold the raw IP until Optimization > "Hash IP Addresses" converts them).
     *
     * @return bool
     */
    public function isIpVisible()
    {
        $ip = $this->visitor->ip ?? '';

        return !Option::get('hash_ips') && !empty($ip) && !$this->isHashedIP();
    }

    /**
     * The logged-in user as reports show it: only while "Track Logged-In User Activity" (visitors_log) is on.
     *
     * @return UserDecorator|null
     */
    public function getVisibleUser()
    {
        return Option::get('visitors_log') ? $this->getUser() : null;
    }

    /**
     * Name of the visitor in titles and headers, following the same rule as the visitor card:
     * the user name (user tracking on), else the IP (when visible), else the short hash, else "Anonymous Visitor".
     *
     * @return string
     */
    public function getDisplayLabel()
    {
        $user = $this->getVisibleUser();
        if ($user) {
            return (string)$user->getDisplayName();
        }

        if ($this->isIpVisible() || $this->isHashedIP()) {
            return (string)$this->getIP();
        }

        return __('Anonymous Visitor', 'wp-statistics');
    }

    /**
     * Checks if the visitor is anonymous.
     *
     * @return bool
     */
    public function isAnonymous()
    {
        return empty($this->getUser()) && ($this->isHashedIP() || $this->isIpAnonymized());
    }

    /**
     * Get the number of hits the visitor has made.
     *
     * @param $raw
     * @return int|null
     */
    public function getHits($raw = false)
    {
        if (!isset($this->visitor->hits)) return 0;

        $hits = intval($this->visitor->hits);

        return $raw ? $hits : number_format_i18n($hits);
    }

    /**
     * Get the last counter value recorded for the visitor.
     *
     * @return int|null
     */
    public function getLastCounter()
    {
        return !empty($this->visitor->last_counter) ? DateTime::format($this->visitor->last_counter, [
            'include_time' => true,
            'exclude_year' => true,
            'separator'    => ', '
        ]) : null;
    }

    /**
     * Get the visitor's referral information.
     *
     * @return ReferralDecorator
     */
    public function getReferral()
    {
        return new ReferralDecorator($this->visitor);
    }

    /**
     * Checks whether the visitor is a logged-in user.
     *
     * @return bool True if the visitor is logged in, false otherwise.
     */
    public function isLoggedInUser()
    {
        return $this->getUser() !== null;
    }

    /**
     * Get the visitor's user object (if logged in).
     *
     * @return UserDecorator
     */
    public function getUser()
    {
        if ($this->getUserId()) {
            $user = new UserDecorator($this->getUserId());

            if ($user->exists()) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Get user ID
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->visitor->user_id;
    }

    /**
     * Retrieves the first view time of the visitor.
     *
     * @return int|null The time of the first view, or null if not available.
     */
    public function getFirstView($raw = false)
    {
        if ($raw) {
            return $this->visitor->first_view ?? null;
        }

        return !empty($this->visitor->first_view) ? DateTime::format($this->visitor->first_view, [
            'include_time' => true,
            'exclude_year' => true,
            'separator'    => ', '
        ]) : null;
    }

    /**
     * Retrieves the first page viewed by the visitor.
     *
     * @return string|null The first page viewed by the visitor, or null if not available.
     */
    public function getFirstPage()
    {
        return !empty($this->visitor->first_page) ? Visitor::get_page_by_id($this->visitor->first_page) : null;
    }

    /**
     * Retrieves the last view time of the visitor.
     *
     * @param bool $raw Whether return raw value or formatted.
     * @return string The time of the last view, or null if not available.
     */
    public function getLastView($raw = false)
    {
        // Get date from last_view (DateTime), if not set use last_counter (Date)
        $date = $this->visitor->last_view ?? $this->visitor->last_counter;

        if ($raw) {
            return $date;
        }

        $date = date_i18n(Helper::getDefaultDateFormat(true, true, false, ', '), strtotime($date));

        return $date ?? null;
    }

    /**
     * Last view in a short one-line form for narrow widgets: "Nov 22, 10:15 am"
     * (short month, no year when it is the current year, site date and time formats).
     *
     * @return string|null
     */
    public function getLastViewShort()
    {
        $date = $this->getLastView(true);

        if (empty($date)) {
            return null;
        }

        $timestamp = strtotime($date);
        $format    = DateTime::getDateFormat();

        if (date_i18n('Y', $timestamp) === DateTime::get('now', 'Y')) {
            $format = preg_replace('/(,\s?Y|Y\s?,|Y[, \/-]?|[, \/-]?Y)/i', '', $format);
        }

        $format = str_replace('F', 'M', trim($format, ' ,')) . ', ' . DateTime::getTimeFormat();

        return date_i18n($format, $timestamp);
    }

    /**
     * Number of visitor rows (visit days) merged into this one, e.g. by
     * MetaboxDataProvider::getTopVisitorsGroupedData(). 1 for a plain row.
     *
     * @return int
     */
    public function getVisits()
    {
        return isset($this->visitor->visits) ? max(1, intval($this->visitor->visits)) : 1;
    }

    /**
     * Last view as one short token for compact lists (WP Dashboard widgets):
     * the time ("7:24 am") when it was today, otherwise the date ("Nov 19", with the
     * year only when it is not the current year). Site date and time formats.
     *
     * @return string|null
     */
    public function getLastViewCompact()
    {
        $date = $this->getLastView(true);

        if (empty($date)) {
            return null;
        }

        $timestamp = strtotime($date);

        // Today, and the row has a time part (last_counter alone is a plain date).
        if (gmdate('Y-m-d', $timestamp) === DateTime::get() && strlen((string) $date) > 10) {
            return date_i18n(DateTime::getTimeFormat(), $timestamp);
        }

        $format = DateTime::getDateFormat();

        if (gmdate('Y', $timestamp) === DateTime::get('now', 'Y')) {
            $format = preg_replace('/(,\s?Y|Y\s?,|Y[, \/-]?|[, \/-]?Y)/i', '', $format);
        }

        $format = str_replace('F', 'M', trim($format, ' ,./-'));

        return date_i18n($format, $timestamp);
    }

    /**
     * Last view for the shared visitor table (views/components/tables/visitor-table.php),
     * as the parts of one short-date rule. See self::compactDateParts().
     *
     * @return array|null ['wide', 'narrow', 'full', 'iso'], or null without a date.
     */
    public function getLastViewParts()
    {
        return self::compactDateParts($this->getLastView(true));
    }

    /**
     * Page view time (single post visitor lists) as short-date parts. See self::compactDateParts().
     *
     * @return array|null
     */
    public function getPageViewParts()
    {
        return self::compactDateParts($this->visitor->page_view ?? null);
    }

    /**
     * Whether the visit entered and left on the same page record (one page seen).
     *
     * @return bool
     */
    public function hasSameEntryAndExit()
    {
        return !empty($this->visitor->first_page) && !empty($this->visitor->last_page)
            && (string)$this->visitor->first_page === (string)$this->visitor->last_page;
    }

    /**
     * One short-date rule for visitor tables, in the site's date and time formats
     * ("F" becomes "M"; the year is dropped for the current year):
     *  - today:     "4:52 pm" (wide and narrow)
     *  - yesterday: "Nov 21, 4:52 pm" wide, "Yesterday" narrow
     *  - this year: "Nov 19, 7:41 pm" wide, "Nov 19" narrow
     *  - older:     "Nov 19, 2025, 7:41 pm" wide, "Nov 19, 2025" narrow
     * A plain date without a time ("2026-11-19") gives the date only ("Today" for today).
     *
     * @param string      $date Stored date or datetime (site time).
     * @param string|null $today Today as Y-m-d (site time zone); for tests.
     * @return array|null ['wide' => string, 'narrow' => string, 'full' => string, 'iso' => string]
     */
    public static function compactDateParts($date, $today = null)
    {
        if (empty($date) || ($timestamp = strtotime((string)$date)) === false) {
            return null;
        }

        $today     = $today ?: DateTime::get();
        $hasTime   = strlen(trim((string)$date)) > 10;
        $day       = gmdate('Y-m-d', $timestamp);
        $yesterday = gmdate('Y-m-d', strtotime($today . ' -1 day'));
        $thisYear  = gmdate('Y', $timestamp) === substr($today, 0, 4);
        $time      = $hasTime ? date_i18n(DateTime::getTimeFormat(), $timestamp) : '';
        $short     = date_i18n(self::shortDateFormat(!$thisYear), $timestamp);
        $join      = function ($date, $time) {
            /* translators: 1: short date ("Nov 19"), 2: time ("7:41 pm"). */
            return $time === '' ? $date : sprintf(_x('%1$s, %2$s', 'short date and time', 'wp-statistics'), $date, $time);
        };

        if ($day === $today) {
            $wide   = $hasTime ? $time : __('Today', 'wp-statistics');
            $narrow = $wide;
        } else {
            $wide   = $join($short, $time);
            $narrow = $day === $yesterday ? __('Yesterday', 'wp-statistics') : $short;
        }

        return [
            'wide'   => $wide,
            'narrow' => $narrow,
            'full'   => $join(date_i18n(DateTime::getDateFormat(), $timestamp), $time),
            'iso'    => gmdate($hasTime ? 'Y-m-d\TH:i' : 'Y-m-d', $timestamp),
        ];
    }

    /**
     * The site date format made short: "F" (November) becomes "M" (Nov), and the year
     * goes unless asked for. "F j, Y" gives "M j"; "j. F Y" gives "j. M"; "d/m/Y" gives "d/m".
     *
     * @param bool $withYear
     * @return string
     */
    public static function shortDateFormat($withYear = false)
    {
        $format = DateTime::getDateFormat();

        if (!$withYear) {
            $format = preg_replace('/(,\s?Y|Y\s?,|Y[, \/.-]?|[, \/.-]?Y)/i', '', $format);
        }

        $format = str_replace('F', 'M', trim($format, ' ,/-'));

        return $format !== '' ? $format : ($withYear ? 'M j, Y' : 'M j');
    }

    /**
     * Retrieves the last page viewed by the visitor.
     *
     * @return string|null The last page viewed by the visitor, or null if not available.
     */
    public function getLastPage()
    {
        if ($this->lastPage === false) {
            $lastPage = $this->visitor->last_page ?? ($this->onlineRow->last_page ?? null);

            $this->lastPage = $lastPage ? Visitor::get_page_by_id($lastPage) : null;
        }

        return $this->lastPage;
    }

    /**
     * Whether the visitor is online now: the row is from today and its last view is inside the
     * online window (OnlineModel, filter `wp_statistics_online_visitors_timeframe`, 5 minutes by default).
     * Uses the row's own columns; rows from a query without last_view need primeOnlineStatus() first,
     * otherwise they count as offline (no query per row).
     *
     * @param array|null $timeframe Optional ['from' => ..., 'to' => ...] window (OnlineModel::getTimeframe()),
     *                              so a table can compute it once for all its rows.
     * @return bool
     */
    public function isOnline($timeframe = null)
    {
        if (property_exists($this->visitor, 'last_view')) {
            $row = $this->visitor;
        } elseif (is_object($this->onlineRow)) {
            $row = $this->onlineRow;
        } else {
            return false;
        }

        if (empty($row->last_view)) {
            return false;
        }

        if (!empty($row->last_counter) && substr((string)$row->last_counter, 0, 10) !== DateTime::get()) {
            return false;
        }

        if (!is_array($timeframe) || empty($timeframe['from']) || empty($timeframe['to'])) {
            $timeframe = (new OnlineModel())->getTimeframe();
        }

        $lastView = (string)$row->last_view;

        return $lastView >= $timeframe['from'] && $lastView <= $timeframe['to'];
    }

    /**
     * Loads the online columns, in one query, for the rows whose list query did not select last_view
     * (e.g. add-on tables), so isOnline() works for them without a query per row.
     * Rows that already carry last_view are skipped; when all do, nothing runs.
     *
     * @param VisitorDecorator[] $visitors
     * @return void
     */
    public static function primeOnlineStatus($visitors)
    {
        $pending = [];
        foreach ((array)$visitors as $visitor) {
            if (!$visitor instanceof self || !is_object($visitor->visitor) || property_exists($visitor->visitor, 'last_view') || $visitor->onlineRow !== null) {
                continue;
            }

            $id = intval($visitor->getId());
            if ($id > 0) {
                $pending[$id][] = $visitor;
            }
        }

        if (empty($pending)) {
            return;
        }

        $timeframe = (new OnlineModel())->getTimeframe();
        $rows      = Query::select(['ID', 'last_counter', 'last_view', 'last_page'])
            ->from('visitor')
            ->where('ID', 'IN', array_keys($pending))
            ->where('last_counter', '=', DateTime::get())
            ->whereDate('last_view', $timeframe)
            ->getAll();

        $online = [];
        foreach ((array)$rows as $row) {
            $online[intval($row->ID)] = $row;
        }

        foreach ($pending as $id => $list) {
            foreach ($list as $visitor) {
                $visitor->onlineRow = $online[$id] ?? false;
            }
        }
    }

    /**
     * Hover and screen reader text of the online marker: "Online now · on <last page title>",
     * or "Online now" when the page is not known.
     *
     * @return string Plain text (not escaped).
     */
    public function getOnlineLabel()
    {
        $page  = $this->getLastPage();
        $title = !empty($page['title']) ? wp_strip_all_tags((string)$page['title']) : '';

        if ($title === '') {
            return __('Online now', 'wp-statistics');
        }

        /* translators: %s: Title of the page the visitor is on now. */
        return sprintf(__('Online now · on %s', 'wp-statistics'), $title);
    }

    /**
     * Retrieves the date a certain page has been viewed
     *
     * @return string|null The last page viewed by the visitor, or null if not available.
     */
    public function getPageView()
    {
        return !empty($this->visitor->page_view) ? DateTime::format($this->visitor->page_view, [
            'include_time' => true,
            'exclude_year' => true,
            'separator'    => ', '
        ]) : null;
    }

    /**
     * Retrieves the online time of the visitor.
     *
     * @deprecated Since online users aren't tracked real-time, this method gives inaccurate results and shouldn't be used.
     * @return string|null The online time in 'H:i:s' format, or null if not available.
     */
    public function getOnlineTime()
    {
        if (isset($this->visitor->last_view)) {
            $lastActivity = strtotime($this->visitor->last_view);
            $now          = strtotime(DateTime::get('now', 'Y-m-d H:i:s'));

            return date_i18n('H:i:s', $now - $lastActivity);
        }

        return null;
    }
}
