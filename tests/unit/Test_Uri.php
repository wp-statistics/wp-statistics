<?php

namespace WP_Statistics\Tests\Uri;

use WP_UnitTestCase;
use WP_Statistics\Utils\Uri;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Service\Tracking\Core\Payload;
use WP_Statistics\Utils\Signature;

/**
 * Tests for Uri::getByVisitor() after simplification.
 *
 * Verifies that:
 * - It uses the profile's resourceUri (not server-side page detection)
 * - It no longer calls getCurrentPageType()
 * - It properly truncates long URIs
 * - It falls back to current URI when profile has none
 *
 * @since 15.0.0
 */
class Test_Uri extends WP_UnitTestCase
{
    private $requestKeys = [
        'resource_uri_id', 'resource_id', 'resource_uri', 'resource_type',
        'page_uri', 'timezone', 'language_code', 'language_name',
        'screen_width', 'screen_height', 'user_id', 'signature',
    ];

    public function tearDown(): void
    {
        foreach ($this->requestKeys as $key) {
            unset($_REQUEST[$key]);
        }
        parent::tearDown();
    }

    private function setValidRequest(array $overrides = []): void
    {
        $defaults = [
            'resource_uri_id' => '1',
            'resource_id'     => '1',
            'resource_uri'    => base64_encode('/test'),
            'resource_type'   => 'post',
            'timezone'        => 'UTC',
            'language_code'   => 'en',
            'language_name'   => 'English',
            'screen_width'    => '1920',
            'screen_height'   => '1080',
            'user_id'         => '0',
        ];

        $params = array_merge($defaults, $overrides);
        $params['signature'] = Signature::generate([
            $params['resource_type'] ?? '',
            (int) ($params['resource_id'] ?? 0),
            (int) ($params['user_id'] ?? 0),
        ]);

        foreach ($params as $key => $value) {
            $_REQUEST[$key] = $value;
        }
    }

    private function profileWithResourceUri(string $uri): VisitorProfile
    {
        $this->setValidRequest(['resource_uri' => base64_encode($uri)]);
        $profile = new VisitorProfile();
        $profile->setPayload(Payload::parse());
        return $profile;
    }

    /**
     * getByVisitor() should return the resource URI from the profile.
     */
    public function test_returns_profile_resource_uri()
    {
        $profile = $this->profileWithResourceUri('/hello-world');

        $result = Uri::getByVisitor($profile);

        $this->assertSame('/hello-world', $result);
    }

    /**
     * URIs with query strings should be preserved.
     */
    public function test_preserves_query_string()
    {
        $profile = $this->profileWithResourceUri('/page?utm_source=google&utm_medium=cpc');

        $result = Uri::getByVisitor($profile);

        $this->assertSame('/page?utm_source=google&utm_medium=cpc', $result);
    }

    /**
     * URIs longer than 255 characters should be truncated.
     */
    public function test_truncates_to_255_characters()
    {
        $longUri = '/' . str_repeat('a', 300);
        $profile = $this->profileWithResourceUri($longUri);

        $result = Uri::getByVisitor($profile);

        $this->assertSame(255, strlen($result));
        $this->assertStringStartsWith('/' . str_repeat('a', 254), $result);
    }

    /**
     * getByVisitor() should NOT call getCurrentPageType() anymore.
     */
    public function test_does_not_call_getCurrentPageType()
    {
        $profile = $this->createMock(VisitorProfile::class);
        $profile->method('getResourceUri')->willReturn('/some-page');
        $profile->expects($this->never())->method('getCurrentPageType');

        $result = Uri::getByVisitor($profile);

        $this->assertSame('/some-page', $result);
    }

    /**
     * When profile has no resourceUri, should fall back to current request URI.
     */
    public function test_falls_back_when_resource_uri_empty()
    {
        $profile = new VisitorProfile();
        // Don't set Payload — getResourceUri() will return ''

        $result = Uri::getByVisitor($profile);

        // Should return something from Uri::get() (server-side URI)
        $this->assertIsString($result);
        $this->assertLessThanOrEqual(255, strlen($result));
    }

    /**
     * Root URI should work.
     */
    public function test_handles_root_uri()
    {
        $profile = $this->profileWithResourceUri('/');

        $result = Uri::getByVisitor($profile);

        $this->assertSame('/', $result);
    }

    /**
     * Unicode URIs should be handled.
     */
    public function test_handles_unicode_uri()
    {
        $profile = $this->profileWithResourceUri('/日本語/ページ');

        $result = Uri::getByVisitor($profile);

        $this->assertSame('/日本語/ページ', $result);
    }
}
