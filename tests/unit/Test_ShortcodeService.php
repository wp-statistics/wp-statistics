<?php

use WP_Statistics\Service\Shortcode\ShortcodeService;
use WP_Statistics\Service\Shortcode\StatRegistry;
use WP_Statistics\Service\Shortcode\Formatters\NumberFormatter;
use WP_Statistics\Service\Shortcode\Handlers\AnalyticsStatHandler;
use WP_Statistics\Service\Shortcode\Handlers\WordPressStatHandler;
use WP_Statistics\Service\Shortcode\Contracts\StatHandlerInterface;

/**
 * Test cases for ShortcodeService
 *
 * @group shortcodes
 */
class Test_ShortcodeService extends WP_UnitTestCase
{
    /**
     * @var ShortcodeService
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new ShortcodeService();
        $this->service->register();
    }

    // =========================================================================
    // Shortcode Registration Tests
    // =========================================================================

    /**
     * @test
     */
    public function shortcode_is_registered()
    {
        $this->assertTrue(shortcode_exists('wpstatistics'));
    }

    /**
     * @test
     */
    public function service_has_registry()
    {
        $registry = $this->service->getRegistry();
        $this->assertInstanceOf(StatRegistry::class, $registry);
    }

    // =========================================================================
    // Basic Render Tests
    // =========================================================================

    /**
     * @test
     */
    public function render_returns_empty_for_no_attributes()
    {
        $this->assertEquals('', $this->service->render([]));
    }

    /**
     * @test
     */
    public function render_returns_empty_for_missing_stat()
    {
        $this->assertEquals('', $this->service->render(['time' => 'today']));
    }

    /**
     * @test
     */
    public function render_returns_empty_for_invalid_stat()
    {
        $this->assertEquals('', $this->service->render(['stat' => 'nonexistent']));
    }

    /**
     * @test
     */
    public function render_returns_empty_for_string_input()
    {
        $this->assertEquals('', $this->service->render('invalid'));
    }

    // =========================================================================
    // WordPress Stats Tests
    // =========================================================================

    /**
     * @test
     */
    public function postcount_returns_numeric()
    {
        $result = $this->service->render(['stat' => 'postcount']);
        $this->assertIsNumeric($result);
    }

    /**
     * @test
     */
    public function pagecount_returns_numeric()
    {
        $result = $this->service->render(['stat' => 'pagecount']);
        $this->assertIsNumeric($result);
    }

    /**
     * @test
     */
    public function commentcount_returns_numeric()
    {
        $result = $this->service->render(['stat' => 'commentcount']);
        $this->assertIsNumeric($result);
    }

    /**
     * @test
     */
    public function usercount_returns_numeric()
    {
        $result = $this->service->render(['stat' => 'usercount']);
        $this->assertIsNumeric($result);
    }

    /**
     * @test
     */
    public function postaverage_returns_numeric()
    {
        $result = $this->service->render(['stat' => 'postaverage']);
        $this->assertIsNumeric($result);
    }

    /**
     * @test
     */
    public function commentaverage_returns_numeric()
    {
        $result = $this->service->render(['stat' => 'commentaverage']);
        $this->assertIsNumeric($result);
    }

    /**
     * @test
     */
    public function useraverage_returns_numeric()
    {
        $result = $this->service->render(['stat' => 'useraverage']);
        $this->assertIsNumeric($result);
    }

    /**
     * @test
     */
    public function lpd_returns_date_string()
    {
        $this->factory->post->create(['post_status' => 'publish']);

        $result = $this->service->render(['stat' => 'lpd']);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function spamcount_returns_string()
    {
        $result = $this->service->render(['stat' => 'spamcount']);
        $this->assertIsString($result);
    }

    // =========================================================================
    // Analytics Stats Tests
    // =========================================================================

    /**
     * @test
     * @group integration
     */
    public function usersonline_returns_numeric()
    {
        $result = $this->service->render(['stat' => 'usersonline']);
        $this->assertIsNumeric($result);
        $this->assertGreaterThanOrEqual(0, (int) $result);
    }

    /**
     * @test
     * @group integration
     */
    public function visits_returns_numeric()
    {
        $result = $this->service->render(['stat' => 'visits', 'time' => 'today']);
        $this->assertIsNumeric($result);
    }

    /**
     * @test
     * @group integration
     */
    public function visitors_returns_numeric()
    {
        $result = $this->service->render(['stat' => 'visitors', 'time' => 'today']);
        $this->assertIsNumeric($result);
    }

    /**
     * @test
     * @group integration
     */
    public function pagevisits_returns_numeric()
    {
        $postId = $this->factory->post->create(['post_status' => 'publish']);

        $result = $this->service->render([
            'stat' => 'pagevisits',
            'id'   => $postId,
            'time' => 'total',
        ]);

        $this->assertIsNumeric($result);
    }

    /**
     * @test
     * @group integration
     */
    public function pagevisitors_returns_numeric()
    {
        $postId = $this->factory->post->create(['post_status' => 'publish']);

        $result = $this->service->render([
            'stat' => 'pagevisitors',
            'id'   => $postId,
            'time' => 'total',
        ]);

        $this->assertIsNumeric($result);
    }

    /**
     * @test
     * @group integration
     */
    public function searches_returns_numeric()
    {
        $result = $this->service->render([
            'stat'     => 'searches',
            'provider' => 'all',
            'time'     => 'month',
        ]);

        $this->assertIsNumeric($result);
    }

    /**
     * @test
     * @group integration
     */
    public function searches_with_provider_returns_numeric()
    {
        $result = $this->service->render([
            'stat'     => 'searches',
            'provider' => 'google',
            'time'     => 'month',
        ]);

        $this->assertIsNumeric($result);
    }

    /**
     * @test
     * @group integration
     */
    public function referrer_returns_numeric()
    {
        $result = $this->service->render(['stat' => 'referrer', 'time' => 'today']);
        $this->assertIsNumeric($result);
    }

    // =========================================================================
    // Time Parameter Tests
    // =========================================================================

    /**
     * @test
     * @dataProvider timeParameterProvider
     * @group integration
     */
    public function visits_with_time_parameter_returns_numeric($time)
    {
        $result = $this->service->render(['stat' => 'visits', 'time' => $time]);
        $this->assertIsNumeric($result);
    }

    public function timeParameterProvider(): array
    {
        return [
            'today'     => ['today'],
            'yesterday' => ['yesterday'],
            'week'      => ['week'],
            'month'     => ['month'],
            'year'      => ['year'],
            'total'     => ['total'],
            'numeric'   => ['30'],
        ];
    }

    // =========================================================================
    // Format Parameter Tests
    // =========================================================================

    /**
     * @test
     */
    public function format_none_returns_raw_number()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->factory->post->create(['post_status' => 'publish']);
        }

        $result = $this->service->render([
            'stat'   => 'postcount',
            'format' => 'none',
        ]);

        $this->assertIsNumeric($result);
        $this->assertStringNotContainsString(',', $result);
    }

    /**
     * @test
     */
    public function lpd_is_not_formatted()
    {
        $this->factory->post->create(['post_status' => 'publish']);

        $result = $this->service->render([
            'stat'   => 'lpd',
            'format' => 'english',
        ]);

        // Date should not be formatted as a number
        $this->assertIsString($result);
    }

    // =========================================================================
    // do_shortcode Integration Tests
    // =========================================================================

    /**
     * @test
     */
    public function do_shortcode_renders_postcount()
    {
        $result = do_shortcode('[wpstatistics stat=postcount]');
        $this->assertIsNumeric($result);
    }

    /**
     * @test
     */
    public function do_shortcode_with_format()
    {
        $result = do_shortcode('[wpstatistics stat=postcount format=english]');
        $this->assertIsString($result);
    }

    /**
     * @test
     */
    public function multiple_shortcodes_render_correctly()
    {
        $content = '[wpstatistics stat=postcount] - [wpstatistics stat=pagecount]';
        $result  = do_shortcode($content);

        $this->assertStringContainsString('-', $result);
    }

    // =========================================================================
    // Widget Context Tests
    // =========================================================================

    /**
     * @test
     */
    public function shortcode_works_in_widget_text()
    {
        // Simulate widget_text filter being applied
        $content = '[wpstatistics stat=postcount]';
        $result  = apply_filters('widget_text', $content);
        $result  = do_shortcode($result);

        $this->assertIsNumeric($result);
    }

    // =========================================================================
    // Email Template Context Tests
    // =========================================================================

    /**
     * @test
     * @group integration
     */
    public function email_template_shortcodes_render()
    {
        // Simulate email template content (same format as default.php)
        $emailContent = 'Views Today: [wpstatistics stat=visits time=today format=english]';
        $result       = do_shortcode($emailContent);

        $this->assertStringContainsString('Views Today:', $result);
        $this->assertStringNotContainsString('[wpstatistics', $result);
    }

    /**
     * @test
     * @group integration
     */
    public function email_template_multiple_shortcodes_render()
    {
        $emailContent = <<<HTML
Views Today: [wpstatistics stat=visits time=today format=english]
Views Yesterday: [wpstatistics stat=visits time=yesterday format=english]
Visitors Today: [wpstatistics stat=visitors time=today format=english]
Visitors Yesterday: [wpstatistics stat=visitors time=yesterday format=english]
Total Views: [wpstatistics stat=visits time=total format=english]
Total Visitors: [wpstatistics stat=visitors time=total format=english]
HTML;

        $result = do_shortcode($emailContent);

        // Verify no shortcodes remain unprocessed
        $this->assertStringNotContainsString('[wpstatistics', $result);
    }

    /**
     * @test
     */
    public function email_template_wordpress_stats_render()
    {
        $emailContent = 'Total Posts: [wpstatistics stat=postcount format=english]';
        $result       = do_shortcode($emailContent);

        $this->assertStringContainsString('Total Posts:', $result);
        $this->assertStringNotContainsString('[wpstatistics', $result);
    }

    // =========================================================================
    // Filter Hook Tests
    // =========================================================================

    /**
     * @test
     */
    public function attributes_filter_is_applied()
    {
        $filterCalled = false;

        add_filter('wp_statistics_shortcode_attributes', function ($atts) use (&$filterCalled) {
            $filterCalled = true;
            return $atts;
        });

        $this->service->render(['stat' => 'postcount']);

        $this->assertTrue($filterCalled);
    }

    /**
     * @test
     */
    public function result_filter_is_applied()
    {
        $filterCalled = false;

        add_filter('wp_statistics_shortcode_result', function ($value, $stat, $atts) use (&$filterCalled) {
            $filterCalled = true;
            return $value;
        }, 10, 3);

        $this->service->render(['stat' => 'postcount']);

        $this->assertTrue($filterCalled);
    }

    /**
     * @test
     */
    public function custom_stat_filter_allows_extension()
    {
        add_filter('wp_statistics_shortcode_custom_stat', function ($value, $stat, $args) {
            if ($stat === 'my_custom') {
                return 42;
            }
            return $value;
        }, 10, 3);

        $result = $this->service->render(['stat' => 'my_custom']);

        $this->assertEquals('42', $result);
    }

    // =========================================================================
    // Registry Tests
    // =========================================================================

    /**
     * @test
     */
    public function custom_handler_can_be_registered()
    {
        $mockHandler = $this->createMock(StatHandlerInterface::class);
        $mockHandler->method('getSupportedStats')->willReturn(['custom_stat']);
        $mockHandler->method('supports')->willReturnCallback(function ($stat) {
            return $stat === 'custom_stat';
        });
        $mockHandler->method('handle')->willReturn(999);

        $this->service->getRegistry()->register($mockHandler);

        $result = $this->service->render(['stat' => 'custom_stat']);

        $this->assertEquals('999', $result);
    }
}

/**
 * Test cases for StatRegistry
 *
 * @group shortcodes
 */
class Test_StatRegistry extends WP_UnitTestCase
{
    /**
     * @var StatRegistry
     */
    private $registry;

    public function setUp(): void
    {
        parent::setUp();
        $this->registry = new StatRegistry();
    }

    /**
     * @test
     */
    public function register_adds_handler()
    {
        $handler = new WordPressStatHandler();
        $this->registry->register($handler);

        $this->assertTrue($this->registry->has('postcount'));
    }

    /**
     * @test
     */
    public function has_returns_false_for_unknown_stat()
    {
        $this->assertFalse($this->registry->has('unknown'));
    }

    /**
     * @test
     */
    public function get_handler_returns_correct_handler()
    {
        $handler = new WordPressStatHandler();
        $this->registry->register($handler);

        $result = $this->registry->getHandler('postcount');

        $this->assertSame($handler, $result);
    }

    /**
     * @test
     */
    public function get_handler_returns_null_for_unknown()
    {
        $this->assertNull($this->registry->getHandler('unknown'));
    }

    /**
     * @test
     */
    public function resolve_returns_value_from_handler()
    {
        $this->registry->register(new WordPressStatHandler());

        $result = $this->registry->resolve('postcount', []);

        $this->assertIsNumeric($result);
    }

    /**
     * @test
     */
    public function resolve_triggers_filter_for_unknown_stat()
    {
        $filterCalled = false;

        add_filter('wp_statistics_shortcode_custom_stat', function ($value, $stat, $args) use (&$filterCalled) {
            $filterCalled = true;
            return 'custom_value';
        }, 10, 3);

        $result = $this->registry->resolve('unknown_stat', []);

        $this->assertTrue($filterCalled);
        $this->assertEquals('custom_value', $result);
    }

    /**
     * @test
     */
    public function get_supported_stats_returns_all_stats()
    {
        $this->registry->register(new AnalyticsStatHandler());
        $this->registry->register(new WordPressStatHandler());

        $stats = $this->registry->getSupportedStats();

        $this->assertContains('usersonline', $stats);
        $this->assertContains('postcount', $stats);
    }

    /**
     * @test
     */
    public function get_handlers_returns_all_handlers()
    {
        $handler1 = new AnalyticsStatHandler();
        $handler2 = new WordPressStatHandler();

        $this->registry->register($handler1);
        $this->registry->register($handler2);

        $handlers = $this->registry->getHandlers();

        $this->assertCount(2, $handlers);
        $this->assertContains($handler1, $handlers);
        $this->assertContains($handler2, $handlers);
    }
}

/**
 * Test cases for NumberFormatter in Shortcodes context
 *
 * @group shortcodes
 */
class Test_ShortcodeNumberFormatter extends WP_UnitTestCase
{
    /**
     * @var NumberFormatter
     */
    private $formatter;

    public function setUp(): void
    {
        parent::setUp();
        $this->formatter = new NumberFormatter();
    }

    /**
     * @test
     */
    public function format_none_returns_raw()
    {
        $this->assertEquals('1234567', $this->formatter->format(1234567, 'none'));
    }

    /**
     * @test
     */
    public function format_empty_returns_raw()
    {
        $this->assertEquals('1234567', $this->formatter->format(1234567, ''));
    }

    /**
     * @test
     */
    public function format_english_adds_commas()
    {
        $this->assertEquals('1,234,567', $this->formatter->format(1234567, 'english'));
    }

    /**
     * @test
     */
    public function format_i18n_uses_locale()
    {
        $result = $this->formatter->format(1234567, 'i18n');
        $this->assertIsString($result);
    }

    /**
     * @test
     */
    public function format_abbreviated_thousands()
    {
        $this->assertEquals('1.5K', $this->formatter->format(1500, 'abbreviated'));
    }

    /**
     * @test
     */
    public function format_abbreviated_millions()
    {
        $this->assertEquals('2.5M', $this->formatter->format(2500000, 'abbreviated'));
    }

    /**
     * @test
     */
    public function format_abbreviated_billions()
    {
        $this->assertEquals('1.2B', $this->formatter->format(1200000000, 'abbreviated'));
    }

    /**
     * @test
     */
    public function format_abbreviated_small_numbers()
    {
        $this->assertEquals('500', $this->formatter->format(500, 'abbreviated'));
    }

    /**
     * @test
     */
    public function format_non_numeric_returns_as_string()
    {
        $this->assertEquals('hello', $this->formatter->format('hello', 'english'));
    }
}

/**
 * Test cases for AnalyticsStatHandler
 *
 * @group shortcodes
 */
class Test_AnalyticsStatHandler extends WP_UnitTestCase
{
    /**
     * @var AnalyticsStatHandler
     */
    private $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->handler = new AnalyticsStatHandler();
    }

    /**
     * @test
     */
    public function supports_analytics_stats()
    {
        $this->assertTrue($this->handler->supports('usersonline'));
        $this->assertTrue($this->handler->supports('visits'));
        $this->assertTrue($this->handler->supports('visitors'));
        $this->assertTrue($this->handler->supports('pagevisits'));
        $this->assertTrue($this->handler->supports('pagevisitors'));
        $this->assertTrue($this->handler->supports('searches'));
        $this->assertTrue($this->handler->supports('referrer'));
    }

    /**
     * @test
     */
    public function does_not_support_wordpress_stats()
    {
        $this->assertFalse($this->handler->supports('postcount'));
        $this->assertFalse($this->handler->supports('pagecount'));
        $this->assertFalse($this->handler->supports('usercount'));
    }

    /**
     * @test
     */
    public function get_supported_stats_returns_array()
    {
        $stats = $this->handler->getSupportedStats();

        $this->assertIsArray($stats);
        $this->assertContains('usersonline', $stats);
        $this->assertContains('visits', $stats);
    }
}

/**
 * Test cases for WordPressStatHandler
 *
 * @group shortcodes
 */
class Test_WordPressStatHandler extends WP_UnitTestCase
{
    /**
     * @var WordPressStatHandler
     */
    private $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->handler = new WordPressStatHandler();
    }

    /**
     * @test
     */
    public function supports_wordpress_stats()
    {
        $this->assertTrue($this->handler->supports('postcount'));
        $this->assertTrue($this->handler->supports('pagecount'));
        $this->assertTrue($this->handler->supports('commentcount'));
        $this->assertTrue($this->handler->supports('spamcount'));
        $this->assertTrue($this->handler->supports('usercount'));
        $this->assertTrue($this->handler->supports('postaverage'));
        $this->assertTrue($this->handler->supports('commentaverage'));
        $this->assertTrue($this->handler->supports('useraverage'));
        $this->assertTrue($this->handler->supports('lpd'));
    }

    /**
     * @test
     */
    public function does_not_support_analytics_stats()
    {
        $this->assertFalse($this->handler->supports('usersonline'));
        $this->assertFalse($this->handler->supports('visits'));
        $this->assertFalse($this->handler->supports('visitors'));
    }

    /**
     * @test
     */
    public function handle_returns_post_count()
    {
        $this->factory->post->create_many(3, ['post_status' => 'publish']);

        $result = $this->handler->handle('postcount');

        $this->assertGreaterThanOrEqual(3, $result);
    }

    /**
     * @test
     */
    public function handle_returns_page_count()
    {
        $this->factory->post->create_many(2, [
            'post_status' => 'publish',
            'post_type'   => 'page',
        ]);

        $result = $this->handler->handle('pagecount');

        $this->assertGreaterThanOrEqual(2, $result);
    }

    /**
     * @test
     */
    public function handle_returns_user_count()
    {
        $result = $this->handler->handle('usercount');
        $this->assertGreaterThanOrEqual(1, $result); // At least admin user
    }
}
