<?php

namespace WP_Statistics\Tests\WordPressIntegration;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\WordPressIntegration\StatsColumnManager;

/**
 * Test StatsColumnManager class.
 *
 * Tests the column registration, rendering, and sortable column methods
 * for both content (post/page/CPT) and user list tables.
 *
 * @group wordpress-integration
 */
class Test_StatsColumnManager extends WP_UnitTestCase
{
    /**
     * Test addContentColumn adds the views column to an existing columns array.
     */
    public function test_addContentColumn_adds_views_column()
    {
        $manager = new StatsColumnManager();
        $columns = ['cb' => '<input type="checkbox" />', 'title' => 'Title', 'date' => 'Date'];

        $result = $manager->addContentColumn($columns);

        $this->assertArrayHasKey('wp_statistics_views', $result);
        $this->assertStringContainsString('Views', $result['wp_statistics_views']);
    }

    /**
     * Test addContentColumn preserves existing columns.
     */
    public function test_addContentColumn_preserves_existing_columns()
    {
        $manager = new StatsColumnManager();
        $columns = ['cb' => '<input type="checkbox" />', 'title' => 'Title'];

        $result = $manager->addContentColumn($columns);

        $this->assertArrayHasKey('cb', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertCount(3, $result);
    }

    /**
     * Test makeContentColumnSortable adds the views column as sortable.
     */
    public function test_makeContentColumnSortable_adds_sortable_column()
    {
        $manager = new StatsColumnManager();
        $columns = ['title' => 'title', 'date' => 'date'];

        $result = $manager->makeContentColumnSortable($columns);

        $this->assertArrayHasKey('wp_statistics_views', $result);
        $this->assertEquals('wp_statistics_views', $result['wp_statistics_views']);
    }

    /**
     * Test makeContentColumnSortable preserves existing sortable columns.
     */
    public function test_makeContentColumnSortable_preserves_existing()
    {
        $manager = new StatsColumnManager();
        $columns = ['title' => 'title'];

        $result = $manager->makeContentColumnSortable($columns);

        $this->assertArrayHasKey('title', $result);
        $this->assertCount(2, $result);
    }

    /**
     * Test renderContentColumnValue outputs nothing for unrelated columns.
     */
    public function test_renderContentColumnValue_ignores_other_columns()
    {
        $manager = new StatsColumnManager();

        ob_start();
        $manager->renderContentColumnValue('title', 1);
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    /**
     * Test renderContentColumnValue outputs a formatted number for views column.
     */
    public function test_renderContentColumnValue_outputs_views()
    {
        global $wpdb;

        $postId  = self::factory()->post->create();
        $manager = new StatsColumnManager();

        // Suppress DB errors since statistics_pages table may not exist in test env
        $wpdb->suppress_errors(true);

        ob_start();
        $manager->renderContentColumnValue('wp_statistics_views', $postId);
        $output = ob_get_clean();

        $wpdb->suppress_errors(false);

        // With no data in the statistics_pages table, views should be 0
        $this->assertEquals('0', $output);
    }

    /**
     * Test addUserColumn adds the user views column.
     */
    public function test_addUserColumn_adds_views_column()
    {
        $manager = new StatsColumnManager();
        $columns = ['username' => 'Username', 'email' => 'Email', 'role' => 'Role'];

        $result = $manager->addUserColumn($columns);

        $this->assertArrayHasKey('wp_statistics_user_views', $result);
        $this->assertStringContainsString('Views', $result['wp_statistics_user_views']);
    }

    /**
     * Test addUserColumn preserves existing columns.
     */
    public function test_addUserColumn_preserves_existing_columns()
    {
        $manager = new StatsColumnManager();
        $columns = ['username' => 'Username', 'email' => 'Email'];

        $result = $manager->addUserColumn($columns);

        $this->assertArrayHasKey('username', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertCount(3, $result);
    }

    /**
     * Test renderUserColumnValue returns original output for unrelated columns.
     */
    public function test_renderUserColumnValue_ignores_other_columns()
    {
        $manager = new StatsColumnManager();

        $result = $manager->renderUserColumnValue('original-output', 'email', 1);

        $this->assertEquals('original-output', $result);
    }

    /**
     * Test renderUserColumnValue returns a formatted number for user views column.
     */
    public function test_renderUserColumnValue_returns_views()
    {
        global $wpdb;

        $userId  = self::factory()->user->create();
        $manager = new StatsColumnManager();

        // Suppress DB errors since statistics_pages table may not exist in test env
        $wpdb->suppress_errors(true);

        $result = $manager->renderUserColumnValue('', 'wp_statistics_user_views', $userId);

        $wpdb->suppress_errors(false);

        // With no data in the statistics_pages table, views should be 0
        $this->assertEquals('0', $result);
    }

    /**
     * Test makeUserColumnSortable adds the user views column as sortable.
     */
    public function test_makeUserColumnSortable_adds_sortable_column()
    {
        $manager = new StatsColumnManager();
        $columns = ['username' => 'username'];

        $result = $manager->makeUserColumnSortable($columns);

        $this->assertArrayHasKey('wp_statistics_user_views', $result);
        $this->assertEquals('wp_statistics_user_views', $result['wp_statistics_user_views']);
    }

    /**
     * Test registerContentColumns registers filters for public post types.
     */
    public function test_registerContentColumns_hooks_post_type_filters()
    {
        $manager = new StatsColumnManager();

        $manager->registerContentColumns();

        // The 'post' post type should have the column filter registered
        $this->assertNotFalse(has_filter('manage_post_posts_columns', [$manager, 'addContentColumn']));
        $this->assertNotFalse(has_action('manage_post_posts_custom_column', [$manager, 'renderContentColumnValue']));
        $this->assertNotFalse(has_filter('manage_edit-post_sortable_columns', [$manager, 'makeContentColumnSortable']));
    }

    /**
     * Test registerContentColumns excludes attachments.
     */
    public function test_registerContentColumns_excludes_attachments()
    {
        $manager = new StatsColumnManager();

        $manager->registerContentColumns();

        $this->assertFalse(has_filter('manage_attachment_posts_columns', [$manager, 'addContentColumn']));
    }

    /**
     * Test handleContentColumnOrderby skips non-main queries.
     */
    public function test_handleContentColumnOrderby_skips_non_views_orderby()
    {
        $manager = new StatsColumnManager();

        $query = new \WP_Query();
        $query->set('orderby', 'date');

        // This should do nothing (no filter added)
        $manager->handleContentColumnOrderby($query);

        $this->assertFalse(has_filter('posts_clauses'));
    }
}
