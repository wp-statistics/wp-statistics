<?php

namespace WP_Statistics\Tests\WordPressIntegration;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\WordPressIntegration\EditorMetabox;

/**
 * Test EditorMetabox class.
 *
 * Tests the metabox registration and render output.
 *
 * @group wordpress-integration
 */
class Test_EditorMetabox extends WP_UnitTestCase
{
    /**
     * Test render shows placeholder message for auto-draft posts.
     */
    public function test_render_shows_publish_message_for_auto_draft()
    {
        $metabox = new EditorMetabox();
        $postId  = self::factory()->post->create(['post_status' => 'auto-draft']);
        $post    = get_post($postId);

        ob_start();
        $metabox->render($post);
        $output = ob_get_clean();

        $this->assertStringContainsString('Publish this content', $output);
        $this->assertStringNotContainsString('View detailed analytics', $output);
    }

    /**
     * Test render shows stats table for published posts.
     */
    public function test_render_shows_stats_for_published_post()
    {
        $metabox = new EditorMetabox();
        $postId  = self::factory()->post->create(['post_status' => 'publish']);
        $post    = get_post($postId);

        ob_start();
        $metabox->render($post);
        $output = ob_get_clean();

        $this->assertStringContainsString('Views', $output);
        $this->assertStringContainsString('Visitors', $output);
        $this->assertStringContainsString('View detailed analytics', $output);
    }

    /**
     * Test render includes link to content detail page.
     */
    public function test_render_includes_dashboard_link()
    {
        $metabox = new EditorMetabox();
        $postId  = self::factory()->post->create(['post_status' => 'publish']);
        $post    = get_post($postId);

        ob_start();
        $metabox->render($post);
        $output = ob_get_clean();

        $this->assertStringContainsString('#/content/' . $postId, $output);
    }

    /**
     * Test render outputs zero values when no statistics data exists.
     */
    public function test_render_shows_zero_without_data()
    {
        $metabox = new EditorMetabox();
        $postId  = self::factory()->post->create(['post_status' => 'publish']);
        $post    = get_post($postId);

        ob_start();
        $metabox->render($post);
        $output = ob_get_clean();

        // Without data in the database, both metrics should show "0"
        $this->assertStringContainsString('0', $output);
    }

    /**
     * Test render wraps output in the expected container class.
     */
    public function test_render_has_container_class()
    {
        $metabox = new EditorMetabox();
        $postId  = self::factory()->post->create(['post_status' => 'publish']);
        $post    = get_post($postId);

        ob_start();
        $metabox->render($post);
        $output = ob_get_clean();

        $this->assertStringContainsString('wps-editor-metabox', $output);
    }

    /**
     * Test render uses table layout for metrics.
     */
    public function test_render_uses_table_layout()
    {
        $metabox = new EditorMetabox();
        $postId  = self::factory()->post->create(['post_status' => 'publish']);
        $post    = get_post($postId);

        ob_start();
        $metabox->render($post);
        $output = ob_get_clean();

        $this->assertStringContainsString('<table', $output);
        $this->assertStringContainsString('</table>', $output);
    }

    /**
     * Test register method adds meta box for public post types.
     */
    public function test_register_adds_metabox()
    {
        // Set as admin with access
        $userId = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        $metabox = new EditorMetabox();
        $metabox->register();

        global $wp_meta_boxes;

        // Check that metabox is registered for the 'post' post type
        $this->assertNotEmpty($wp_meta_boxes['post']['side']['default']['wp_statistics_editor_metabox'] ?? null);
    }
}
