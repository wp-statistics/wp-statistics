<?php

use WP_STATISTICS\User;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\Visitors;
use WP_Statistics\Service\Admin\Metabox\VisitorsCardMigration;

/**
 * 14.17: Latest Visitors, Most Active Visitors and Currently Online became one Visitors card.
 * Each user's order, hidden and collapsed cards and the Most Active date range carry over.
 */
class Test_VisitorsCardMigration extends WP_UnitTestCase
{
    const OVERVIEW = 'toplevel_page_wps_overview_page';

    const RECENT = 'wp-statistics-recent-widget';
    const TOP    = 'wp-statistics-top-visitors-widget';
    const ONLINE = 'wp-statistics-useronline-widget';
    const NEW_ID = 'wp-statistics-visitors-widget';

    private $userId;

    public function setUp(): void
    {
        parent::setUp();
        $this->userId = self::factory()->user->create(['role' => 'administrator']);
    }

    private function meta($key)
    {
        return get_user_meta($this->userId, $key, true);
    }

    private function set($key, $value)
    {
        update_user_meta($this->userId, $key, $value);
    }

    public function test_screens_are_overview_and_dashboard()
    {
        $this->assertSame([self::OVERVIEW => 'overview', 'dashboard' => 'dashboard'], VisitorsCardMigration::getScreens());
    }

    public function test_new_card_takes_the_place_of_the_first_old_card()
    {
        $this->set('meta-box-order_' . self::OVERVIEW, [
            'side'   => 'wp-statistics-summary-widget,' . self::ONLINE . ',wp-statistics-browsers-widget',
            'normal' => 'wp-statistics-hits-widget,' . self::TOP . ',wp-statistics-pages-widget,' . self::RECENT,
        ]);

        VisitorsCardMigration::migrateUser($this->userId, true);

        $this->assertSame([
            'side'   => 'wp-statistics-summary-widget,' . self::NEW_ID . ',wp-statistics-browsers-widget',
            'normal' => 'wp-statistics-hits-widget,wp-statistics-pages-widget',
        ], $this->meta('meta-box-order_' . self::OVERVIEW));
    }

    public function test_dashboard_order_columns_are_migrated_too()
    {
        $this->set('meta-box-order_dashboard', [
            'normal'  => 'dashboard_activity',
            'column3' => self::RECENT . ',' . self::TOP,
            'column4' => self::ONLINE . ',dashboard_primary',
        ]);

        VisitorsCardMigration::migrateUser($this->userId, true);

        $this->assertSame([
            'normal'  => 'dashboard_activity',
            'column3' => self::NEW_ID,
            'column4' => 'dashboard_primary',
        ], $this->meta('meta-box-order_dashboard'));
    }

    public function test_hidden_only_when_all_three_were_hidden()
    {
        $this->set('metaboxhidden_' . self::OVERVIEW, [self::RECENT, self::TOP, self::ONLINE, 'wp-statistics-hits-widget']);
        $this->set('metaboxhidden_dashboard', [self::RECENT, self::TOP, 'dashboard_primary']);

        VisitorsCardMigration::migrateUser($this->userId, true);

        $this->assertSame(['wp-statistics-hits-widget', self::NEW_ID], $this->meta('metaboxhidden_' . self::OVERVIEW));

        // Currently Online stayed visible on the dashboard: the card shows, on the Online list.
        $this->assertSame(['dashboard_primary'], $this->meta('metaboxhidden_dashboard'));
        $this->assertSame('online', Visitors::getSavedSegment($this->userId, 'dashboard'));

        // All hidden on the Overview: nothing to pick, Latest stays the default.
        $this->assertSame('', Visitors::getSavedSegment($this->userId, 'overview'));
    }

    public function test_online_off_counts_as_hidden()
    {
        $this->set('metaboxhidden_dashboard', [self::RECENT, self::TOP]);

        VisitorsCardMigration::migrateUser($this->userId, false);

        $this->assertSame([self::NEW_ID], $this->meta('metaboxhidden_dashboard'));
    }

    public function test_default_segment_is_the_first_visible_old_card_in_the_saved_order()
    {
        $this->set('meta-box-order_' . self::OVERVIEW, ['normal' => self::TOP . ',' . self::ONLINE . ',' . self::RECENT]);
        $this->set('metaboxhidden_' . self::OVERVIEW, [self::TOP]);

        VisitorsCardMigration::migrateUser($this->userId, true);

        $this->assertSame([], $this->meta('metaboxhidden_' . self::OVERVIEW));
        $this->assertSame('online', Visitors::getSavedSegment($this->userId, 'overview'));
    }

    public function test_default_segment_without_saved_order_follows_the_default_order()
    {
        // Default order: Most Active, Latest, Online.
        $this->set('metaboxhidden_' . self::OVERVIEW, [self::RECENT]);

        VisitorsCardMigration::migrateUser($this->userId, true);

        $this->assertSame('active', Visitors::getSavedSegment($this->userId, 'overview'));
    }

    public function test_nothing_hidden_keeps_latest_as_default()
    {
        $this->set('meta-box-order_' . self::OVERVIEW, ['normal' => self::TOP . ',' . self::RECENT]);

        VisitorsCardMigration::migrateUser($this->userId, true);

        $this->assertSame('', Visitors::getSavedSegment($this->userId, 'overview'));
    }

    public function test_collapsed_when_the_first_old_card_was_collapsed()
    {
        $this->set('meta-box-order_' . self::OVERVIEW, ['normal' => self::RECENT . ',' . self::TOP . ',' . self::ONLINE]);
        $this->set('closedpostboxes_' . self::OVERVIEW, [self::RECENT, 'wp-statistics-hits-widget']);

        $this->set('meta-box-order_dashboard', ['column3' => self::RECENT . ',' . self::TOP]);
        $this->set('closedpostboxes_dashboard', [self::TOP]);

        VisitorsCardMigration::migrateUser($this->userId, true);

        $this->assertSame(['wp-statistics-hits-widget', self::NEW_ID], $this->meta('closedpostboxes_' . self::OVERVIEW));

        // Only the second card was collapsed: the merged card opens.
        $this->assertSame([], $this->meta('closedpostboxes_dashboard'));
    }

    public function test_most_active_date_filter_is_copied()
    {
        $this->set(User::$dateFilterMetaKey, [self::TOP => 'filter|7days', 'wp-statistics-pages-widget' => 'filter|today']);

        VisitorsCardMigration::migrateUser($this->userId, true);

        $filters = $this->meta(User::$dateFilterMetaKey);
        $this->assertSame('filter|7days', $filters[self::NEW_ID]);
        $this->assertSame('filter|today', $filters['wp-statistics-pages-widget']);
    }

    public function test_existing_new_date_filter_is_kept()
    {
        $this->set(User::$dateFilterMetaKey, [self::TOP => 'filter|7days', self::NEW_ID => 'custom|2026-01-01:2026-01-31']);

        VisitorsCardMigration::migrateUser($this->userId, true);

        $this->assertSame('custom|2026-01-01:2026-01-31', $this->meta(User::$dateFilterMetaKey)[self::NEW_ID]);
    }

    public function test_running_twice_changes_nothing()
    {
        $this->set('meta-box-order_' . self::OVERVIEW, ['normal' => 'wp-statistics-hits-widget,' . self::RECENT . ',' . self::TOP]);
        $this->set('metaboxhidden_' . self::OVERVIEW, [self::TOP]);
        $this->set('closedpostboxes_' . self::OVERVIEW, [self::RECENT]);

        VisitorsCardMigration::migrateUser($this->userId, true);
        $first = [
            $this->meta('meta-box-order_' . self::OVERVIEW),
            $this->meta('metaboxhidden_' . self::OVERVIEW),
            $this->meta('closedpostboxes_' . self::OVERVIEW),
            Visitors::getSavedSegment($this->userId, 'overview'),
        ];

        VisitorsCardMigration::migrateUser($this->userId, true);
        $second = [
            $this->meta('meta-box-order_' . self::OVERVIEW),
            $this->meta('metaboxhidden_' . self::OVERVIEW),
            $this->meta('closedpostboxes_' . self::OVERVIEW),
            Visitors::getSavedSegment($this->userId, 'overview'),
        ];

        $this->assertSame($first, $second);
        $this->assertSame(['normal' => 'wp-statistics-hits-widget,' . self::NEW_ID], $first[0]);
        $this->assertSame([], $first[1]);
        $this->assertSame([self::NEW_ID], $first[2]);
        $this->assertSame('latest', $first[3]);
    }

    public function test_runs_once_per_user()
    {
        $this->assertTrue(VisitorsCardMigration::maybeMigrate($this->userId));
        $this->assertFalse(VisitorsCardMigration::maybeMigrate($this->userId));

        // A later saved order with an old id (should not happen) is left alone.
        $this->set('meta-box-order_' . self::OVERVIEW, ['normal' => self::RECENT]);
        VisitorsCardMigration::maybeMigrate($this->userId);
        $this->assertSame(['normal' => self::RECENT], $this->meta('meta-box-order_' . self::OVERVIEW));
    }

    public function test_user_without_saved_screens_gets_no_meta()
    {
        VisitorsCardMigration::migrateUser($this->userId, true);

        $this->assertSame('', $this->meta('meta-box-order_' . self::OVERVIEW));
        $this->assertSame('', $this->meta('metaboxhidden_dashboard'));
        $this->assertSame('', $this->meta(Visitors::SEGMENT_META_KEY));
    }
}
