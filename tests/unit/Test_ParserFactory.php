<?php

namespace WP_Statistics\Tests\ImportExport;

use WP_UnitTestCase;
use WP_Statistics\Service\ImportExport\ParserFactory;

/**
 * Tests for ParserFactory.
 */
class Test_ParserFactory extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        remove_all_filters('wp_statistics_import_parsers');
    }

    public function tearDown(): void
    {
        remove_all_filters('wp_statistics_import_parsers');
        parent::tearDown();
    }

    public function test_creates_csv_parser()
    {
        $parser = ParserFactory::create('csv');
        $this->assertInstanceOf(\WP_Statistics\Service\ImportExport\Contracts\ParserInterface::class, $parser);
    }

    public function test_creates_json_parser()
    {
        $parser = ParserFactory::create('json');
        $this->assertInstanceOf(\WP_Statistics\Service\ImportExport\Contracts\ParserInterface::class, $parser);
    }

    public function test_is_case_insensitive()
    {
        $parser = ParserFactory::create('CSV');
        $this->assertInstanceOf(\WP_Statistics\Service\ImportExport\Contracts\ParserInterface::class, $parser);

        $parser = ParserFactory::create('Json');
        $this->assertInstanceOf(\WP_Statistics\Service\ImportExport\Contracts\ParserInterface::class, $parser);
    }

    public function test_throws_for_unknown_extension()
    {
        $this->expectException(\RuntimeException::class);
        ParserFactory::create('xlsx');
    }

    public function test_throws_for_empty_extension()
    {
        $this->expectException(\RuntimeException::class);
        ParserFactory::create('');
    }

    public function test_supported_extensions_default()
    {
        $extensions = ParserFactory::getSupportedExtensions();

        $this->assertContains('csv', $extensions);
        $this->assertContains('json', $extensions);
        $this->assertCount(2, $extensions);
    }

    public function test_registered_parsers_returns_class_map()
    {
        $parsers = ParserFactory::getRegisteredParsers();

        $this->assertArrayHasKey('csv', $parsers);
        $this->assertArrayHasKey('json', $parsers);
        $this->assertEquals(\WP_Statistics\Service\ImportExport\Parsers\CsvParser::class, $parsers['csv']);
        $this->assertEquals(\WP_Statistics\Service\ImportExport\Parsers\JsonParser::class, $parsers['json']);
    }

    public function test_filter_adds_parser()
    {
        add_filter('wp_statistics_import_parsers', function ($parsers) {
            $parsers['tsv'] = \WP_Statistics\Service\ImportExport\Parsers\CsvParser::class;
            return $parsers;
        });

        $extensions = ParserFactory::getSupportedExtensions();
        $this->assertContains('tsv', $extensions);
        $this->assertContains('csv', $extensions);
        $this->assertContains('json', $extensions);
        $this->assertCount(3, $extensions);
    }

    public function test_filter_added_parser_is_creatable()
    {
        add_filter('wp_statistics_import_parsers', function ($parsers) {
            $parsers['tsv'] = \WP_Statistics\Service\ImportExport\Parsers\CsvParser::class;
            return $parsers;
        });

        $parser = ParserFactory::create('tsv');
        $this->assertInstanceOf(\WP_Statistics\Service\ImportExport\Contracts\ParserInterface::class, $parser);
    }

    public function test_filter_can_override_core_parser()
    {
        add_filter('wp_statistics_import_parsers', function ($parsers) {
            $parsers['csv'] = \WP_Statistics\Service\ImportExport\Parsers\JsonParser::class;
            return $parsers;
        });

        $parser = ParserFactory::create('csv');
        $this->assertInstanceOf(\WP_Statistics\Service\ImportExport\Parsers\JsonParser::class, $parser);
    }
}
