<?php

namespace WP_Statistics\Tests\Multilang;

use WP_UnitTestCase;
use WP_Statistics\Service\AnalyticsQuery\Filters\ContentLanguageFilter;
use WP_Statistics\Service\Multilang\AdapterRegistry;
use WP_Statistics\Service\Multilang\MultilangService;
use WP_Statistics\Service\Multilang\Adapters\AbstractAdapter;

/**
 * Tests for ContentLanguageFilter.
 *
 * Mirrors the structure of Test_PostTypeFilter / Test_DeviceTypeFilter — checks
 * column, joins, options, and frontend serialization.
 */
class Test_ContentLanguageFilter extends WP_UnitTestCase
{
    /** @var ContentLanguageFilter */
    private $filter;

    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new ContentLanguageFilter();
    }

    public function tearDown(): void
    {
        MultilangService::reset();
        parent::tearDown();
    }

    public function test_filter_name_is_content_language(): void
    {
        $this->assertSame('content_language', $this->filter->getName());
    }

    public function test_filter_column_targets_resources_language(): void
    {
        $this->assertSame('resources.language', $this->filter->getColumn());
    }

    public function test_filter_type_is_string(): void
    {
        $this->assertSame('string', $this->filter->getType());
    }

    public function test_input_type_is_multi_select(): void
    {
        $this->assertSame('multi-select', $this->filter->getInputType());
    }

    public function test_supported_operators(): void
    {
        $operators = $this->filter->getSupportedOperators();
        $this->assertContains('is', $operators);
        $this->assertContains('is_not', $operators);
        $this->assertContains('in', $operators);
        $this->assertContains('not_in', $operators);
    }

    public function test_label_is_content_language(): void
    {
        $label = $this->filter->getLabel();
        $this->assertIsString($label);
        $this->assertNotEmpty($label);
        $this->assertStringContainsString('Language', $label);
    }

    public function test_groups_include_content_and_views(): void
    {
        $groups = $this->filter->getGroups();
        $this->assertIsArray($groups);
        $this->assertContains('content', $groups);
        $this->assertContains('views', $groups);
    }

    public function test_joins_chain_views_to_resources_via_resource_uris(): void
    {
        $joins = $this->filter->getJoins();

        $this->assertIsArray($joins);
        $this->assertCount(2, $joins);

        // First join: views -> resource_uris
        $this->assertSame('resource_uris', $joins[0]['table']);
        $this->assertStringContainsString('views.resource_uri_id = resource_uris.ID', $joins[0]['on']);

        // Second join: resource_uris -> resources (excluding deleted)
        $this->assertSame('resources', $joins[1]['table']);
        $this->assertStringContainsString('resource_uris.resource_id = resources.ID', $joins[1]['on']);
        $this->assertStringContainsString('is_deleted = 0', $joins[1]['on']);
    }

    public function test_requirement_is_views(): void
    {
        $this->assertSame('views', $this->filter->getRequirement());
    }

    public function test_options_are_empty_when_no_adapter_active(): void
    {
        MultilangService::setInstance(new MultilangService(new AdapterRegistry([])));

        $options = $this->filter->getOptions();
        $this->assertSame([], $options);
    }

    public function test_options_come_from_active_adapter_available_languages(): void
    {
        $adapter = new FilterFakeAdapter([
            'en' => 'English',
            'fr' => 'Français',
            'es' => 'Español',
        ]);
        MultilangService::setInstance(new MultilangService(new AdapterRegistry([$adapter])));

        $options = $this->filter->getOptions();

        $this->assertCount(3, $options);

        // Each option must have value + label
        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }

        $values = array_column($options, 'value');
        $this->assertContains('en', $values);
        $this->assertContains('fr', $values);
        $this->assertContains('es', $values);

        $labels = array_column($options, 'label');
        $this->assertContains('English', $labels);
        $this->assertContains('Français', $labels);
    }

    public function test_to_frontend_array_includes_options(): void
    {
        $adapter = new FilterFakeAdapter(['en' => 'English']);
        MultilangService::setInstance(new MultilangService(new AdapterRegistry([$adapter])));

        $array = $this->filter->toFrontendArray();

        $this->assertArrayHasKey('options', $array);
        $this->assertCount(1, $array['options']);
    }

    public function test_to_array_excludes_internal_fields_from_frontend_array(): void
    {
        $array = $this->filter->toFrontendArray();

        // toFrontendArray strips column / joins / requirement / type — UI doesn't need them
        $this->assertArrayNotHasKey('column', $array);
        $this->assertArrayNotHasKey('joins', $array);
        $this->assertArrayNotHasKey('requirement', $array);
    }
}

class FilterFakeAdapter extends AbstractAdapter
{
    /** @var array<string,string> */
    private $available;

    public function __construct(array $available)
    {
        $this->available = $available;
    }

    public function getSlug(): string
    {
        return 'filter-fake';
    }

    public function getName(): string
    {
        return 'Filter Fake';
    }

    public function getMode(): string
    {
        return 'per-post';
    }

    public function isActive(): bool
    {
        return true;
    }

    public function detectLanguage(string $resourceType, int $resourceId, string $uri): ?string
    {
        return null;
    }

    public function getAvailableLanguages(): array
    {
        return $this->available;
    }
}
