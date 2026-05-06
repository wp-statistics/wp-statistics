<?php

namespace WP_Statistics\Tests\Multilang;

use WP_UnitTestCase;
use WP_Statistics\Service\Multilang\AdapterRegistry;
use WP_Statistics\Service\Multilang\Adapters\AbstractAdapter;
use WP_Statistics\Service\Multilang\Adapters\AdapterInterface;

/**
 * Tests for AdapterRegistry — selects the active adapter from the configured
 * priority list and honors the wp_statistics_multilang_adapter override filter.
 */
class Test_MultilangAdapterRegistry extends WP_UnitTestCase
{
    public function tearDown(): void
    {
        remove_all_filters('wp_statistics_multilang_adapter');
        remove_all_filters('wp_statistics_multilang_adapters');
        parent::tearDown();
    }

    public function test_returns_null_when_no_adapter_is_active(): void
    {
        $registry = new AdapterRegistry([
            new FakeRegistryAdapter('wpml', false),
            new FakeRegistryAdapter('polylang', false),
        ]);

        $this->assertNull($registry->resolve());
    }

    public function test_returns_first_active_adapter_in_priority_order(): void
    {
        $polylang = new FakeRegistryAdapter('polylang', true);
        $wpml     = new FakeRegistryAdapter('wpml', true);

        // WPML wins over Polylang when listed first
        $registry = new AdapterRegistry([$wpml, $polylang]);
        $this->assertSame('wpml', $registry->resolve()->getSlug());

        // Reverse priority → Polylang wins
        $registry = new AdapterRegistry([$polylang, $wpml]);
        $this->assertSame('polylang', $registry->resolve()->getSlug());
    }

    public function test_skips_inactive_adapters(): void
    {
        $registry = new AdapterRegistry([
            new FakeRegistryAdapter('wpml', false),
            new FakeRegistryAdapter('polylang', true),
        ]);

        $this->assertSame('polylang', $registry->resolve()->getSlug());
    }

    public function test_default_priority_order_is_wpml_polylang_trp_qtranslate_weglot(): void
    {
        // The real default constructor instantiates concrete adapters in priority order.
        $registry = new AdapterRegistry();
        $slugs    = array_map(static function (AdapterInterface $a) {
            return $a->getSlug();
        }, $registry->getAdapters());

        $this->assertSame(
            ['wpml', 'polylang', 'translatepress', 'qtranslate', 'weglot'],
            $slugs
        );
    }

    public function test_filter_can_override_resolved_adapter(): void
    {
        $polylang = new FakeRegistryAdapter('polylang', true);
        $custom   = new FakeRegistryAdapter('custom', true);

        add_filter('wp_statistics_multilang_adapter', function () use ($custom) {
            return $custom;
        });

        $registry = new AdapterRegistry([$polylang]);

        $this->assertSame('custom', $registry->resolve()->getSlug());
    }

    public function test_filter_returning_non_adapter_is_ignored(): void
    {
        $polylang = new FakeRegistryAdapter('polylang', true);

        add_filter('wp_statistics_multilang_adapter', function () {
            return 'not-an-adapter';
        });

        $registry = new AdapterRegistry([$polylang]);

        $this->assertNull($registry->resolve());
    }

    public function test_adapters_filter_can_extend_registry_with_new_adapter(): void
    {
        // Site has no multilang plugin auto-detected, but a custom plugin
        // wants to register its own adapter via the wp_statistics_multilang_adapters filter.
        $registry = new AdapterRegistry([
            new FakeRegistryAdapter('wpml', false),
            new FakeRegistryAdapter('polylang', false),
        ]);

        $custom = new FakeRegistryAdapter('custom', true);

        add_filter('wp_statistics_multilang_adapters', function ($adapters) use ($custom) {
            $adapters[] = $custom;
            return $adapters;
        });

        $resolved = $registry->resolve();
        $this->assertNotNull($resolved, 'Custom adapter should be resolved');
        $this->assertSame('custom', $resolved->getSlug());
    }

    public function test_adapters_filter_runs_before_resolution_so_priority_is_preserved(): void
    {
        // Custom adapter prepended via filter should win over registry defaults
        // when both are active (filter prepends → first in priority).
        $registry = new AdapterRegistry([
            new FakeRegistryAdapter('wpml', true),
        ]);

        $custom = new FakeRegistryAdapter('custom', true);

        add_filter('wp_statistics_multilang_adapters', function ($adapters) use ($custom) {
            array_unshift($adapters, $custom);
            return $adapters;
        });

        $this->assertSame('custom', $registry->resolve()->getSlug());
    }

    public function test_adapters_filter_with_non_array_return_is_ignored(): void
    {
        // A misbehaving filter that returns null/string/etc. should not break resolution.
        $registry = new AdapterRegistry([
            new FakeRegistryAdapter('wpml', true),
        ]);

        add_filter('wp_statistics_multilang_adapters', function () {
            return null; // misbehaving filter
        });

        $this->assertSame('wpml', $registry->resolve()->getSlug(), 'Bad filter return is ignored, default list used');
    }
}

/**
 * Test-double adapter — controllable isActive() and slug, so the registry's
 * priority/override logic can be exercised without depending on whether real
 * plugin functions are stubbed in this test process.
 */
class FakeRegistryAdapter extends AbstractAdapter
{
    /** @var string */
    private $slug;

    /** @var bool */
    private $active;

    public function __construct(string $slug, bool $active)
    {
        $this->slug   = $slug;
        $this->active = $active;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->slug;
    }

    public function getMode(): string
    {
        return 'per-post';
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function detectLanguage(string $resourceType, int $resourceId, string $uri): ?string
    {
        return null;
    }
}
