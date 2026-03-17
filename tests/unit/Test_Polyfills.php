<?php

namespace WP_Statistics\Tests\Polyfills;

use WP_Statistics\Utils\Signature;
use WP_Statistics\Utils\User;
use WP_UnitTestCase;

/**
 * Tests for SHORTINIT polyfill functions, User::getRolesById(), and Signature.
 */
class Test_Polyfills extends WP_UnitTestCase
{
    public function test_signature_uses_auth_constants()
    {
        $this->assertTrue(defined('AUTH_KEY'));
        $this->assertTrue(defined('AUTH_SALT'));

        $payload = ['post', 1, 1];
        $sig = Signature::generate($payload);

        $this->assertIsString($sig);
        $this->assertSame(32, strlen($sig)); // md5 hex length
        $this->assertTrue(Signature::check($payload, $sig));
    }

    public function test_get_roles_by_id_returns_roles()
    {
        $userId = self::factory()->user->create(['role' => 'editor']);

        $roles = User::getRolesById($userId);

        $this->assertContains('editor', $roles);
    }

    public function test_get_roles_by_id_returns_empty_for_nonexistent_user()
    {
        $this->assertSame([], User::getRolesById(999999));
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
}
