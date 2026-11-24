<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\PostSummary;
use WP_Statistics\Service\Admin\Posts\EditorPanel;
use WP_STATISTICS\Option;

/**
 * Block editor "WP Statistics" panel: who sees it, on which posts, what it loads,
 * and that the "Statistics Summary" meta box then only shows in the Classic Editor.
 */
class Test_EditorPanel extends WP_UnitTestCase
{
    private $originalOptions;
    private $panel;
    private $suppressDbErrors;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalOptions = get_option(Option::$opt_name);
        $this->panel           = new EditorPanel();

        // The test database has no statistics tables; the summary queries just return nothing.
        $this->suppressDbErrors = $GLOBALS['wpdb']->suppress_errors(true);

        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        Option::update('disable_editor', false);
        $this->resetScripts();
    }

    public function tearDown(): void
    {
        remove_action('enqueue_block_editor_assets', [$this->panel, 'enqueue']);
        remove_all_filters('wp_statistics_editor_panel_enabled');

        if (false !== $this->originalOptions) {
            update_option(Option::$opt_name, $this->originalOptions);
        } else {
            delete_option(Option::$opt_name);
        }

        $GLOBALS['wpdb']->suppress_errors($this->suppressDbErrors);
        $GLOBALS['post'] = null;
        wp_set_current_user(0);
        set_current_screen('front');
        $this->resetScripts();

        parent::tearDown();
    }

    private function resetScripts()
    {
        wp_dequeue_script(EditorPanel::HANDLE);
        wp_deregister_script(EditorPanel::HANDLE);
    }

    private function openBlockEditor($postId)
    {
        $GLOBALS['post'] = get_post($postId);
        set_current_screen('post');
        get_current_screen()->is_block_editor(true);
    }

    public function test_available_for_users_who_can_view_stats()
    {
        $this->assertTrue(EditorPanel::isAvailable());
    }

    public function test_hidden_when_the_editor_setting_is_off()
    {
        Option::update('disable_editor', true);

        $this->assertFalse(EditorPanel::isAvailable());
    }

    public function test_hidden_for_users_without_the_stats_capability()
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'editor']));

        $this->assertFalse(EditorPanel::isAvailable());
    }

    public function test_filter_turns_the_panel_off()
    {
        add_filter('wp_statistics_editor_panel_enabled', '__return_false');

        $this->assertFalse(EditorPanel::isAvailable());
    }

    public function test_only_existing_posts_of_tracked_post_types()
    {
        $post = self::factory()->post->create_and_get();
        $this->assertTrue(EditorPanel::isAvailableForPost($post));

        $draft = self::factory()->post->create_and_get(['post_status' => 'auto-draft']);
        $this->assertFalse(EditorPanel::isAvailableForPost($draft));

        register_post_type('wps_untracked', ['public' => false]);
        $untracked = self::factory()->post->create_and_get(['post_type' => 'wps_untracked']);
        $this->assertFalse(EditorPanel::isAvailableForPost($untracked));
        unregister_post_type('wps_untracked');

        $this->assertFalse(EditorPanel::isAvailableForPost(null));
    }

    public function test_summary_meta_box_is_classic_only_while_the_panel_is_used()
    {
        $metabox = new PostSummary();
        $this->assertTrue($metabox->getCallbackArgs()['__back_compat_meta_box']);

        Option::update('disable_editor', true);
        $this->assertFalse($metabox->getCallbackArgs()['__back_compat_meta_box']);

        Option::update('disable_editor', false);
        add_filter('wp_statistics_editor_panel_enabled', '__return_false');
        $this->assertFalse($metabox->getCallbackArgs()['__back_compat_meta_box'], 'Meta box comes back in the block editor when the panel is filtered off');
    }

    public function test_panel_data_carries_the_summary_markup_and_slot()
    {
        $post = self::factory()->post->create_and_get();
        $data = EditorPanel::getPanelData($post);

        $this->assertSame($post->ID, $data['postId']);
        $this->assertSame('WPStatisticsPostPanel', $data['slot']);
        $this->assertStringContainsString('wps-post-summary', $data['html']);
        $this->assertStringContainsString('View full analytics', $data['html']);
        $this->assertArrayHasKey('chart', $data);
    }

    public function test_enqueued_in_the_block_editor_with_wordpress_packages()
    {
        $postId = self::factory()->post->create();
        $this->openBlockEditor($postId);

        $this->panel->enqueue();

        $this->assertTrue(wp_script_is(EditorPanel::HANDLE, 'enqueued'));

        $script = wp_scripts()->registered[EditorPanel::HANDLE];
        foreach (['wp-plugins', 'wp-editor', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n'] as $dep) {
            $this->assertContains($dep, $script->deps, "depends on $dep");
        }
        $this->assertNotContains('wp-statistics-chart-js', $script->deps, 'no extra bundles');

        $inline = implode("\n", (array)wp_scripts()->get_data(EditorPanel::HANDLE, 'before'));
        $this->assertStringContainsString('window.wpStatisticsPostPanelData', $inline);
        $this->assertStringContainsString('"postId":' . $postId, $inline);
        $this->assertSame('wp-statistics', $script->textdomain);
    }

    public function test_not_enqueued_for_users_without_access_or_outside_the_post_editor()
    {
        $postId = self::factory()->post->create();

        // Classic editor screen
        $GLOBALS['post'] = get_post($postId);
        set_current_screen('post');
        get_current_screen()->is_block_editor(false);
        $this->panel->enqueue();
        $this->assertFalse(wp_script_is(EditorPanel::HANDLE, 'enqueued'));

        // Block editor, but no stats capability
        $this->openBlockEditor($postId);
        wp_set_current_user(self::factory()->user->create(['role' => 'editor']));
        $this->panel->enqueue();
        $this->assertFalse(wp_script_is(EditorPanel::HANDLE, 'enqueued'));

        // Block editor, setting off
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        Option::update('disable_editor', true);
        $this->panel->enqueue();
        $this->assertFalse(wp_script_is(EditorPanel::HANDLE, 'enqueued'));
    }

    public function test_meta_box_data_is_not_loaded_for_the_hidden_meta_box_in_the_block_editor()
    {
        $postId       = self::factory()->post->create();
        $_GET['post'] = $postId;
        $_REQUEST['post'] = $postId;

        set_current_screen('post');
        get_current_screen()->is_block_editor(true);
        $blockEditor = \WP_Statistics\Service\Admin\Metabox\MetaboxHelper::getScreenMetaboxes();

        get_current_screen()->is_block_editor(false);
        $classic = \WP_Statistics\Service\Admin\Metabox\MetaboxHelper::getScreenMetaboxes();

        unset($_GET['post'], $_REQUEST['post']);

        $this->assertNotContains('wp-statistics-post-summary-widget', array_merge(...array_values($blockEditor ?: [[]])));
        $this->assertContains('wp-statistics-post-summary-widget', array_merge(...array_values($classic ?: [[]])));
    }

    public function test_activity_feed_wide_and_narrow_parts()
    {
        $html = View::load('components/objects/activity-feed', [
            'items'       => [[
                'image'       => 'https://example.test/flags/pl.svg',
                'title'       => 'Poland',
                'meta'        => 'Safari on macOS',
                'subtitle'    => 'from example.org',
                'detail'      => 'Organic Search',
                'count'       => 5,
                'count_label' => '5 views',
                'time'        => 'Nov 21, 4:38 pm',
                'time_short'  => '4:38 pm',
                'url'         => 'https://example.test/wp-admin/admin.php?page=wps_visitors_page&visitor_id=1',
            ]],
            'footer_text' => '53 visitors this week',
            'footer_url'  => 'https://example.test/report',
            'target'      => '_blank',
        ], true);

        $this->assertStringContainsString('<b class="wps-activity-feed__title">Poland</b>', $html);
        $this->assertStringContainsString('Safari on macOS', $html);
        $this->assertStringContainsString('from example.org', $html);
        $this->assertStringContainsString('Organic Search', $html);
        $this->assertStringContainsString('5 views', $html);
        $this->assertStringContainsString('4:38 pm', $html);
        $this->assertStringContainsString('target="_blank" rel="noopener"', $html);
        $this->assertStringContainsString('53 visitors this week', $html);
        $this->assertStringContainsString('View all', $html);
        $this->assertSame(1, substr_count($html, 'wps-activity-feed__item'));
    }

    public function test_activity_feed_empty_state()
    {
        $html = View::load('components/objects/activity-feed', ['items' => [], 'empty_message' => 'No visitors yet.'], true);

        $this->assertStringContainsString('No visitors yet.', $html);
        $this->assertStringNotContainsString('wps-activity-feed__list', $html);
    }
}
