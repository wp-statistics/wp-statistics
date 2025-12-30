<?php

namespace WP_Statistics\Tests\Integration\Simulator;

// Load base test case
require_once __DIR__ . '/SimulatorTestCase.php';

use WP_Statistics\Testing\Simulator\Generators\InvalidDataGenerator;

/**
 * Test cases for InvalidDataGenerator
 *
 * @group simulator
 * @group generators
 * @group invalid
 */
class Test_InvalidDataGenerator extends SimulatorTestCase
{
    /**
     * Test that all categories can be generated
     */
    public function test_can_generate_all_categories(): void
    {
        $generator = $this->createInvalidGenerator();

        $categories = [
            InvalidDataGenerator::CATEGORY_BOUNDARY,
            InvalidDataGenerator::CATEGORY_MALFORMED,
            InvalidDataGenerator::CATEGORY_MISSING,
            InvalidDataGenerator::CATEGORY_OVERFLOW,
            InvalidDataGenerator::CATEGORY_ENCODING,
            InvalidDataGenerator::CATEGORY_TYPE,
        ];

        foreach ($categories as $category) {
            $result = $generator->generateForCategory($category);

            $this->assertArrayHasKey('category', $result, "Result should have category");
            $this->assertEquals($category, $result['category']);
            $this->assertArrayHasKey('request_data', $result);
            $this->assertArrayHasKey('expected', $result);
            $this->assertEquals('rejection', $result['expected']);
        }
    }

    /**
     * Test boundary value generation
     */
    public function test_generate_boundary_case(): void
    {
        $generator = $this->createInvalidGenerator();
        $result = $generator->generateBoundaryCase();

        $this->assertEquals(InvalidDataGenerator::CATEGORY_BOUNDARY, $result['category']);
        $this->assertArrayHasKey('field', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('request_data', $result);

        // Boundary values should include negative or extreme numbers
        $boundaryFields = ['resourceUriId', 'resource_id', 'screenWidth', 'screenHeight'];
        $this->assertContains($result['field'], $boundaryFields);
    }

    /**
     * Test malformed data generation
     */
    public function test_generate_malformed_data(): void
    {
        $generator = $this->createInvalidGenerator();
        $result = $generator->generateMalformedData();

        $this->assertEquals(InvalidDataGenerator::CATEGORY_MALFORMED, $result['category']);
        $this->assertArrayHasKey('field', $result);
        $this->assertArrayHasKey('value', $result);

        // Value should be a malformed string
        $this->assertTrue(
            is_string($result['value']) || $result['value'] === '',
            'Malformed value should be a string'
        );
    }

    /**
     * Test missing fields generation
     */
    public function test_generate_missing_fields(): void
    {
        $generator = $this->createInvalidGenerator();
        $result = $generator->generateMissingFields();

        $this->assertEquals(InvalidDataGenerator::CATEGORY_MISSING, $result['category']);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('request_data', $result);

        // Check that specified fields are actually missing
        foreach ($result['fields'] as $field) {
            $this->assertArrayNotHasKey(
                $field,
                $result['request_data'],
                "Field '{$field}' should be missing from request data"
            );
        }
    }

    /**
     * Test overflow data generation
     */
    public function test_generate_overflow_data(): void
    {
        $generator = $this->createInvalidGenerator();
        $result = $generator->generateOverflowData();

        $this->assertEquals(InvalidDataGenerator::CATEGORY_OVERFLOW, $result['category']);
        $this->assertArrayHasKey('field', $result);
        $this->assertArrayHasKey('length', $result);

        // Length should be very large
        $this->assertGreaterThan(100, $result['length']);
    }

    /**
     * Test encoding issues generation
     */
    public function test_generate_encoding_issues(): void
    {
        $generator = $this->createInvalidGenerator();
        $result = $generator->generateEncodingIssues();

        $this->assertEquals(InvalidDataGenerator::CATEGORY_ENCODING, $result['category']);
        $this->assertArrayHasKey('field', $result);
        $this->assertArrayHasKey('issue_type', $result);
        $this->assertArrayHasKey('value', $result);

        // Value should be hex-encoded for display
        $this->assertMatchesRegularExpression('/^[0-9a-f]*$/', $result['value']);
    }

    /**
     * Test type error generation
     */
    public function test_generate_type_errors(): void
    {
        $generator = $this->createInvalidGenerator();
        $result = $generator->generateTypeErrors();

        $this->assertEquals(InvalidDataGenerator::CATEGORY_TYPE, $result['category']);
        $this->assertArrayHasKey('field', $result);
        $this->assertArrayHasKey('value', $result);

        // Type error fields should be numeric fields receiving non-numeric values
        $numericFields = ['resourceUriId', 'resource_id', 'screenWidth', 'screenHeight'];
        $this->assertContains($result['field'], $numericFields);
    }

    /**
     * Test getAllCases generator
     */
    public function test_get_all_cases_generator(): void
    {
        $generator = $this->createInvalidGenerator();
        $cases = iterator_to_array($generator->getAllCases());

        // Should generate multiple cases
        $this->assertGreaterThan(30, count($cases), 'Should generate at least 30 test cases');

        // Should include all categories
        $categories = array_unique(array_column($cases, 'category'));
        $this->assertCount(6, $categories, 'Should include all 6 categories');
    }

    /**
     * Test random generation
     */
    public function test_random_generation(): void
    {
        $generator = $this->createInvalidGenerator();

        $categories = [];
        for ($i = 0; $i < 100; $i++) {
            $result = $generator->generate();
            $categories[] = $result['category'];
        }

        $uniqueCategories = array_unique($categories);

        // Over 100 iterations, should hit multiple categories
        $this->assertGreaterThanOrEqual(3, count($uniqueCategories));
    }

    /**
     * Test path traversal payloads in malformed data
     */
    public function test_malformed_includes_path_traversal(): void
    {
        $generator = $this->createInvalidGenerator();

        $found = false;
        for ($i = 0; $i < 50; $i++) {
            $result = $generator->generateMalformedData();
            if (strpos($result['value'], '..') !== false || strpos($result['value'], 'etc/passwd') !== false) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Malformed data should include path traversal patterns');
    }

    /**
     * Test that request_data always has action field
     */
    public function test_request_data_has_action(): void
    {
        $generator = $this->createInvalidGenerator();

        for ($i = 0; $i < 20; $i++) {
            $result = $generator->generate();
            $this->assertArrayHasKey('action', $result['request_data']);
            $this->assertEquals('wp_statistics_hit_record', $result['request_data']['action']);
        }
    }
}
