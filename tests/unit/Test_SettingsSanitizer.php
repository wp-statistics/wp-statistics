<?php

namespace WP_Statistics\Tests\Settings;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\Settings\SettingsSanitizer;

/**
 * Tests for SettingsSanitizer service.
 */
class Test_SettingsSanitizer extends WP_UnitTestCase
{
    public function test_converts_string_true_to_bool()
    {
        $this->assertTrue(SettingsSanitizer::sanitize('any_key', 'true'));
        $this->assertTrue(SettingsSanitizer::sanitize('any_key', true));
    }

    public function test_converts_string_false_to_bool()
    {
        $this->assertFalse(SettingsSanitizer::sanitize('any_key', 'false'));
        $this->assertFalse(SettingsSanitizer::sanitize('any_key', false));
    }

    public function test_converts_numeric_to_int()
    {
        $this->assertSame(42, SettingsSanitizer::sanitize('any_key', '42'));
        $this->assertSame(0, SettingsSanitizer::sanitize('any_key', '0'));
    }

    public function test_converts_negative_numeric_to_int()
    {
        $this->assertSame(-5, SettingsSanitizer::sanitize('any_key', '-5'));
    }

    public function test_sanitizes_regular_string()
    {
        $result = SettingsSanitizer::sanitize('any_key', '<b>bold</b>');
        $this->assertStringNotContainsString('<b>', $result);
    }

    public function test_sanitizes_array_values()
    {
        $result = SettingsSanitizer::sanitize('any_key', ['<script>alert(1)</script>', 'clean']);
        $this->assertIsArray($result);
        $this->assertStringNotContainsString('<script>', $result[0]);
        $this->assertEquals('clean', $result[1]);
    }

    public function test_preserves_newlines_for_textarea_keys()
    {
        $value = "line1\nline2\nline3";
        $result = SettingsSanitizer::sanitize('exclude_ip', $value);
        $this->assertStringContainsString("\n", $result);
    }

    public function test_preserves_newlines_for_all_textarea_keys()
    {
        $textareaKeys = SettingsSanitizer::getTextareaKeys();
        $value        = "a\nb\nc";

        foreach ($textareaKeys as $key) {
            $result = SettingsSanitizer::sanitize($key, $value);
            $this->assertStringContainsString("\n", $result, "Key '{$key}' should preserve newlines");
        }
    }

    public function test_strips_newlines_for_non_textarea_keys()
    {
        $result = SettingsSanitizer::sanitize('some_regular_key', "line1\nline2");
        $this->assertStringNotContainsString("\n", $result);
    }

    public function test_textarea_keys_are_hardcoded()
    {
        $keys = SettingsSanitizer::getTextareaKeys();
        $this->assertContains('exclude_ip', $keys);
        $this->assertContains('excluded_urls', $keys);
        $this->assertContains('excluded_countries', $keys);
        $this->assertContains('included_countries', $keys);
        $this->assertContains('robotlist', $keys);
        $this->assertContains('query_params_allow_list', $keys);
        $this->assertContains('email_list', $keys);
    }

    public function test_access_levels_enforces_admin_manage()
    {
        $result = SettingsSanitizer::sanitizeAccessLevels([
            'administrator' => 'none',
            'editor'        => 'view_stats',
        ]);

        $this->assertEquals('manage', $result['administrator']);
        $this->assertEquals('view_stats', $result['editor']);
    }

    public function test_access_levels_skips_invalid_roles()
    {
        $result = SettingsSanitizer::sanitizeAccessLevels([
            'nonexistent_role' => 'manage',
            'administrator'    => 'manage',
        ]);

        $this->assertArrayNotHasKey('nonexistent_role', $result);
        $this->assertArrayHasKey('administrator', $result);
    }

    public function test_access_levels_always_includes_admin()
    {
        $result = SettingsSanitizer::sanitizeAccessLevels([]);

        $this->assertArrayHasKey('administrator', $result);
        $this->assertEquals('manage', $result['administrator']);
    }

    public function test_access_levels_via_sanitize()
    {
        $result = SettingsSanitizer::sanitize('access_levels', [
            'administrator' => 'none',
        ]);

        $this->assertEquals('manage', $result['administrator']);
    }
}
