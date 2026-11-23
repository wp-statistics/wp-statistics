<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\User;
use WP_STATISTICS\UserOnline;
use WP_Statistics\Service\Admin\Metabox\MetaboxHelper;
use WP_Statistics\Utils\Request;

/**
 * Visitors card (Overview page and WP Dashboard).
 *
 * One card for the three visitor lists that were separate widgets before 14.17
 * (Latest Visitors, Most Active Visitors, Currently Online). A switch in the card header
 * picks the list: "Latest" (newest first, all time), "Most active" (most views in the
 * chosen date range) and "Online now" (only when the Online feature is on). All three use
 * the shared visitor table; only the rows and the sorted column change.
 *
 * Each list loads over the metabox AJAX call with a `segment` parameter. The last list a
 * user picked is kept in user meta, per screen (Overview and WP Dashboard).
 */
class Visitors extends BaseMetabox
{
    const SEGMENT_LATEST = 'latest';
    const SEGMENT_ACTIVE = 'active';
    const SEGMENT_ONLINE = 'online';

    /**
     * User meta: last segment per screen, ['overview' => 'latest', 'dashboard' => 'online'].
     */
    const SEGMENT_META_KEY = 'wp_statistics_visitors_segment';

    protected $key = 'visitors';
    protected $context = 'normal';

    /**
     * @var string|null Segment of this request (resolved once).
     */
    private $segment = null;

    /**
     * @var int|null Online count of this request (fetched once).
     */
    private $onlineCount = null;

    public function getName()
    {
        return esc_html__('Visitors', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Latest visitors, the most active in the period, and who is online now.', 'wp-statistics');
    }

    /**
     * Segments in switch order. "Online now" only while the Online feature is on.
     *
     * @param bool|null $onlineActive Defaults to UserOnline::active().
     *
     * @return string[]
     */
    public static function getAvailableSegments($onlineActive = null)
    {
        $onlineActive = $onlineActive === null ? UserOnline::active() : (bool)$onlineActive;
        $segments     = [self::SEGMENT_LATEST, self::SEGMENT_ACTIVE];

        if ($onlineActive) {
            $segments[] = self::SEGMENT_ONLINE;
        }

        return $segments;
    }

    /**
     * The segment to show: the requested one, else the saved one, else Latest.
     * A segment that is not available (Online with the feature off) falls back the same way.
     *
     * @param string|null $requested
     * @param string|null $saved
     * @param string[]    $available
     *
     * @return string
     */
    public static function resolveSegment($requested, $saved, $available)
    {
        foreach ([$requested, $saved] as $segment) {
            if (is_string($segment) && in_array($segment, $available, true)) {
                return $segment;
            }
        }

        return self::SEGMENT_LATEST;
    }

    /**
     * 'dashboard' on the WP Dashboard, else 'overview'.
     *
     * @return string
     */
    public static function getScreenContext()
    {
        return MetaboxHelper::isDashboardContext() ? 'dashboard' : 'overview';
    }

    /**
     * The segment a user last picked on a screen, or '' when none.
     *
     * @param int    $userId
     * @param string $context 'overview' or 'dashboard'.
     *
     * @return string
     */
    public static function getSavedSegment($userId, $context)
    {
        $saved = get_user_meta($userId, self::SEGMENT_META_KEY, true);

        return is_array($saved) && !empty($saved[$context]) && is_string($saved[$context]) ? $saved[$context] : '';
    }

    /**
     * Remember the segment a user picked on a screen.
     *
     * @param int    $userId
     * @param string $context 'overview' or 'dashboard'.
     * @param string $segment
     *
     * @return void
     */
    public static function saveSegment($userId, $context, $segment)
    {
        if (!$userId || !in_array($segment, [self::SEGMENT_LATEST, self::SEGMENT_ACTIVE, self::SEGMENT_ONLINE], true)) {
            return;
        }

        $saved = get_user_meta($userId, self::SEGMENT_META_KEY, true);
        $saved = is_array($saved) ? $saved : [];

        if (isset($saved[$context]) && $saved[$context] === $segment) {
            return;
        }

        $saved[$context] = $segment;
        update_user_meta($userId, self::SEGMENT_META_KEY, $saved);
    }

    /**
     * The segment of this request. A valid `segment` parameter is saved as the user's choice.
     *
     * @return string
     */
    public function getSegment()
    {
        if ($this->segment !== null) {
            return $this->segment;
        }

        $userId    = get_current_user_id();
        $context   = self::getScreenContext();
        $requested = Request::has('segment') ? Request::get('segment') : null;
        $available = self::getAvailableSegments();

        $this->segment = self::resolveSegment($requested, self::getSavedSegment($userId, $context), $available);

        if ($requested === $this->segment) {
            self::saveSegment($userId, $context, $this->segment);
        }

        return $this->segment;
    }

    /**
     * Online now, for the badge. Null when the Online feature is off.
     *
     * @return int|null
     */
    public function getOnlineCount()
    {
        if (!UserOnline::active()) {
            return null;
        }

        if ($this->onlineCount === null) {
            $this->onlineCount = $this->dataProvider->getOnlineVisitorsCount();
        }

        return $this->onlineCount;
    }

    /**
     * Switch labels, in order.
     *
     * @return array[] [['key' => 'latest', 'label' => 'Latest'], ...]
     */
    public static function getSegmentLabels($available)
    {
        $labels = [
            self::SEGMENT_LATEST => esc_html__('Latest', 'wp-statistics'),
            self::SEGMENT_ACTIVE => esc_html__('Most active', 'wp-statistics'),
            self::SEGMENT_ONLINE => esc_html__('Online now', 'wp-statistics'),
        ];

        $segments = [];
        foreach ($available as $key) {
            $segments[] = ['key' => $key, 'label' => $labels[$key]];
        }

        return $segments;
    }

    /**
     * Footer link of a segment.
     *
     * @param string $segment
     * @param bool   $isDashboard
     *
     * @return array ['link' => string, 'title' => string]
     */
    public static function getFooterLink($segment, $isDashboard)
    {
        if ($segment === self::SEGMENT_ACTIVE) {
            return [
                'link'  => Menus::admin_url('visitors', ['tab' => 'top-visitors']),
                'title' => $isDashboard ? esc_html__('View all active visitors', 'wp-statistics') : esc_html__('View Most Active Visitors', 'wp-statistics'),
            ];
        }

        if ($segment === self::SEGMENT_ONLINE) {
            // WP Dashboard: the live page when Real-Time is active, else Online Visitors.
            if ($isDashboard && Helper::isAddOnActive('realtime-stats')) {
                return [
                    'link'  => admin_url('admin.php?page=wp_statistics_realtime_stats'),
                    'title' => esc_html__('Open Real-Time', 'wp-statistics'),
                ];
            }

            return [
                'link'  => Menus::admin_url('visitors', ['tab' => 'online']),
                'title' => $isDashboard ? esc_html__('View all online visitors', 'wp-statistics') : esc_html__('View Online Visitors', 'wp-statistics'),
            ];
        }

        return [
            'link'  => Menus::admin_url('visitors', ['tab' => 'visitors']),
            'title' => $isDashboard ? esc_html__('View all visitors', 'wp-statistics') : esc_html__('View Latest Visitors', 'wp-statistics'),
        ];
    }

    public function getOptions()
    {
        $segment   = $this->getSegment();
        $available = self::getAvailableSegments();

        return [
            // Date range only on Most active; Latest and Online are not tied to a range.
            'datepicker' => $segment === self::SEGMENT_ACTIVE,
            'button'     => View::load('metabox/action-button', self::getFooterLink($segment, MetaboxHelper::isDashboardContext()), true),
            'segment'    => $segment,
            'segments'   => self::getSegmentLabels($available),
            'online'     => $this->getOnlineCount(),
            'switch'     => [
                'label' => esc_html__('Visitors', 'wp-statistics'),
            ],
        ];
    }

    public function getData()
    {
        $segment     = $this->getSegment();
        $isDashboard = MetaboxHelper::isDashboardContext();
        $limit       = $isDashboard ? MetaboxHelper::DASHBOARD_ROWS : 10;
        $filters     = $segment === self::SEGMENT_ACTIVE ? $this->getFilters() : [];
        $total       = null;

        if ($segment === self::SEGMENT_ACTIVE) {
            // WP Dashboard: one row per logged-in user ("Admin · 3 visits").
            $data = $isDashboard
                ? $this->dataProvider->getTopVisitorsGroupedData($filters, $limit)
                : $this->dataProvider->getTopVisitorsData($filters, $limit);
        } elseif ($segment === self::SEGMENT_ONLINE) {
            $online = $this->dataProvider->getOnlineVisitorsData([], $limit);
            $data   = $online['visitors'];
            $total  = $online['total'];

            // The list query counts too: keep the badge in step with the rows.
            $this->onlineCount = intval($total);
        } else {
            $data = $this->dataProvider->getLatestVisitorsData(['ignore_date' => true], $limit);
        }

        return View::load('metabox/visitors', [
            'segment'   => $segment,
            'data'      => $data,
            'filters'   => $filters,
            'dashboard' => $isDashboard,
            'label'     => self::getSegmentLabels([$segment])[0]['label'],
        ], true);
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }

    /**
     * Also registers the "remember segment" call: switching to a list that is already
     * loaded makes no data request, so the choice is saved with this small one.
     */
    public function register()
    {
        parent::register();

        if (User::Access('read')) {
            Ajax::register('visitors_segment_save', [$this, 'saveSegmentResponse'], false);
        }
    }

    /**
     * AJAX: save the segment a user picked (wp_statistics_visitors_segment_save).
     *
     * @return void
     */
    public function saveSegmentResponse()
    {
        if (!wp_verify_nonce(Request::get('wps_nonce'), 'wp_rest') || !User::Access('read')) {
            wp_send_json_error(['message' => esc_html__('Unauthorized.', 'wp-statistics')], 403);
        }

        $segment = Request::get('segment');

        if (!in_array($segment, self::getAvailableSegments(), true)) {
            wp_send_json_error(['message' => esc_html__('Invalid request.', 'wp-statistics')], 400);
        }

        self::saveSegment(get_current_user_id(), self::getScreenContext(), $segment);

        wp_send_json_success(['segment' => $segment]);
    }
}
