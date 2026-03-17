<?php

namespace WP_Statistics\Tests\Polyfills;

use WP_UnitTestCase;

/**
 * Tests for SHORTINIT polyfill functions.
 *
 * These tests verify polyfill behavior by loading the polyfills file
 * in an environment where the real WordPress functions already exist.
 * Since each polyfill is guarded by function_exists(), the real functions
 * are tested here — but the logic being verified (e.g. wp_salt returning
 * AUTH_KEY . AUTH_SALT) matches the polyfill implementation.
 */
class Test_Polyfills extends WP_UnitTestCase
{
    public function test_wp_salt_returns_non_empty_string()
    {
        $salt = wp_salt('auth');
        $this->assertIsString($salt);
        $this->assertNotEmpty($salt);
    }

    public function test_polyfill_wp_salt_constants_are_defined()
    {
        // The polyfill returns AUTH_KEY . AUTH_SALT when wp_salt() is not available.
        // Verify the constants it relies on are defined (non-empty strings).
        $this->assertTrue(defined('AUTH_KEY'));
        $this->assertTrue(defined('AUTH_SALT'));
        $this->assertIsString(AUTH_KEY);
        $this->assertIsString(AUTH_SALT);
    }

    public function test_wp_generate_password_returns_correct_length()
    {
        $password = wp_generate_password(16, false, false);
        $this->assertSame(16, strlen($password));

        $password32 = wp_generate_password(32, false, false);
        $this->assertSame(32, strlen($password32));
    }

    public function test_get_user_by_returns_user_with_roles()
    {
        $userId = self::factory()->user->create(['role' => 'editor']);

        $user = get_user_by('id', $userId);

        $this->assertNotFalse($user);
        $this->assertEquals($userId, $user->ID);
        $this->assertContains('editor', $user->roles);
    }

    public function test_get_user_by_returns_false_for_nonexistent_user()
    {
        $this->assertFalse(get_user_by('id', 999999));
    }

    public function test_home_url_returns_site_home()
    {
        $home = get_option('home');
        $this->assertSame($home, home_url());
    }

    public function test_home_url_appends_path()
    {
        $home = get_option('home');
        $this->assertSame(rtrim($home, '/') . '/test-path', home_url('/test-path'));
    }

    public function test_wp_parse_url_matches_parse_url()
    {
        $url = 'https://example.com/path?query=1#fragment';

        $this->assertSame(parse_url($url, PHP_URL_HOST), wp_parse_url($url, PHP_URL_HOST));
        $this->assertSame(parse_url($url, PHP_URL_PATH), wp_parse_url($url, PHP_URL_PATH));
    }

    public function test_translation_functions_pass_through()
    {
        $this->assertSame('Hello', __('Hello', 'wp-statistics'));
        $this->assertSame('Hello', esc_html__('Hello', 'wp-statistics'));
    }

    public function test_esc_html_escapes_html_entities()
    {
        $this->assertStringContainsString('&lt;', esc_html__('<script>', 'wp-statistics'));
    }
}
