<?php

namespace WP_Statistics\Service\Admin\Notice;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Admin\Notice\Notices\NoticeInterface;

/**
 * Global Notice Manager.
 *
 * Manages admin notices for both React and non-React pages.
 * This is the central handler for all WP Statistics notices.
 *
 * @since 15.0.0
 */
class NoticeManager
{
    /**
     * Option key for dismissed notices (same as v14 for migration compatibility).
     */
    private const DISMISSED_OPTION = 'wp_statistics_dismissed_notices';

    /**
     * Registered notices.
     *
     * @var NoticeItem[]
     */
    private static array $notices = [];

    /**
     * Registered notice generators.
     *
     * @var NoticeInterface[]
     */
    private static array $generators = [];

    /**
     * Cached dismissed notice IDs.
     *
     * @var array|null
     */
    private static ?array $dismissedCache = null;

    /**
     * Whether the manager has been initialized.
     *
     * @var bool
     */
    private static bool $initialized = false;

    /**
     * Initialize the notice manager.
     *
     * Called early to register hooks for non-React pages.
     *
     * @return void
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        // Register AJAX handler for dismissing notices (admin-only)
        Ajax::register('dismiss_notice', [__CLASS__, 'handleDismissAjax'], false);

        // Register admin notices for non-React pages
        add_action('admin_notices', [__CLASS__, 'renderWordPressNotices'], 20);

        self::$initialized = true;
    }

    /**
     * Register a notice generator.
     *
     * Notice generators are classes that implement NoticeInterface
     * and can dynamically generate notices based on conditions.
     *
     * @param NoticeInterface $generator Notice generator instance.
     * @return void
     */
    public static function registerGenerator(NoticeInterface $generator): void
    {
        self::$generators[] = $generator;
    }

    /**
     * Add a notice to be displayed.
     *
     * @param NoticeItem $notice Notice to add.
     * @return void
     */
    public static function add(NoticeItem $notice): void
    {
        // Skip if already dismissed
        if (self::isDismissed($notice->id)) {
            return;
        }

        self::$notices[$notice->id] = $notice;
    }

    /**
     * Add multiple notices.
     *
     * @param NoticeItem[] $notices Notices to add.
     * @return void
     */
    public static function addMany(array $notices): void
    {
        foreach ($notices as $notice) {
            self::add($notice);
        }
    }

    /**
     * Get all active (non-dismissed) notices.
     *
     * Includes notices from registered generators.
     *
     * @return NoticeItem[]
     */
    public static function getActive(): array
    {
        // Collect notices from generators
        foreach (self::$generators as $generator) {
            if ($generator->shouldRun()) {
                self::addMany($generator->getNotices());
            }
        }

        // Filter out dismissed notices
        $active = array_filter(
            self::$notices,
            fn(NoticeItem $notice) => !self::isDismissed($notice->id)
        );

        // Sort by priority (lower = higher priority)
        uasort($active, fn(NoticeItem $a, NoticeItem $b) => $a->priority <=> $b->priority);

        return $active;
    }

    /**
     * Get a specific notice by ID.
     *
     * @param string $id Notice ID.
     * @return NoticeItem|null
     */
    public static function get(string $id): ?NoticeItem
    {
        return self::$notices[$id] ?? null;
    }

    /**
     * Remove a notice by ID.
     *
     * @param string $id Notice ID.
     * @return void
     */
    public static function remove(string $id): void
    {
        unset(self::$notices[$id]);
    }

    /**
     * Check if a notice is dismissed.
     *
     * @param string $id Notice ID.
     * @return bool
     */
    public static function isDismissed(string $id): bool
    {
        $dismissed = self::getDismissedIds();
        return in_array($id, $dismissed, true);
    }

    /**
     * Dismiss a notice by ID.
     *
     * @param string $id Notice ID.
     * @return void
     */
    public static function dismiss(string $id): void
    {
        $dismissed = self::getDismissedIds();

        if (!in_array($id, $dismissed, true)) {
            $dismissed[] = $id;
            update_option(self::DISMISSED_OPTION, $dismissed, false);
            self::$dismissedCache = $dismissed;
        }

        // Remove from active notices
        self::remove($id);
    }

    /**
     * Undismiss a notice (restore it).
     *
     * @param string $id Notice ID.
     * @return void
     */
    public static function undismiss(string $id): void
    {
        $dismissed = self::getDismissedIds();
        $dismissed = array_filter($dismissed, fn($dismissedId) => $dismissedId !== $id);

        update_option(self::DISMISSED_OPTION, array_values($dismissed), false);
        self::$dismissedCache = $dismissed;
    }

    /**
     * Get all dismissed notice IDs.
     *
     * @return array
     */
    public static function getDismissedIds(): array
    {
        if (self::$dismissedCache === null) {
            self::$dismissedCache = get_option(self::DISMISSED_OPTION, []);

            if (!is_array(self::$dismissedCache)) {
                self::$dismissedCache = [];
            }
        }

        return self::$dismissedCache;
    }

    /**
     * Clear all dismissed notices.
     *
     * @return void
     */
    public static function clearDismissed(): void
    {
        delete_option(self::DISMISSED_OPTION);
        self::$dismissedCache = [];
    }

    /**
     * Render notices for non-React WordPress admin pages.
     *
     * Hooked to admin_notices action.
     *
     * @return void
     */
    public static function renderWordPressNotices(): void
    {
        // Skip on React pages (they handle notices differently)
        if (self::isReactPage()) {
            return;
        }

        // Only show on WP Statistics pages or WordPress dashboard
        if (!self::shouldShowNotices()) {
            return;
        }

        $notices = self::getActive();

        foreach ($notices as $notice) {
            self::renderNotice($notice);
        }
    }

    /**
     * Render a single notice as WordPress admin notice HTML.
     *
     * @param NoticeItem $notice Notice to render.
     * @return void
     */
    private static function renderNotice(NoticeItem $notice): void
    {
        $classes = ['notice', 'notice-' . $notice->type];

        if ($notice->dismissible) {
            $classes[] = 'is-dismissible';
        }

        $classAttr = esc_attr(implode(' ', $classes));
        $dataAttr  = $notice->dismissible ? sprintf('data-notice-id="%s"', esc_attr($notice->id)) : '';

        ?>
        <div class="<?php echo $classAttr; ?>" <?php echo $dataAttr; ?>>
            <p>
                <strong><?php esc_html_e('WP Statistics:', 'wp-statistics'); ?></strong>
                <?php echo wp_kses_post($notice->message); ?>
            </p>
            <?php if ($notice->actionUrl || $notice->helpUrl): ?>
                <p>
                    <?php if ($notice->actionUrl): ?>
                        <a href="<?php echo esc_url($notice->actionUrl); ?>" class="button button-primary">
                            <?php echo esc_html($notice->actionLabel ?: __('View', 'wp-statistics')); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($notice->helpUrl): ?>
                        <a href="<?php echo esc_url($notice->helpUrl); ?>" target="_blank" rel="noopener" class="button">
                            <?php esc_html_e('Learn More', 'wp-statistics'); ?>
                        </a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        <?php

        // Output inline script for dismissal handling (once per page)
        static $scriptRendered = false;
        if ($notice->dismissible && !$scriptRendered) {
            self::renderDismissScript();
            $scriptRendered = true;
        }
    }

    /**
     * Render the JavaScript for handling notice dismissals.
     *
     * @return void
     */
    private static function renderDismissScript(): void
    {
        ?>
        <script>
            jQuery(function($) {
                $(document).on('click', '.notice[data-notice-id] .notice-dismiss', function() {
                    var noticeId = $(this).closest('.notice').data('notice-id');
                    if (noticeId) {
                        $.post(ajaxurl, {
                            action: 'wp_statistics_dismiss_notice',
                            notice_id: noticeId,
                            _wpnonce: '<?php echo esc_js(wp_create_nonce('wp_statistics_dismiss_notice')); ?>'
                        });
                    }
                });
            });
        </script>
        <?php
    }

    /**
     * Handle AJAX request to dismiss a notice.
     *
     * @return void
     */
    public static function handleDismissAjax(): void
    {
        check_ajax_referer('wp_statistics_dismiss_notice', '_wpnonce');

        $noticeId = isset($_POST['notice_id']) ? sanitize_key($_POST['notice_id']) : '';

        if (empty($noticeId)) {
            wp_send_json_error(['message' => __('Notice ID is required.', 'wp-statistics')]);
        }

        self::dismiss($noticeId);

        wp_send_json_success(['message' => __('Notice dismissed.', 'wp-statistics')]);
    }

    /**
     * Get notices data formatted for React pages.
     *
     * @return array
     */
    public static function getDataForReact(): array
    {
        $notices = self::getActive();

        return array_values(array_map(
            fn(NoticeItem $notice) => $notice->toArray(),
            $notices
        ));
    }

    /**
     * Check if current page is a React-rendered WP Statistics page.
     *
     * @return bool
     */
    private static function isReactPage(): bool
    {
        if (!is_admin()) {
            return false;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return false;
        }

        // Check if it's a WP Statistics page that uses React
        // React pages have their admin_notices removed by ReactHandler
        $reactPages = [
            'toplevel_page_wps_overview_page',
            'statistics_page_wps_visitors_page',
            'statistics_page_wps_pages_page',
            'statistics_page_wps_referrals_page',
            'statistics_page_wps_geographic_page',
            'statistics_page_wps_devices_page',
            'statistics_page_wps_content_analytics_page',
            'statistics_page_wps_author_analytics_page',
            'statistics_page_wps_category_analytics_page',
            'statistics_page_wps_settings_page',
            'statistics_page_wps_tools_page',
            'statistics_page_wps_privacy_audit_page',
        ];

        return in_array($screen->id, $reactPages, true);
    }

    /**
     * Check if notices should be shown on current page.
     *
     * @return bool
     */
    private static function shouldShowNotices(): bool
    {
        if (!is_admin()) {
            return false;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return false;
        }

        // Always show on WordPress dashboard
        if ($screen->id === 'dashboard') {
            return true;
        }

        // Show on WP Statistics pages
        if (strpos($screen->id, 'wps_') !== false || strpos($screen->id, 'wp-statistics') !== false) {
            return true;
        }

        // Show on plugins page
        if ($screen->id === 'plugins') {
            return true;
        }

        return false;
    }

    /**
     * Reset the manager state (useful for testing).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$notices        = [];
        self::$generators     = [];
        self::$dismissedCache = null;
        self::$initialized    = false;
    }
}
