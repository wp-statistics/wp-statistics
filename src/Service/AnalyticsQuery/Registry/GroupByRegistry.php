<?php

namespace WP_Statistics\Service\AnalyticsQuery\Registry;

use WP_Statistics\Service\AnalyticsQuery\Contracts\GroupByInterface;
use WP_Statistics\Service\AnalyticsQuery\Contracts\RegistryInterface;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\DateGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\WeekGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\MonthGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\HourGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\CountryGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\CityGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\RegionGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\BrowserGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\OsGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\DeviceTypeGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\DeviceModelGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ReferrerGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ReferrerChannelGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\PageGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\VisitorGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\OnlineVisitorGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ContinentGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\LanguageGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ResolutionGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\SearchTermGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\EntryPageGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\AuthorGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\TaxonomyGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ExclusionReasonGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ExclusionDateGroupBy;

/**
 * Registry for analytics group by.
 *
 * @since 15.0.0
 */
class GroupByRegistry implements RegistryInterface
{
    /**
     * Registered group by instances (lazy loaded).
     *
     * @var array<string, GroupByInterface>
     */
    private $groupBy = [];

    /**
     * Registered group by class names for lazy loading.
     *
     * @var array<string, string>
     */
    private $groupByClasses = [];

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Whether defaults have been registered.
     *
     * @var bool
     */
    private $defaultsRegistered = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->registerDefaults();
    }

    /**
     * Get singleton instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register default group by using class names for lazy loading.
     *
     * @return void
     */
    private function registerDefaults(): void
    {
        if ($this->defaultsRegistered) {
            return;
        }

        // Register class names for lazy instantiation
        $this->groupByClasses = [
            'date'             => DateGroupBy::class,
            'week'             => WeekGroupBy::class,
            'month'            => MonthGroupBy::class,
            'hour'             => HourGroupBy::class,
            'country'          => CountryGroupBy::class,
            'city'             => CityGroupBy::class,
            'region'           => RegionGroupBy::class,
            'browser'          => BrowserGroupBy::class,
            'os'               => OsGroupBy::class,
            'device_type'      => DeviceTypeGroupBy::class,
            'device_model'     => DeviceModelGroupBy::class,
            'referrer'         => ReferrerGroupBy::class,
            'referrer_channel' => ReferrerChannelGroupBy::class,
            'page'             => PageGroupBy::class,
            'visitor'          => VisitorGroupBy::class,
            'online_visitor'   => OnlineVisitorGroupBy::class,
            'continent'        => ContinentGroupBy::class,
            'language'         => LanguageGroupBy::class,
            'resolution'       => ResolutionGroupBy::class,
            'search_term'      => SearchTermGroupBy::class,
            'entry_page'       => EntryPageGroupBy::class,
            'author'           => AuthorGroupBy::class,
            'taxonomy'         => TaxonomyGroupBy::class,
            'exclusion_reason' => ExclusionReasonGroupBy::class,
            'exclusion_date'   => ExclusionDateGroupBy::class,
        ];

        $this->defaultsRegistered = true;
    }

    /**
     * Resolve a group by instance (lazy loading).
     *
     * @param string $name Group by name.
     * @return GroupByInterface|null
     */
    private function resolve(string $name): ?GroupByInterface
    {
        // Already instantiated
        if (isset($this->groupBy[$name])) {
            return $this->groupBy[$name];
        }

        // Create instance from class name
        if (isset($this->groupByClasses[$name])) {
            $this->groupBy[$name] = new $this->groupByClasses[$name]();
            return $this->groupBy[$name];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $name, $item): void
    {
        if (!$item instanceof GroupByInterface) {
            throw new \InvalidArgumentException('Item must implement GroupByInterface');
        }
        // Remove from class registry if it was there (instance takes precedence)
        unset($this->groupByClasses[$name]);

        $this->groupBy[$name] = $item;
    }

    /**
     * Register a group by class for lazy loading.
     *
     * @param string $name      Group by name.
     * @param string $className Fully qualified class name.
     * @return void
     */
    public function registerClass(string $name, string $className): void
    {
        $this->groupByClasses[$name] = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return isset($this->groupBy[$name]) || isset($this->groupByClasses[$name]);
    }

    /**
     * {@inheritdoc}
     *
     * @return GroupByInterface|null
     */
    public function get(string $name): ?GroupByInterface
    {
        return $this->resolve($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return array_unique(array_merge(
            array_keys($this->groupBy),
            array_keys($this->groupByClasses)
        ));
    }

    /**
     * Get SELECT columns for a group by.
     *
     * @param string $name Group by name.
     * @return array
     */
    public function getSelectColumns(string $name): array
    {
        $groupByItem = $this->get($name);
        return $groupByItem ? $groupByItem->getSelectColumns() : [];
    }

    /**
     * Get JOINs for a group by.
     *
     * @param string $name Group by name.
     * @return array
     */
    public function getJoins(string $name): array
    {
        $groupByItem = $this->get($name);
        return $groupByItem ? $groupByItem->getJoins() : [];
    }

    /**
     * Get GROUP BY for a group by.
     *
     * @param string $name Group by name.
     * @return string|null
     */
    public function getGroupBy(string $name): ?string
    {
        $groupByItem = $this->get($name);
        return $groupByItem ? $groupByItem->getGroupBy() : null;
    }

    /**
     * Get ORDER for a group by.
     *
     * @param string $name Group by name.
     * @return string
     */
    public function getOrder(string $name): string
    {
        $groupByItem = $this->get($name);
        return $groupByItem ? $groupByItem->getOrder() : 'DESC';
    }

    /**
     * Get filter for a group by.
     *
     * @param string $name Group by name.
     * @return string|null
     */
    public function getFilter(string $name): ?string
    {
        $groupByItem = $this->get($name);
        return $groupByItem ? $groupByItem->getFilter() : null;
    }

    /**
     * Get requirement for a group by.
     *
     * @param string $name Group by name.
     * @return string|null
     */
    public function getRequirement(string $name): ?string
    {
        $groupByItem = $this->get($name);
        return $groupByItem ? $groupByItem->getRequirement() : null;
    }

    /**
     * Check if any group by requires views table.
     *
     * @param array $groupBy Group by WP_Statistics_names.
     * @return bool
     */
    public function requiresViewsTable(array $groupBy): bool
    {
        foreach ($groupBy as $name) {
            if ($this->getRequirement($name) === 'views') {
                return true;
            }
        }

        return false;
    }
}
