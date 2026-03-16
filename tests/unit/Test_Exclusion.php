<?php

namespace WP_Statistics\Tests\Exclusion;

use WP_UnitTestCase;
use WP_Statistics\Service\Tracking\Core\Exclusion;
use WP_Statistics\Service\Analytics\VisitorProfile;
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
        unset(
            $_REQUEST['resource_type'],
            $_REQUEST['user_id'],
            $_REQUEST['page_uri']
        );
        parent::tearDown();
    }

    /**
     * Reset all static properties on Exclusion via reflection.
     */
    private function resetExclusionState()
    {
        $reflection = new ReflectionClass(Exclusion::class);

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
        $reflection = new ReflectionClass(Exclusion::class);
        $prop = $reflection->getProperty('options');
        $prop->setAccessible(true);
        $prop->setValue(null, $options);
    }

    // ─── Exclusion Map Integrity ──────────────────────────────────────

    public function test_exclusion_map_does_not_contain_removed_checks()
    {
        $list = Exclusion::getExclusionList();

        $removedKeys = ['ajax', 'cronjob', 'admin_page', 'xmlrpc', 'pre_flight'];
        foreach ($removedKeys as $key) {
            $this->assertNotContains($key, $list, "Removed key '{$key}' should not be in exclusion list");
        }
    }

    public function test_exclusion_map_contains_all_active_checks()
    {
        $list = Exclusion::getExclusionList();

        // Note: PHP's array_keys() converts numeric string keys like '404' to int 404,
        // so we use loose comparison (in_array without strict) for the check.
        $expectedKeys = ['robot', 'broken_file', 'ip_match', 'self_referral', 'login_page', 'feed', '404', 'excluded_url', 'user_role', 'geoip', 'robot_threshold'];
        foreach ($expectedKeys as $key) {
            $this->assertTrue(in_array($key, $list), "Active key '{$key}' should be in exclusion list");
        }
    }

    public function test_exclusion_map_has_exactly_11_checks()
    {
        $list = Exclusion::getExclusionList();
        $this->assertCount(11, $list);
    }

    // ─── Feed Exclusion ───────────────────────────────────────────────

    public function test_exclusion_feed_excludes_when_resource_type_is_feed()
    {
        $this->setOptions(['exclude_feeds' => true]);
        $_REQUEST['resource_type'] = 'feed';

        $this->assertTrue(Exclusion::exclusionFeed(new VisitorProfile()));
    }

    public function test_exclusion_feed_allows_when_option_disabled()
    {
        $this->setOptions(['exclude_feeds' => false]);
        $_REQUEST['resource_type'] = 'feed';

        $this->assertFalse(Exclusion::exclusionFeed(new VisitorProfile()));
    }

    public function test_exclusion_feed_allows_when_option_empty()
    {
        $this->setOptions([]);
        $_REQUEST['resource_type'] = 'feed';

        $this->assertFalse(Exclusion::exclusionFeed(new VisitorProfile()));
    }

    public function test_exclusion_feed_allows_when_resource_type_is_not_feed()
    {
        $this->setOptions(['exclude_feeds' => true]);
        $_REQUEST['resource_type'] = 'post';

        $this->assertFalse(Exclusion::exclusionFeed(new VisitorProfile()));
    }

    public function test_exclusion_feed_allows_when_resource_type_missing()
    {
        $this->setOptions(['exclude_feeds' => true]);
        unset($_REQUEST['resource_type']);

        $this->assertFalse(Exclusion::exclusionFeed(new VisitorProfile()));
    }

    public function test_exclusion_feed_is_case_sensitive()
    {
        $this->setOptions(['exclude_feeds' => true]);
        $_REQUEST['resource_type'] = 'Feed';

        $this->assertFalse(Exclusion::exclusionFeed(new VisitorProfile()));
    }

    // ─── Login Page Exclusion ─────────────────────────────────────────

    public function test_exclusion_login_page_excludes_when_resource_type_is_loginpage()
    {
        $this->setOptions(['exclude_loginpage' => true]);
        $_REQUEST['resource_type'] = 'loginpage';

        $this->assertTrue(Exclusion::exclusionLoginPage(new VisitorProfile()));
    }

    public function test_exclusion_login_page_allows_when_option_disabled()
    {
        $this->setOptions(['exclude_loginpage' => false]);
        $_REQUEST['resource_type'] = 'loginpage';

        $this->assertFalse(Exclusion::exclusionLoginPage(new VisitorProfile()));
    }

    public function test_exclusion_login_page_allows_when_resource_type_is_not_loginpage()
    {
        $this->setOptions(['exclude_loginpage' => true]);
        $_REQUEST['resource_type'] = 'page';

        $this->assertFalse(Exclusion::exclusionLoginPage(new VisitorProfile()));
    }

    // ─── 404 Exclusion ────────────────────────────────────────────────

    public function test_exclusion_404_excludes_when_resource_type_is_404()
    {
        $this->setOptions(['exclude_404s' => true]);
        $_REQUEST['resource_type'] = '404';

        $this->assertTrue(Exclusion::exclusion404(new VisitorProfile()));
    }

    public function test_exclusion_404_allows_when_option_disabled()
    {
        $this->setOptions(['exclude_404s' => false]);
        $_REQUEST['resource_type'] = '404';

        $this->assertFalse(Exclusion::exclusion404(new VisitorProfile()));
    }

    public function test_exclusion_404_allows_when_option_missing()
    {
        $this->setOptions([]);
        $_REQUEST['resource_type'] = '404';

        $this->assertFalse(Exclusion::exclusion404(new VisitorProfile()));
    }

    public function test_exclusion_404_allows_when_resource_type_is_page()
    {
        $this->setOptions(['exclude_404s' => true]);
        $_REQUEST['resource_type'] = 'page';

        $this->assertFalse(Exclusion::exclusion404(new VisitorProfile()));
    }

    public function test_exclusion_404_allows_when_resource_type_missing()
    {
        $this->setOptions(['exclude_404s' => true]);
        unset($_REQUEST['resource_type']);

        $this->assertFalse(Exclusion::exclusion404(new VisitorProfile()));
    }

    // ─── Broken File Exclusion ────────────────────────────────────────
    // Note: exclusionBrokenFile uses $visitorProfile->getRequestUri() which reads
    // from TrackerHelper::getRequestUri() → $_REQUEST['page_uri'] (base64-decoded)

    public function test_broken_file_excludes_404_with_image_extension()
    {
        $_REQUEST['resource_type'] = '404';
        $_REQUEST['page_uri'] = base64_encode('/images/missing-photo.jpg');

        $this->assertTrue(Exclusion::exclusionBrokenFile(new VisitorProfile()));
    }

    public function test_broken_file_excludes_404_with_css_extension()
    {
        $_REQUEST['resource_type'] = '404';
        $_REQUEST['page_uri'] = base64_encode('/assets/style.css');

        $this->assertTrue(Exclusion::exclusionBrokenFile(new VisitorProfile()));
    }

    public function test_broken_file_excludes_404_with_js_extension()
    {
        $_REQUEST['resource_type'] = '404';
        $_REQUEST['page_uri'] = base64_encode('/js/app.js');

        $this->assertTrue(Exclusion::exclusionBrokenFile(new VisitorProfile()));
    }

    public function test_broken_file_allows_404_without_extension()
    {
        $_REQUEST['resource_type'] = '404';
        $_REQUEST['page_uri'] = base64_encode('/some/missing-page');

        $this->assertFalse(Exclusion::exclusionBrokenFile(new VisitorProfile()));
    }

    public function test_broken_file_allows_404_with_php_extension()
    {
        $_REQUEST['resource_type'] = '404';
        $_REQUEST['page_uri'] = base64_encode('/some/script.php');

        $this->assertFalse(Exclusion::exclusionBrokenFile(new VisitorProfile()));
    }

    public function test_broken_file_allows_non_404_resource_type()
    {
        $_REQUEST['resource_type'] = 'page';
        $_REQUEST['page_uri'] = base64_encode('/images/photo.jpg');

        $this->assertFalse(Exclusion::exclusionBrokenFile(new VisitorProfile()));
    }

    public function test_broken_file_allows_when_resource_type_missing()
    {
        unset($_REQUEST['resource_type']);
        $_REQUEST['page_uri'] = base64_encode('/images/photo.jpg');

        $this->assertFalse(Exclusion::exclusionBrokenFile(new VisitorProfile()));
    }

    public function test_broken_file_allows_404_with_query_string_and_no_extension()
    {
        $_REQUEST['resource_type'] = '404';
        $_REQUEST['page_uri'] = base64_encode('/some/page?foo=bar.jpg');

        // The extension check is on the path, not the query string
        $this->assertFalse(Exclusion::exclusionBrokenFile(new VisitorProfile()));
    }

    // ─── User Role Exclusion ──────────────────────────────────────────

    public function test_user_role_excludes_admin_when_configured()
    {
        $user = self::factory()->user->create_and_get(['role' => 'administrator']);
        $this->setOptions(['exclude_administrator' => true]);
        $_REQUEST['user_id'] = $user->ID;

        $this->assertTrue(Exclusion::exclusionUserRole(new VisitorProfile()));
    }

    public function test_user_role_excludes_editor_when_configured()
    {
        $user = self::factory()->user->create_and_get(['role' => 'editor']);
        $this->setOptions(['exclude_editor' => true]);
        $_REQUEST['user_id'] = $user->ID;

        $this->assertTrue(Exclusion::exclusionUserRole(new VisitorProfile()));
    }

    public function test_user_role_allows_non_excluded_role()
    {
        $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
        $this->setOptions(['exclude_administrator' => true]);
        $_REQUEST['user_id'] = $user->ID;

        $this->assertFalse(Exclusion::exclusionUserRole(new VisitorProfile()));
    }

    public function test_user_role_excludes_anonymous_when_configured()
    {
        $this->setOptions(['exclude_anonymous_users' => true]);
        $_REQUEST['user_id'] = 0;

        $this->assertTrue(Exclusion::exclusionUserRole(new VisitorProfile()));
    }

    public function test_user_role_allows_anonymous_when_not_configured()
    {
        $this->setOptions(['exclude_anonymous_users' => false]);
        $_REQUEST['user_id'] = 0;

        $this->assertFalse(Exclusion::exclusionUserRole(new VisitorProfile()));
    }

    public function test_user_role_allows_anonymous_when_user_id_missing()
    {
        $this->setOptions(['exclude_anonymous_users' => false]);
        unset($_REQUEST['user_id']);

        $this->assertFalse(Exclusion::exclusionUserRole(new VisitorProfile()));
    }

    public function test_user_role_treats_nonexistent_user_id_as_anonymous()
    {
        $this->setOptions(['exclude_anonymous_users' => true]);
        $_REQUEST['user_id'] = 999999; // Non-existent user

        // get_user_by returns false → treated as anonymous
        $this->assertTrue(Exclusion::exclusionUserRole(new VisitorProfile()));
    }

    public function test_user_role_handles_negative_user_id_safely()
    {
        $this->setOptions(['exclude_anonymous_users' => false]);
        $_REQUEST['user_id'] = -5;

        // absint(-5) = 5, but user 5 likely doesn't exist in test → anonymous
        // This should not crash or produce unexpected results
        $result = Exclusion::exclusionUserRole(new VisitorProfile());
        $this->assertIsBool($result);
    }

    public function test_user_role_handles_string_user_id()
    {
        $this->setOptions(['exclude_anonymous_users' => true]);
        $_REQUEST['user_id'] = 'abc';

        // absint('abc') = 0 → anonymous
        $this->assertTrue(Exclusion::exclusionUserRole(new VisitorProfile()));
    }

    // ─── Excluded URL ─────────────────────────────────────────────────

    public function test_excluded_url_matches_exact_pattern()
    {
        $this->setOptions(['excluded_urls' => "secret-page"]);

        $profile = $this->createMock(VisitorProfile::class);
        $profile->method('getRequestUri')->willReturn('/secret-page');

        $this->assertTrue(Exclusion::exclusionExcludedUrl($profile));
    }

    public function test_excluded_url_matches_wildcard_pattern()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => "admin/*"]);

        $profile = $this->createMock(VisitorProfile::class);
        $profile->method('getRequestUri')->willReturn('/admin/dashboard');

        $this->assertTrue(Exclusion::exclusionExcludedUrl($profile));
    }

    public function test_excluded_url_no_match_for_unrelated_url()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => "secret-page"]);

        $profile = $this->createMock(VisitorProfile::class);
        $profile->method('getRequestUri')->willReturn('/public-page');

        $this->assertFalse(Exclusion::exclusionExcludedUrl($profile));
    }

    public function test_excluded_url_handles_empty_patterns()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => '']);

        $profile = $this->createMock(VisitorProfile::class);
        $profile->method('getRequestUri')->willReturn('/any-page');

        $this->assertFalse(Exclusion::exclusionExcludedUrl($profile));
    }

    public function test_excluded_url_handles_multiple_patterns()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => "page-a\npage-b\npage-c"]);

        $profile = $this->createMock(VisitorProfile::class);
        $profile->method('getRequestUri')->willReturn('/page-b');

        $this->assertTrue(Exclusion::exclusionExcludedUrl($profile));
    }

    public function test_excluded_url_strips_query_string_before_matching()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => "secret-page"]);

        $profile = $this->createMock(VisitorProfile::class);
        $profile->method('getRequestUri')->willReturn('/secret-page?ref=123');

        $this->assertTrue(Exclusion::exclusionExcludedUrl($profile));
    }

    public function test_excluded_url_is_case_insensitive()
    {
        $this->resetExclusionState();
        $this->setOptions(['excluded_urls' => "Secret-Page"]);

        $profile = $this->createMock(VisitorProfile::class);
        $profile->method('getRequestUri')->willReturn('/secret-page');

        $this->assertTrue(Exclusion::exclusionExcludedUrl($profile));
    }

    // ─── Check Method Integration ─────────────────────────────────────

    public function test_check_caches_result_on_second_call()
    {
        // Exclusion::check() caches in static $exclusionResult.
        // We verify the cache by calling check() twice and confirming
        // the second call returns the same result even after changing state.

        // First, inject a result directly via reflection
        $reflection = new ReflectionClass(Exclusion::class);
        $resultProp = $reflection->getProperty('exclusionResult');
        $resultProp->setAccessible(true);
        $resultProp->setValue(null, [
            'exclusion_match'  => true,
            'exclusion_reason' => 'test_cached'
        ]);

        // check() should return the cached result without running any checks
        $result = Exclusion::check(new VisitorProfile());
        $this->assertTrue($result['exclusion_match']);
        $this->assertSame('test_cached', $result['exclusion_reason']);
    }
}
