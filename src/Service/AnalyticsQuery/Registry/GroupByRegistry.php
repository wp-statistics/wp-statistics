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
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ReferrerGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\PageGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\VisitorGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\OnlineVisitorGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ContinentGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\LanguageGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\ResolutionGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\SearchTermGroupBy;
use WP_Statistics\Service\AnalyticsQuery\GroupBy\EntryPageGroupBy;

/**
 * Registry for analytics group by.
 *
 * @since 15.0.0
 */
class GroupByRegistry implements RegistryInterface
{
    /**
     * Registered group by.
     *
     * @var array<string, GroupByInterface>
     */
    private $groupBy = [];

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static $instance = null;

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
     * Register default group by.
     *
     * @return void
     */
    private function registerDefaults(): void
    {
        $defaults = [
            new DateGroupBy(),
            new WeekGroupBy(),
            new MonthGroupBy(),
            new HourGroupBy(),
            new CountryGroupBy(),
            new CityGroupBy(),
            new RegionGroupBy(),
            new BrowserGroupBy(),
            new OsGroupBy(),
            new DeviceTypeGroupBy(),
            new ReferrerGroupBy(),
            new PageGroupBy(),
            new VisitorGroupBy(),
            new OnlineVisitorGroupBy(),
            new ContinentGroupBy(),
            new LanguageGroupBy(),
            new ResolutionGroupBy(),
            new SearchTermGroupBy(),
            new EntryPageGroupBy(),
        ];

        foreach ($defaults as $groupByItem) {
            $this->register($groupByItem->getName(), $groupByItem);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $name, $item): void
    {
        if (!$item instanceof GroupByInterface) {
            throw new \InvalidArgumentException('Item must implement GroupByInterface');
        }

        $this->groupBy[$name] = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return isset($this->groupBy[$name]);
    }

    /**
     * {@inheritdoc}
     *
     * @return GroupByInterface|null
     */
    public function get(string $name): ?GroupByInterface
    {
        return $this->groupBy[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return array_keys($this->groupBy);
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
     * @param array $groupBy Group by names.
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
