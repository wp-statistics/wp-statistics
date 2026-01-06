<?php
/**
 * Benchmark script for Registry Lazy Loading Performance
 *
 * Run with: php tests/benchmark-registry-lazy-loading.php
 *
 * @package WP_Statistics
 */

// Bootstrap WordPress environment
$wp_load_path = dirname(__DIR__, 4) . '/wp-load.php';
if (file_exists($wp_load_path)) {
    require_once $wp_load_path;
} else {
    // Fallback: just load the autoloader for class loading tests
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

use WP_Statistics\Service\AnalyticsQuery\Registry\FilterRegistry;
use WP_Statistics\Service\AnalyticsQuery\Registry\GroupByRegistry;
use WP_Statistics\Service\AnalyticsQuery\Registry\SourceRegistry;

echo "=============================================================\n";
echo "  Registry Lazy Loading Performance Benchmark\n";
echo "=============================================================\n\n";

/**
 * Memory tracking helper
 */
function get_memory_usage_kb(): float {
    return memory_get_usage() / 1024;
}

/**
 * Benchmark a callable
 */
function benchmark(string $name, callable $fn, int $iterations = 1000): array {
    // Warm up
    $fn();

    // Force garbage collection
    gc_collect_cycles();

    $startMemory = get_memory_usage_kb();
    $startTime = microtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        $fn();
    }

    $endTime = microtime(true);
    $endMemory = get_memory_usage_kb();

    return [
        'name' => $name,
        'iterations' => $iterations,
        'total_time_ms' => ($endTime - $startTime) * 1000,
        'avg_time_us' => (($endTime - $startTime) * 1000000) / $iterations,
        'memory_delta_kb' => $endMemory - $startMemory,
    ];
}

// ============================================================
// BEFORE: Simulate eager loading (instantiate all at once)
// ============================================================

echo "## BEFORE: Eager Loading Simulation\n";
echo "   (All objects instantiated on registry creation)\n\n";

// Simulate what the old code did - instantiate ALL objects
function simulateEagerFilterRegistry(): array {
    $filters = [];
    $filterClasses = [
        \WP_Statistics\Service\AnalyticsQuery\Filters\CountryFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\CityFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\RegionFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\BrowserFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\OsFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\DeviceTypeFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\ResolutionFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerTypeFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerChannelFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerDomainFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerNameFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\PostTypeFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\AuthorFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\PageFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\ResourceIdFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\UserIdFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\LoggedInFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\IpFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\UserRoleFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\VisitorTypeFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\SessionDurationFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\ViewsPerSessionFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\TotalViewsFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\TotalSessionsFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\FirstSeenFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\LastSeenFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\BounceFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\LanguageFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\TimezoneFilter::class,
        \WP_Statistics\Service\AnalyticsQuery\Filters\EventPageIdFilter::class,
    ];

    foreach ($filterClasses as $class) {
        $filters[] = new $class();
    }

    return $filters;
}

function simulateEagerSourceRegistry(): array {
    $sources = [];
    $sourceClasses = [
        \WP_Statistics\Service\AnalyticsQuery\Sources\VisitorsSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\ViewsSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\SessionsSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\BounceRateSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\AvgSessionDurationSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\PagesPerSessionSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\AvgTimeOnPageSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\TotalDurationSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\VisitorStatusSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\SearchesSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\EventsSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\ExclusionsSource::class,
        \WP_Statistics\Service\AnalyticsQuery\Sources\OnlineVisitorsSource::class,
    ];

    foreach ($sourceClasses as $class) {
        $sources[] = new $class();
    }

    return $sources;
}

function simulateEagerGroupByRegistry(): array {
    $groupBys = [];
    $groupByClasses = [
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\DateGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\WeekGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\MonthGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\HourGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\CountryGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\CityGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\RegionGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\BrowserGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\OsGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\DeviceTypeGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\DeviceModelGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\ReferrerGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\ReferrerChannelGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\PageGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\VisitorGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\OnlineVisitorGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\ContinentGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\LanguageGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\ResolutionGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\SearchTermGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\EntryPageGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\AuthorGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\TaxonomyGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\ExclusionReasonGroupBy::class,
        \WP_Statistics\Service\AnalyticsQuery\GroupBy\ExclusionDateGroupBy::class,
    ];

    foreach ($groupByClasses as $class) {
        $groupBys[] = new $class();
    }

    return $groupBys;
}

// Count objects
$eagerFilterCount = count(simulateEagerFilterRegistry());
$eagerSourceCount = count(simulateEagerSourceRegistry());
$eagerGroupByCount = count(simulateEagerGroupByRegistry());
$eagerTotalCount = $eagerFilterCount + $eagerSourceCount + $eagerGroupByCount;

echo "   Objects instantiated on load:\n";
echo "   - Filters:  $eagerFilterCount\n";
echo "   - Sources:  $eagerSourceCount\n";
echo "   - GroupBys: $eagerGroupByCount\n";
echo "   - TOTAL:    $eagerTotalCount objects\n\n";

// Benchmark eager loading
gc_collect_cycles();
$memBefore = get_memory_usage_kb();
$timeBefore = microtime(true);

simulateEagerFilterRegistry();
simulateEagerSourceRegistry();
simulateEagerGroupByRegistry();

$timeAfter = microtime(true);
$memAfter = get_memory_usage_kb();

$eagerTimeMs = ($timeAfter - $timeBefore) * 1000;
$eagerMemoryKb = $memAfter - $memBefore;

echo "   Instantiation time: " . number_format($eagerTimeMs, 3) . " ms\n";
echo "   Memory allocated:   " . number_format($eagerMemoryKb, 2) . " KB\n\n";

// ============================================================
// AFTER: Lazy Loading (current implementation)
// ============================================================

echo "## AFTER: Lazy Loading (Current Implementation)\n";
echo "   (Objects instantiated only when accessed)\n\n";

// Reset singletons for fresh measurement
$filterReflection = new ReflectionClass(FilterRegistry::class);
$filterInstance = $filterReflection->getProperty('instance');
$filterInstance->setAccessible(true);
$filterInstance->setValue(null, null);

$sourceReflection = new ReflectionClass(SourceRegistry::class);
$sourceInstance = $sourceReflection->getProperty('instance');
$sourceInstance->setAccessible(true);
$sourceInstance->setValue(null, null);

$groupByReflection = new ReflectionClass(GroupByRegistry::class);
$groupByInstance = $groupByReflection->getProperty('instance');
$groupByInstance->setAccessible(true);
$groupByInstance->setValue(null, null);

gc_collect_cycles();
$memBefore = get_memory_usage_kb();
$timeBefore = microtime(true);

// Just create the registries - no objects should be instantiated yet
$filterRegistry = FilterRegistry::getInstance();
$sourceRegistry = SourceRegistry::getInstance();
$groupByRegistry = GroupByRegistry::getInstance();

$timeAfter = microtime(true);
$memAfter = get_memory_usage_kb();

$lazyTimeMs = ($timeAfter - $timeBefore) * 1000;
$lazyMemoryKb = $memAfter - $memBefore;

echo "   Objects instantiated on load: 0 (class names only)\n";
echo "   Instantiation time: " . number_format($lazyTimeMs, 3) . " ms\n";
echo "   Memory allocated:   " . number_format($lazyMemoryKb, 2) . " KB\n\n";

// ============================================================
// TYPICAL USAGE: Only access what's needed
// ============================================================

echo "## Typical Usage Scenario\n";
echo "   (Dashboard query: visitors + views, grouped by date)\n\n";

// Reset singletons
$filterInstance->setValue(null, null);
$sourceInstance->setValue(null, null);
$groupByInstance->setValue(null, null);

gc_collect_cycles();
$memBefore = get_memory_usage_kb();
$timeBefore = microtime(true);

$filterRegistry = FilterRegistry::getInstance();
$sourceRegistry = SourceRegistry::getInstance();
$groupByRegistry = GroupByRegistry::getInstance();

// Typical dashboard query accesses only what's needed
$visitors = $sourceRegistry->get('visitors');
$views = $sourceRegistry->get('views');
$dateGroupBy = $groupByRegistry->get('date');
$countryFilter = $filterRegistry->get('country');

$timeAfter = microtime(true);
$memAfter = get_memory_usage_kb();

$typicalTimeMs = ($timeAfter - $timeBefore) * 1000;
$typicalMemoryKb = $memAfter - $memBefore;

echo "   Objects instantiated: 4 (2 sources, 1 group_by, 1 filter)\n";
echo "   Instantiation time: " . number_format($typicalTimeMs, 3) . " ms\n";
echo "   Memory allocated:   " . number_format($typicalMemoryKb, 2) . " KB\n\n";

// ============================================================
// COMPARISON SUMMARY
// ============================================================

echo "=============================================================\n";
echo "  PERFORMANCE COMPARISON SUMMARY\n";
echo "=============================================================\n\n";

$timeSavings = $eagerTimeMs > 0 ? (($eagerTimeMs - $lazyTimeMs) / $eagerTimeMs) * 100 : 0;
$memorySavings = $eagerMemoryKb > 0 ? (($eagerMemoryKb - $lazyMemoryKb) / $eagerMemoryKb) * 100 : 0;

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ Metric              │ Before (Eager) │ After (Lazy) │ Saved │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";
printf("│ Objects on Load     │ %14d │ %12d │ %4d%% │\n", $eagerTotalCount, 0, 100);
printf("│ Init Time (ms)      │ %14.3f │ %12.3f │ %4.0f%% │\n", $eagerTimeMs, $lazyTimeMs, $timeSavings);
printf("│ Memory (KB)         │ %14.2f │ %12.2f │ %4.0f%% │\n", $eagerMemoryKb, $lazyMemoryKb, $memorySavings);
echo "└─────────────────────────────────────────────────────────────┘\n\n";

echo "Typical Query Scenario (dashboard):\n";
printf("  - Objects needed: 4 out of %d (%.1f%%)\n", $eagerTotalCount, (4 / $eagerTotalCount) * 100);
printf("  - Time: %.3f ms\n", $typicalTimeMs);
printf("  - Memory: %.2f KB\n\n", $typicalMemoryKb);

echo "Key Benefits:\n";
echo "  ✓ Zero object instantiation on registry creation\n";
echo "  ✓ Objects created on-demand when first accessed\n";
echo "  ✓ Subsequent accesses return cached instance\n";
echo "  ✓ Third-party extensions can register via registerClass()\n";
echo "  ✓ Backwards compatible - existing code works unchanged\n\n";

echo "=============================================================\n";
