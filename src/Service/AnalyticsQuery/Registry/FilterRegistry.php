<?php

namespace WP_Statistics\Service\AnalyticsQuery\Registry;

use WP_Statistics\Service\AnalyticsQuery\Contracts\FilterInterface;
use WP_Statistics\Service\AnalyticsQuery\Filters\CountryFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\CountryIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\CityFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ContinentFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\RegionFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\BrowserFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\BrowserVersionFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\BrowserVersionIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\OsFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\DeviceTypeFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerTypeFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerChannelFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerDomainFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerNameFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\PostTypeFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\AuthorIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\UserIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\LoggedInFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\PageFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ResourceIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\LanguageFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\IpFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ResolutionFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ResolutionIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\TimezoneFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\TimezoneIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\VisitorIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\SessionIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\UserRoleFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\VisitorTypeFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\SessionDurationFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ViewsPerSessionFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\TotalViewsFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\TotalSessionsFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\FirstSeenFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\LastSeenFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\BounceFilter;

/**
 * Registry for analytics query filters.
 *
 * @since 15.0.0
 */
class FilterRegistry
{
    /**
     * Registered filters.
     *
     * @var array<string, FilterInterface>
     */
    private $filters = [];

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
     * Register default filters.
     *
     * @return void
     */
    private function registerDefaults(): void
    {
        $defaults = [
            // Geographic filters
            new CountryFilter(),
            new CountryIdFilter(),
            new ContinentFilter(),
            new CityFilter(),
            new RegionFilter(),

            // Device filters
            new BrowserFilter(),
            new BrowserVersionFilter(),
            new BrowserVersionIdFilter(),
            new OsFilter(),
            new DeviceTypeFilter(),
            new ResolutionFilter(),
            new ResolutionIdFilter(),

            // Referrer filters
            new ReferrerFilter(),
            new ReferrerTypeFilter(),
            new ReferrerIdFilter(),
            new ReferrerChannelFilter(),
            new ReferrerDomainFilter(),
            new ReferrerNameFilter(),

            // Content filters
            new PostTypeFilter(),
            new AuthorIdFilter(),
            new PageFilter(),
            new ResourceIdFilter(),

            // Visitor/session filters
            new UserIdFilter(),
            new LoggedInFilter(),
            new VisitorIdFilter(),
            new SessionIdFilter(),
            new IpFilter(),
            new UserRoleFilter(),
            new VisitorTypeFilter(),
            new SessionDurationFilter(),
            new ViewsPerSessionFilter(),
            new TotalViewsFilter(),
            new TotalSessionsFilter(),
            new FirstSeenFilter(),
            new LastSeenFilter(),
            new BounceFilter(),

            // User preference filters
            new LanguageFilter(),
            new TimezoneFilter(),
            new TimezoneIdFilter(),
        ];

        foreach ($defaults as $filter) {
            $this->register($filter->getName(), $filter);
        }

        /**
         * Allow third-party plugins to register custom filters.
         *
         * @param FilterRegistry $registry The filter registry instance.
         * @since 15.0.0
         */
        do_action('wp_statistics_register_filters', $this);
    }

    /**
     * Register a filter.
     *
     * @param string          $name   Filter name.
     * @param FilterInterface $filter Filter instance.
     * @return void
     */
    public function register(string $name, FilterInterface $filter): void
    {
        $this->filters[$name] = $filter;
    }

    /**
     * Check if a filter exists.
     *
     * @param string $name Filter name.
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->filters[$name]);
    }

    /**
     * Get a filter by name.
     *
     * @param string $name Filter name.
     * @return FilterInterface|null
     */
    public function get(string $name): ?FilterInterface
    {
        return $this->filters[$name] ?? null;
    }

    /**
     * Get all registered filter names.
     *
     * @return array
     */
    public function getAll(): array
    {
        return array_keys($this->filters);
    }

    /**
     * Get all filters as configuration array.
     *
     * Useful for serialization and sending to React frontend.
     *
     * @return array
     */
    public function getAllAsArray(): array
    {
        $result = [];
        foreach ($this->filters as $name => $filter) {
            $result[$name] = $filter->toArray();
        }
        return $result;
    }

    /**
     * Get column for a filter.
     *
     * @param string $name Filter name.
     * @return string|null
     */
    public function getColumn(string $name): ?string
    {
        $filter = $this->get($name);
        return $filter ? $filter->getColumn() : null;
    }

    /**
     * Get type for a filter.
     *
     * @param string $name Filter name.
     * @return string
     */
    public function getType(string $name): string
    {
        $filter = $this->get($name);
        return $filter ? $filter->getType() : 'string';
    }

    /**
     * Get joins for a filter.
     *
     * @param string $name Filter name.
     * @return array|null
     */
    public function getJoins(string $name): ?array
    {
        $filter = $this->get($name);
        return $filter ? $filter->getJoins() : null;
    }

    /**
     * Get requirement for a filter.
     *
     * @param string $name Filter name.
     * @return string|null
     */
    public function getRequirement(string $name): ?string
    {
        $filter = $this->get($name);
        return $filter ? $filter->getRequirement() : null;
    }

    /**
     * Check if any filter requires the views table.
     *
     * @param array $filterNames Filter names to check.
     * @return bool
     */
    public function requiresViewsTable(array $filterNames): bool
    {
        foreach ($filterNames as $name) {
            if ($this->getRequirement($name) === 'views') {
                return true;
            }
        }

        return false;
    }
}
