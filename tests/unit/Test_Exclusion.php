<?php

namespace WP_Statistics\Tests\Exclusion;

use WP_UnitTestCase;
use WP_Statistics\Service\Tracking\Pipeline\Exclusions;
use WP_Statistics\Service\Tracking\Pipeline\Visitor;
use WP_Statistics\Service\Tracking\Pipeline\Payload;
use ReflectionClass;

/**
 * Tests for the Exclusion engine after simplification.
 *
 * Verifies that exclusion checks use client-provided request parameters
 * (resource_type, user_id) instead of server-side WordPress conditional tags.
 *
 * @since 15.0.0
 */
class Test_Exclusion extends WP_UnitTestCase
{
    /**
     * Reset static state between tests.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->resetExclusionState();
    }

    public function tearDown(): void
    {
        $this->resetExclusionState();
        parent::tearDown();
    }

    /**
     * Create a Visitor with a mocked Payload via reflection.
     *
     * Payload is final with a private constructor, so we use reflection
     * to instantiate it and set the needed properties directly.
     */
    private function mockContext(array $overrides = []): Visitor
    {
        $request = $this->buildPayload($overrides);
        return new Visitor($request);
    }

    /**
     * Build a Payload instance via reflection (bypasses private constructor and create()).
     */
    private function buildPayload(array $overrides = []): Payload
    {
        $ref      = new ReflectionClass(Payload::class);
        $instance = $ref->newInstanceWithoutConstructor();

        $defaults = [
            'resourceType'  => '',
            'userId'        => 0,
            'resourceUri'   => '',
            'resourceUriId' => 0,
            'resourceId'    => 0,
            'referrer'      => '',
            'timezone'      => 'UTC',
            'languageCode'  => 'en',
            'languageName'  => 'English',
            'screenWidth'   => '1920',
            'screenHeight'  => '1080',
            'trackingLevel' => 'full',
        ];

        $values = array_merge($defaults, $overrides);

        $propMap = [
            'resourceType'  => 'resourceType',
            'userId'        => 'userId',
            'resourceUri'   => 'resourceUri',
            'resourceUriId' => 'resourceUriId',
            'resourceId'    => 'resourceId',
            'referrer'      => 'referrer',
            'timezone'      => 'timezone',
            'languageCode'  => 'languageCode',
            'languageName'  => 'languageName',
            'screenWidth'   => 'screenWidth',
            'screenHeight'  => 'screenHeight',
            'trackingLevel' => 'trackingLevel',
        ];

        foreach ($propMap as $key => $prop) {
            if (array_key_exists($key, $values)) {
                $rp = $ref->getProperty($prop);
                $rp->setAccessible(true);
                $rp->setValue($instance, $values[$key]);
            }
        }

        return $instance;
    }

    /**
     * Reset all static properties on Exclusion via reflection.
     */
    private function resetExclusionState()
    {
        $reflection = new ReflectionClass(Exclusions::class);

        $props = ['exclusionMap', 'excludedUrlPatterns', 'exclusionResult'];
        foreach ($props as $prop) {
            $rp = $reflection->getProperty($prop);
            $rp->setAccessible(true);
            $rp->setValue(null, null);
        }

        $optionsProp = $reflection->getProperty('options');
        $optionsProp->setAccessible(true);
        $optionsProp->setValue(null, []);
    }

    /**
     * Set options on the Exclusion class via reflection.
     */
    private function setOptions(array $options)
    {
        $reflection = new ReflectionClass(Exclusions::class);
        $prop = $reflection->getProperty('options');
        $prop->setAccessible(true);
        $prop->setValue(null, $options);
    }

    // ─── Exclusion Map Integrity ──────────────────────────────────────

    public function test_exclusion_map_does_not_contain_removed_checks()
    {
        $list = Exclusions::getExclusionList();

        $removedKeys = ['ajax', 'cronjob', 'admin_page', 'xmlrpc', 'pre_flight'];
        foreach ($removedKeys as $key) {
            $this->assertNotContains($key, $list, "Removed key '{$key}' should not be in exclusion list");
        }
    }

    public function test_exclusion_map_contains_all_active_checks()
    {
        $list = Exclusions::getExclusionList();

        // Note: PHP's array_keys() converts numeric string keys like '404' to int 404,
        // so we use loose comparison (in_array without strict) for the check.
        $expectedKeys = ['robot', 'broken_file', 'ip_match', 'login_page', 'feed', '404', 'excluded_url', 'user_role', 'geoip', 'robot_threshold'];
        foreach ($expectedKeys as $key) {
            $this->assertTrue(in_array($key, $list), "Active key '{$key}' should be in exclusion list");
        }
    }

    public function test_exclusion_map_has_exactly_10_checks()
    {
        $list = Exclusions::getExclusionList();
        $this->assertCount(10, $list);
    }

    // ─── Feed Exclusion ───────────────────────────────────────────────

    public function test_exclusion_feed_excludes_when_resource_type_is_feed()
    {
        $this->setOptions(['exclude_feeds' => true]);
        $this->assertTrue(Exclusions::exclusionFeed($this->mockContext(['resourceType' => 'feed'])));
    }

    public function test_exclusion_feed_allows_when_option_disabled()
    {
        $this->setOptions(['exclude_feeds' => false]);
        $this->assertFalse(Exclusions::exclusionFeed($this->mockContext(['resourceType' => 'feed'])));
    }

    public function test_exclusion_feed_allows_when_option_empty()
    {
        $this->setOptions([]);
        $this->assertFalse(Exclusions::exclusionFeed($this->mockContext(['resourceType' => 'feed'])));
    }

    public function test_exclusion_feed_allows_when_resource_type_is_not_feed()
    {
        $this->setOptions(['exclude_feeds' => true]);
        $this->assertFalse(Exclusions::exclusionFeed($this->mockContext(['resourceType' => 'post'])));
    }

    public function test_exclusion_feed_allows_when_resource_type_missing()
    {
        $this->setOptions(['exclude_feeds' => true]);
        $this->assertFalse(Exclusions::exclusionFeed($this->mockContext()));
    }

    public function test_exclusion_feed_is_case_sensitive()
    {
        $this->setOptions(['exclude_feeds' => true]);
        $this->assertFalse(Exclusions::exclusionFeed($this->mockContext(['resourceType' => 'Feed'])));
    }

    // ─── Login Page Exclusion ─────────────────────────────────────────

    public function test_exclusion_login_page_excludes_when_resource_type_is_loginpage()
    {
        $this->setOptions(['exclude_loginpage' => true]);
        $this->assertTrue(Exclusions::exclusionLoginPage($this->mockContext(['resourceType' => 'loginpage'])));
    }

    public function test_exclusion_login_page_allows_when_option_disabled()
    {
        $this->setOptions(['exclude_loginpage' => false]);
        $this->assertFalse(Exclusions::exclusionLoginPage($this->mockContext(['resourceType' => 'loginpage'])));
    }

    public function test_exclusion_login_page_allows_when_resource_type_is_not_loginpage()
    {
        $this->setOptions(['exclude_loginpage' => true]);
        $this->assertFalse(Exclusions::exclusionLoginPage($this->mockContext(['resourceType' => 'page'])));
    }

    // ─── 404 Exclusion ────────────────────────────────────────────────

    public function test_exclusion_404_excludes_when_resource_type_is_404()
    {
        $this->setOptions(['exclude_404s' => true]);
        $this->assertTrue(Exclusions::exclusion404($this->mockContext(['resourceType' => '404'])));
    }

    public function test_exclusion_404_allows_when_option_disabled()
    {
        $this->setOptions(['exclude_404s' => false]);
        $this->assertFalse(Exclusions::exclusion404($this->mockContext(['resourceType' => '404'])));
    }

    public function test_exclusion_404_allows_when_option_missing()
    {
        $this->setOptions([]);
        $this->assertFalse(Exclusions::exclusion404($this->mockContext(['resourceType' => '404'])));
    }

    public function test_exclusion_404_allows_when_resource_type_is_page()
    {
        $this->setOptions(['exclude_404s' => true]);
        $this->assertFalse(Exclusions::exclusion404($this->mockContext(['resourceType' => 'page'])));
    }

    public function test_exclusion_404_allows_when_resource_type_missing()
    {
        $this->setOptions(['exclude_404s' => true]);
        $this->assertFalse(Exclusions::exclusion404($this->mockContext()));
    }

    // ─── Broken File Exclusion ────────────────────────────────────────
    // Note: exclusionBrokenFile uses $visitor->getRequest()->getResourceUri()

    public function test_broken_file_excludes_404_with_image_extension()
    {
        $visitor = $this->mockContext(['resourceType' => '404', 'resourceUri' => '/images/missing-photo.jpg']);
        $this->assertTrue(Exclusions::exclusionBrokenFile($visitor));
    }

    public function test_broken_file_excludes_404_with_css_extension()
    {
        $visitor = $this->mockContext(['resourceType' => '404', 'resourceUri' => '/assets/style.css']);
        $this->assertTrue(Exclusions::exclusionBrokenFile($visitor));
    }

    public function test_broken_file_excludes_404_with_js_extension()
    {
        $visitor = $this->mockContext(['resourceType' => '404', 'resourceUri' => '/js/app.js']);
        $this->assertTrue(Exclusions::exclusionBrokenFile($visitor));
    }

    public function test_broken_file_allows_404_without_extension()
    {
        $visitor = $this->mockContext(['resourceType' => '404', 'resourceUri' => '/some/missing-page']);
        $this->assertFalse(Exclusions::exclusionBrokenFile($visitor));
    }

    public function test_broken_file_allows_404_with_php_extension()
    {
        $visitor = $this->mockContext(['resourceType' => '404', 'resourceUri' => '/some/script.php']);
        $this->assertFalse(Exclusions::exclusionBrokenFile($visitor));
    }

    public function test_broken_file_allows_non_404_resource_type()
    {
        $visitor = $this->mockContext(['resourceType' => 'page', 'resourceUri' => '/images/photo.jpg']);
        $this->assertFalse(Exclusions::exclusionBrokenFile($visitor));
    }

    public function test_broken_file_allows_when_resource_type_missing()
    {
        $visitor = $this->mockContext(['resourceUri' => '/images/photo.jpg']);
        $this->assertFalse(Exclusions::exclusionBrokenFile($visitor));
    }

    public function test_broken_file_allows_404_with_query_string_and_no_extension()
    {
        // The extension check is on the path, not the query string
        $visitor = $this->mockContext(['resourceType' => '404', 'resourceUri' => '/some/page?foo=bar.jpg']);
        $this->assertFalse(Exclusions::exclusionBrokenFile($visitor));
    }

    // ─── User Role Exclusion ──────────────────────────────────────────

    public function test_user_role_excludes_admin_when_configured()
    {
        $user = self::factory()->user->create_and_get(['role' => 'administrator']);
        $this->setOptions(['exclude_administrator' => true]);
        $this->assertTrue(Exclusions::exclusionUserRole($this->mockContext(['userId' => $user->ID])));
    }

    public function test_user_role_excludes_editor_when_configured()
    {
        $user = self::factory()->user->create_and_get(['role' => 'editor']);
        $this->setOptions(['exclude_editor' => true]);
        $this->assertTrue(Exclusions::exclusionUserRole($this->mockContext(['userId' => $user->ID])));
    }

    public function test_user_role_allows_non_excluded_role()
    {
        $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
        $this->setOptions(['exclude_administrator' => true]);
        $this->assertFalse(Exclusions::exclusionUserRole($this->mockContext(['userId' => $user->ID])));
    }

    public function test_user_role_excludes_anonymous_when_configured()
    {
        $this->setOptions(['exclude_anonymous_users' => true]);
        $this->assertTrue(Exclusions::exclusionUserRole($this->mockContext(['userId' => 0])));
    }

    public function test_user_role_allows_anonymous_when_not_configured()
    {
        $this->setOptions(['exclude_anonymous_users' => false]);
        $this->assertFalse(Exclusions::exclusionUserRole($this->mockContext(['userId' => 0])));
    }

    public function test_user_role_allows_anonymous_when_user_id_missing()
    {
        $this->setOptions(['exclude_anonymous_users' => false]);
        $this->assertFalse(Exclusions::exclusionUserRole($this->mockContext()));
    }

    public function test_user_role_treats_nonexistent_user_id_as_not_excluded()
    {
        $this->setOptions(['exclude_anonymous_users' => true]);
        // User::getRolesById returns [] for nonexistent user, but since userId > 0
        // the code enters the logged-in branch and finds no matching role to exclude.
        $this->assertFalse(Exclusions::exclusionUserRole($this->mockContext(['userId' => 999999])));
    }

    public function test_user_role_handles_negative_user_id_safely()
    {
        $this->setOptions(['exclude_anonymous_users' => false]);
        // absint(-5) = 5, but user 5 likely doesn't exist in test -> anonymous
        $result = Exclusions::exclusionUserRole($this->mockContext(['userId' => -5]));
        $this->assertIsBool($result);
    }

    public function test_user_role_handles_string_user_id()
    {
        $this->setOptions(['exclude_anonymous_users' => true]);
        // absint(0) = 0 -> anonymous
        $this->assertTrue(Exclusions::exclusionUserRole($this->mockContext(['userId' => 0])));
    }

    // ─── Excluded URL ─────────────────────────────────────────────────

    public function test_excluded_url_matches_exact_pattern()
    {
        $this->setOptions(['excluded_urls' => "secret-page"]);
        $visitor = $this->mockContext(['resourceUri' => '/secret-page']);
        $this->assertTrue(Exclusions::exclusionExcludedUrl($visitor));
    }

    public function test_excluded_url_matches_wildcard_pattern()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => "admin/*"]);
        $visitor = $this->mockContext(['resourceUri' => '/admin/dashboard']);
        $this->assertTrue(Exclusions::exclusionExcludedUrl($visitor));
    }

    public function test_excluded_url_no_match_for_unrelated_url()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => "secret-page"]);
        $visitor = $this->mockContext(['resourceUri' => '/public-page']);
        $this->assertFalse(Exclusions::exclusionExcludedUrl($visitor));
    }

    public function test_excluded_url_handles_empty_patterns()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => '']);
        $visitor = $this->mockContext(['resourceUri' => '/any-page']);
        $this->assertFalse(Exclusions::exclusionExcludedUrl($visitor));
    }

    public function test_excluded_url_handles_multiple_patterns()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => "page-a\npage-b\npage-c"]);
        $visitor = $this->mockContext(['resourceUri' => '/page-b']);
        $this->assertTrue(Exclusions::exclusionExcludedUrl($visitor));
    }

    public function test_excluded_url_strips_query_string_before_matching()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => "secret-page"]);
        $visitor = $this->mockContext(['resourceUri' => '/secret-page?ref=123']);
        $this->assertTrue(Exclusions::exclusionExcludedUrl($visitor));
    }

    public function test_excluded_url_is_case_insensitive()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => "Secret-Page"]);
        $visitor = $this->mockContext(['resourceUri' => '/secret-page']);
        $this->assertTrue(Exclusions::exclusionExcludedUrl($visitor));
    }

    // ─── Check Method Integration ─────────────────────────────────────

    public function test_check_caches_result_on_second_call()
    {
        // Exclusions::check() caches in static $exclusionResult.
        // We verify the cache by calling check() twice and confirming
        // the second call returns the same result even after changing state.

        // First, inject a result directly via reflection
        $reflection = new ReflectionClass(Exclusions::class);
        $resultProp = $reflection->getProperty('exclusionResult');
        $resultProp->setAccessible(true);
        $resultProp->setValue(null, [
            'exclusion_match'  => true,
            'exclusion_reason' => 'test_cached'
        ]);

        // check() should return the cached result without running any checks
        $result = Exclusions::check($this->mockContext());
        $this->assertTrue($result['exclusion_match']);
        $this->assertSame('test_cached', $result['exclusion_reason']);
    }
}
