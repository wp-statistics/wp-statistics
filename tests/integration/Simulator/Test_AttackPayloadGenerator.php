<?php

namespace WP_Statistics\Tests\Integration\Simulator;

// Load base test case
require_once __DIR__ . '/SimulatorTestCase.php';

use WP_Statistics\Testing\Simulator\Generators\AttackPayloadGenerator;

/**
 * Test cases for AttackPayloadGenerator
 *
 * @group simulator
 * @group generators
 * @group security
 */
class Test_AttackPayloadGenerator extends SimulatorTestCase
{
    /**
     * Test SQL injection generation
     */
    public function test_generate_sql_injection(): void
    {
        $generator = $this->createAttackGenerator();
        $result = $generator->generateSqlInjection();

        $this->assertEquals(AttackPayloadGenerator::CATEGORY_SQL_INJECTION, $result['category']);
        $this->assertArrayHasKey('subcategory', $result);
        $this->assertArrayHasKey('payload', $result);
        $this->assertArrayHasKey('severity', $result);
        $this->assertEquals(AttackPayloadGenerator::SEVERITY_CRITICAL, $result['severity']);

        // Payload should contain SQL-like syntax
        $sqlPatterns = ["'", 'UNION', 'SELECT', 'OR', 'AND', '--', '/*'];
        $found = false;
        foreach ($sqlPatterns as $pattern) {
            if (stripos($result['payload'], $pattern) !== false) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'SQL injection payload should contain SQL syntax');
    }

    /**
     * Test XSS generation
     */
    public function test_generate_xss(): void
    {
        $generator = $this->createAttackGenerator();
        $result = $generator->generateXss();

        $this->assertEquals(AttackPayloadGenerator::CATEGORY_XSS, $result['category']);
        $this->assertArrayHasKey('payload', $result);
        $this->assertEquals(AttackPayloadGenerator::SEVERITY_HIGH, $result['severity']);

        // Payload should contain XSS-like syntax
        $xssPatterns = ['<script', 'javascript:', 'onerror', 'onload', 'alert', '<img', '<svg'];
        $found = false;
        foreach ($xssPatterns as $pattern) {
            if (stripos($result['payload'], $pattern) !== false) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'XSS payload should contain script/event handler syntax');
    }

    /**
     * Test path traversal generation
     */
    public function test_generate_path_traversal(): void
    {
        $generator = $this->createAttackGenerator();
        $result = $generator->generatePathTraversal();

        $this->assertEquals(AttackPayloadGenerator::CATEGORY_PATH_TRAVERSAL, $result['category']);
        $this->assertArrayHasKey('payload', $result);
        $this->assertEquals(AttackPayloadGenerator::SEVERITY_HIGH, $result['severity']);

        // Payload should contain path traversal syntax
        $this->assertTrue(
            strpos($result['payload'], '..') !== false ||
            strpos($result['payload'], '%2e') !== false,
            'Path traversal payload should contain directory traversal syntax'
        );
    }

    /**
     * Test header injection generation
     */
    public function test_generate_header_injection(): void
    {
        $generator = $this->createAttackGenerator();
        $result = $generator->generateHeaderInjection();

        $this->assertEquals(AttackPayloadGenerator::CATEGORY_HEADER_INJECTION, $result['category']);
        $this->assertArrayHasKey('payload', $result);
        $this->assertEquals(AttackPayloadGenerator::SEVERITY_MEDIUM, $result['severity']);
    }

    /**
     * Test encoding bypass generation
     */
    public function test_generate_encoding_bypass(): void
    {
        $generator = $this->createAttackGenerator();
        $result = $generator->generateEncodingBypass();

        $this->assertEquals(AttackPayloadGenerator::CATEGORY_ENCODING_BYPASS, $result['category']);
        $this->assertArrayHasKey('subcategory', $result);
        $this->assertArrayHasKey('payload', $result);
    }

    /**
     * Test generation by severity
     */
    public function test_generate_by_severity(): void
    {
        $generator = $this->createAttackGenerator();

        // Critical severity
        $critical = $generator->generateBySeverity(AttackPayloadGenerator::SEVERITY_CRITICAL);
        $this->assertEquals(AttackPayloadGenerator::SEVERITY_CRITICAL, $critical['severity']);

        // High severity
        $high = $generator->generateBySeverity(AttackPayloadGenerator::SEVERITY_HIGH);
        $this->assertEquals(AttackPayloadGenerator::SEVERITY_HIGH, $high['severity']);

        // Medium severity
        $medium = $generator->generateBySeverity(AttackPayloadGenerator::SEVERITY_MEDIUM);
        $this->assertEquals(AttackPayloadGenerator::SEVERITY_MEDIUM, $medium['severity']);

        // Low severity
        $low = $generator->generateBySeverity(AttackPayloadGenerator::SEVERITY_LOW);
        $this->assertEquals(AttackPayloadGenerator::SEVERITY_LOW, $low['severity']);
    }

    /**
     * Test all categories can be generated
     */
    public function test_can_generate_all_categories(): void
    {
        $generator = $this->createAttackGenerator();
        $categories = $generator->getCategories();

        $this->assertGreaterThan(5, count($categories), 'Should have multiple attack categories');

        foreach ($categories as $category) {
            $result = $generator->generateForCategory($category);

            $this->assertArrayHasKey('category', $result);
            $this->assertArrayHasKey('payload', $result);
            $this->assertArrayHasKey('field', $result);
            $this->assertArrayHasKey('request_data', $result);
            $this->assertEquals('rejection', $result['expected']);
        }
    }

    /**
     * Test getAllCases generator
     */
    public function test_get_all_cases_generator(): void
    {
        $generator = $this->createAttackGenerator();
        $cases = iterator_to_array($generator->getAllCases());

        // Should generate many cases
        $this->assertGreaterThan(20, count($cases));

        // Should cover multiple categories
        $categories = array_unique(array_column($cases, 'category'));
        $this->assertGreaterThan(5, count($categories));
    }

    /**
     * Test scenario execution
     */
    public function test_run_scenario(): void
    {
        $generator = $this->createAttackGenerator();
        $scenarios = $generator->getScenarios();

        $this->assertContains('basic_security_scan', $scenarios);
        $this->assertContains('comprehensive_scan', $scenarios);

        // Run basic scenario
        $cases = iterator_to_array($generator->runScenario('basic_security_scan'));
        $this->assertGreaterThan(5, count($cases));
    }

    /**
     * Test specific SQL injection types
     */
    public function test_specific_sql_injection_types(): void
    {
        $generator = $this->createAttackGenerator();

        $types = ['classic_union', 'blind_boolean', 'time_based', 'comment_bypass'];

        foreach ($types as $type) {
            $result = $generator->generateSqlInjection($type);
            $this->assertEquals($type, $result['subcategory']);
        }
    }

    /**
     * Test specific XSS types
     */
    public function test_specific_xss_types(): void
    {
        $generator = $this->createAttackGenerator();

        $types = ['script_tags', 'event_handlers', 'javascript_uris'];

        foreach ($types as $type) {
            $result = $generator->generateXss($type);
            $this->assertEquals($type, $result['subcategory']);
        }
    }

    /**
     * Test that request_data includes target field with payload
     */
    public function test_request_data_includes_payload(): void
    {
        $generator = $this->createAttackGenerator();

        for ($i = 0; $i < 10; $i++) {
            $result = $generator->generate();
            $requestData = $result['request_data'];
            $targetField = $result['field'];

            // The payload should be in the request data (possibly base64 encoded)
            $this->assertArrayHasKey($targetField, $requestData, "Target field '{$targetField}' should exist in request data");
        }
    }

    /**
     * Test attack metadata is included
     */
    public function test_attack_metadata_included(): void
    {
        $generator = $this->createAttackGenerator();
        $result = $generator->generate();

        $requestData = $result['request_data'];

        $this->assertArrayHasKey('_attack_meta', $requestData);
        $this->assertArrayHasKey('category', $requestData['_attack_meta']);
        $this->assertArrayHasKey('payload', $requestData['_attack_meta']);
    }

    /**
     * Test WordPress-specific path traversal
     */
    public function test_wordpress_specific_path_traversal(): void
    {
        $generator = $this->createAttackGenerator();

        $found = false;
        for ($i = 0; $i < 50; $i++) {
            $result = $generator->generatePathTraversal();
            if (
                stripos($result['payload'], 'wp-config') !== false ||
                stripos($result['payload'], 'wp-includes') !== false
            ) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Path traversal should include WordPress-specific paths');
    }

    /**
     * Test severity distribution
     */
    public function test_severity_distribution(): void
    {
        $generator = $this->createAttackGenerator();

        $severities = [];
        for ($i = 0; $i < 100; $i++) {
            $result = $generator->generate();
            $severities[] = $result['severity'];
        }

        $uniqueSeverities = array_unique($severities);

        // Should hit multiple severity levels
        $this->assertGreaterThanOrEqual(3, count($uniqueSeverities));
    }
}
