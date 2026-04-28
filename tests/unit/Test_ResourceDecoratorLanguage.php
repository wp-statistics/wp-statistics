<?php

namespace WP_Statistics\Tests\Multilang;

use WP_UnitTestCase;
use WP_Statistics\Decorators\ResourceDecorator;
use WP_Statistics\Service\Multilang\AdapterRegistry;
use WP_Statistics\Service\Multilang\MultilangService;

/**
 * Smoke test for the new ResourceDecorator::getLanguageName() accessor.
 *
 * The decorator wraps a record object (stdClass-like) under
 * $resource->identifier->resource->record. We construct a minimal stub here
 * since this test only cares about the language fields.
 */
class Test_ResourceDecoratorLanguage extends WP_UnitTestCase
{
    public function tearDown(): void
    {
        MultilangService::reset();
        parent::tearDown();
    }

    public function test_returns_null_when_no_language_stored(): void
    {
        $decorator = $this->makeDecorator(null);

        $this->assertNull($decorator->getLanguageName());
    }

    public function test_returns_label_from_built_in_table_when_no_adapter(): void
    {
        MultilangService::setInstance(new MultilangService(new AdapterRegistry([])));

        $decorator = $this->makeDecorator('fr');

        $this->assertSame('Français', $decorator->getLanguageName());
    }

    public function test_returns_code_for_unknown_language(): void
    {
        MultilangService::setInstance(new MultilangService(new AdapterRegistry([])));

        $decorator = $this->makeDecorator('xx');

        $this->assertSame('xx', $decorator->getLanguageName());
    }

    /**
     * Build a decorator wrapping a synthetic record. Passing an object with
     * resource_type triggers ResourceManager's record-passthrough path so the
     * decorator's identifier->resource->record points at our stub.
     */
    private function makeDecorator(?string $language): ResourceDecorator
    {
        $record = (object) [
            'ID'            => 1,
            'resource_id'   => 100,
            'resource_type' => 'post',
            'language'      => $language,
        ];

        return new ResourceDecorator($record);
    }
}
