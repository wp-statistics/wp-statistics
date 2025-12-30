<?php

namespace WP_Statistics\Tests\Integration\Simulator;

// Load base test case
require_once __DIR__ . '/SimulatorTestCase.php';

/**
 * Test cases for RealisticVisitorGenerator
 *
 * @group simulator
 * @group generators
 */
class Test_RealisticVisitorGenerator extends SimulatorTestCase
{
    /**
     * Test that generator produces required fields
     */
    public function test_generate_produces_required_fields(): void
    {
        $generator = $this->createVisitorGenerator();

        // Set up resources
        $postIds = $this->createTestPosts(3);
        $pageIds = $this->createTestPages(2);

        $resources = [];
        foreach ($postIds as $id) {
            $resources[] = [
                'resource_uri_id' => $id,
                'resource_id'     => $id,
                'post_id'         => $id,
                'post_type'       => 'post',
                'uri'             => "/test-post-{$id}/",
            ];
        }
        foreach ($pageIds as $id) {
            $resources[] = [
                'resource_uri_id' => $id + 1000,
                'resource_id'     => $id,
                'post_id'         => $id,
                'post_type'       => 'page',
                'uri'             => "/test-page-{$id}/",
            ];
        }

        $generator->setResources($resources);

        $data = $generator->generate();

        $requiredFields = [
            'action',
            'resourceUriId',
            'resourceUri',
            'resource_type',
            'resource_id',
            'signature',
            'timezone',
            'language',
            'screenWidth',
            'screenHeight',
        ];

        $this->assertHasFields($data, $requiredFields);
    }

    /**
     * Test that resourceUri is base64 encoded
     */
    public function test_resource_uri_is_base64_encoded(): void
    {
        $generator = $this->createVisitorGenerator();
        $this->setUpGeneratorResources($generator);

        $data = $generator->generate();

        $this->assertBase64($data['resourceUri'], 'resourceUri should be base64 encoded');
    }

    /**
     * Test action field value
     */
    public function test_action_field_is_correct(): void
    {
        $generator = $this->createVisitorGenerator();
        $this->setUpGeneratorResources($generator);

        $data = $generator->generate();

        $this->assertEquals('wp_statistics_hit_record', $data['action']);
    }

    /**
     * Test user_id for logged-in visitors
     */
    public function test_logged_in_visitors_have_user_id(): void
    {
        // Configure high logged-in ratio
        $this->config->loggedInRatio = 1.0;

        $generator = $this->createVisitorGenerator();
        $this->setUpGeneratorResources($generator);

        // Create users
        $userIds = $this->createTestUsers(3);
        $users = [];
        foreach ($userIds as $id) {
            $users[] = ['ID' => $id, 'role' => 'subscriber'];
        }
        $generator->setUsers($users);

        $data = $generator->generate();

        // user_id is in the _profile sub-array
        $this->assertArrayHasKey('_profile', $data);
        $this->assertArrayHasKey('user_id', $data['_profile']);
        $this->assertContains($data['_profile']['user_id'], $userIds);
    }

    /**
     * Test guest visitors have null user_id
     */
    public function test_guest_visitors_have_null_user_id(): void
    {
        // Configure zero logged-in ratio
        $this->config->loggedInRatio = 0.0;

        $generator = $this->createVisitorGenerator();
        $this->setUpGeneratorResources($generator);

        $data = $generator->generate();

        // user_id is in the _profile sub-array
        $this->assertArrayHasKey('_profile', $data);
        $this->assertArrayHasKey('user_id', $data['_profile']);
        $this->assertNull($data['_profile']['user_id']);
    }

    /**
     * Test timezone is valid
     */
    public function test_timezone_is_valid(): void
    {
        $generator = $this->createVisitorGenerator();
        $this->setUpGeneratorResources($generator);

        $data = $generator->generate();

        $this->assertNotEmpty($data['timezone']);

        // Check if it's a recognized timezone
        $validTimezones = \DateTimeZone::listIdentifiers();
        $this->assertContains(
            $data['timezone'],
            $validTimezones,
            'Timezone should be a valid timezone identifier'
        );
    }

    /**
     * Test screen dimensions are positive integers
     */
    public function test_screen_dimensions_are_valid(): void
    {
        $generator = $this->createVisitorGenerator();
        $this->setUpGeneratorResources($generator);

        $data = $generator->generate();

        $this->assertIsInt($data['screenWidth']);
        $this->assertIsInt($data['screenHeight']);
        $this->assertGreaterThan(0, $data['screenWidth']);
        $this->assertGreaterThan(0, $data['screenHeight']);
    }

    /**
     * Test language format
     */
    public function test_language_format(): void
    {
        $generator = $this->createVisitorGenerator();
        $this->setUpGeneratorResources($generator);

        $data = $generator->generate();

        $this->assertNotEmpty($data['language']);
        // Language should be in format like 'en-US'
        $this->assertMatchesRegularExpression('/^[a-z]{2}(-[A-Z]{2})?$/', $data['language']);
    }

    /**
     * Test visitor profile generation
     */
    public function test_generate_visitor_profile(): void
    {
        $generator = $this->createVisitorGenerator();
        $profile = $generator->generateVisitorProfile();

        $expectedKeys = [
            'user_id',
            'is_logged_in',
            'device_type',
            'browser',
            'os',
            'country_code',
            'timezone',
            'language_code',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $profile, "Profile should have '{$key}' key");
        }
    }

    /**
     * Test signature is generated
     */
    public function test_signature_is_generated(): void
    {
        $generator = $this->createVisitorGenerator();
        $this->setUpGeneratorResources($generator);

        $data = $generator->generate();

        $this->assertArrayHasKey('signature', $data);
        $this->assertNotEmpty($data['signature']);
        // Signature should be 32 character hex (MD5)
        $this->assertEquals(32, strlen($data['signature']));
    }

    /**
     * Test multiple generations produce different data
     */
    public function test_multiple_generations_vary(): void
    {
        $generator = $this->createVisitorGenerator();
        $this->setUpGeneratorResources($generator);

        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = $generator->generate();
        }

        // Collect unique signatures
        $signatures = array_unique(array_column($results, 'signature'));

        // Should have at least some unique signatures (with limited resources, some may repeat)
        $this->assertGreaterThan(1, count($signatures), 'Multiple generations should produce varied results');
    }

    /**
     * Helper to set up resources for generator
     */
    private function setUpGeneratorResources($generator): void
    {
        $postIds = $this->createTestPosts(3);
        $resources = [];

        foreach ($postIds as $id) {
            $resources[] = [
                'resource_uri_id' => $id,
                'resource_id'     => $id,
                'post_id'         => $id,
                'post_type'       => 'post',
                'uri'             => "/test-post-{$id}/",
            ];
        }

        $generator->setResources($resources);
    }
}
