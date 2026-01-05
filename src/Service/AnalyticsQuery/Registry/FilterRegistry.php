<?php

namespace WP_Statistics\Service\AnalyticsQuery\Registry;

use WP_Statistics\Service\AnalyticsQuery\Contracts\FilterInterface;
use WP_Statistics\Service\AnalyticsQuery\Filters\CountryFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\CityFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ContinentFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\RegionFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\BrowserFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\BrowserVersionFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\OsFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\DeviceTypeFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerTypeFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerChannelFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerDomainFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ReferrerNameFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\PostTypeFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\AuthorFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\UserIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\LoggedInFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\PageFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ResourceIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\LanguageFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\IpFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ResolutionFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\TimezoneFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\UserRoleFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\VisitorTypeFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\SessionDurationFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\ViewsPerSessionFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\TotalViewsFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\TotalSessionsFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\FirstSeenFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\LastSeenFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\BounceFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\EventNameFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\EventPageIdFilter;
use WP_Statistics\Service\AnalyticsQuery\Filters\TaxonomyTypeFilter;

/**
 * Registry for analytics query filters.
 *
 * Uses lazy loading to only instantiate filter objects when they are first accessed.
 * This improves performance by deferring object creation until actually needed.
 *
 * @since 15.0.0
 */
class FilterRegistry
{
    /**
     * Registered filter instances (lazy loaded).
     *
     * @var array<string, FilterInterface>
     */
    private $filters = [];

    /**
     * Registered filter class names for lazy loading.
     *
     * @var array<string, string>
     */
    private $filterClasses = [];

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
     * Register default filters using class names for lazy loading.
     *
     * @return void
     */
    private function registerDefaults(): void
    {
        if ($this->defaultsRegistered) {
            return;
        }

        // Register class names for lazy instantiation
        $defaults = [
            // Geographic filters
            'country'          => CountryFilter::class,
            'continent'        => ContinentFilter::class,
            'city'             => CityFilter::class,
            'region'           => RegionFilter::class,

            // Device filters
            'browser'          => BrowserFilter::class,
            'browser_version'  => BrowserVersionFilter::class,
            'os'               => OsFilter::class,
            'device_type'      => DeviceTypeFilter::class,
            'resolution'       => ResolutionFilter::class,

            // Referrer filters
            'referrer'         => ReferrerFilter::class,
            'referrer_type'    => ReferrerTypeFilter::class,
            'referrer_channel' => ReferrerChannelFilter::class,
            'referrer_domain'  => ReferrerDomainFilter::class,
            'referrer_name'    => ReferrerNameFilter::class,

            // Content filters
            'post_type'        => PostTypeFilter::class,
            'author'           => AuthorFilter::class,
            'taxonomy_type'    => TaxonomyTypeFilter::class,
            'page'             => PageFilter::class,
            'resource_id'      => ResourceIdFilter::class,

            // Visitor/session filters
            'user_id'          => UserIdFilter::class,
            'logged_in'        => LoggedInFilter::class,
            'ip'               => IpFilter::class,
            'user_role'        => UserRoleFilter::class,
            'visitor_type'     => VisitorTypeFilter::class,
            'session_duration' => SessionDurationFilter::class,
            'views_per_session' => ViewsPerSessionFilter::class,
            'total_views'      => TotalViewsFilter::class,
            'total_sessions'   => TotalSessionsFilter::class,
            'first_seen'       => FirstSeenFilter::class,
            'last_seen'        => LastSeenFilter::class,
            'bounce'           => BounceFilter::class,

            // User preference filters
            'language'         => LanguageFilter::class,
            'timezone'         => TimezoneFilter::class,

            // Event filters
            'event_name'       => EventNameFilter::class,
            'event_page_id'    => EventPageIdFilter::class,
        ];

        $this->filterClasses = $defaults;
        $this->defaultsRegistered = true;

        /**
         * Allow third-party plugins to register custom filters.
         *
         * @param FilterRegistry $registry The filter registry instance.
         * @since 15.0.0
         */
        do_action('wp_statistics_register_filters', $this);
    }

    /**
     * Resolve a filter instance (lazy loading).
     *
     * @param string $name Filter name.
     * @return FilterInterface|null
     */
    private function resolve(string $name): ?FilterInterface
    {
        // Already instantiated
        if (isset($this->filters[$name])) {
            return $this->filters[$name];
        }

        // Create instance from class name
        if (isset($this->filterClasses[$name])) {
            $this->filters[$name] = new $this->filterClasses[$name]();
            return $this->filters[$name];
        }

        return null;
    }

    /**
     * Register a filter instance directly.
     *
     * Used by third-party plugins to register custom filters.
     *
     * @param string          $name   Filter name.
     * @param FilterInterface $filter Filter instance.
     * @return void
     */
    public function register(string $name, FilterInterface $filter): void
    {
        $this->filters[$name] = $filter;
        // Remove from class registry if it was there (instance takes precedence)
        unset($this->filterClasses[$name]);
    }

    /**
     * Register a filter class for lazy loading.
     *
     * @param string $name      Filter name.
     * @param string $className Fully qualified class name.
     * @return void
     */
    public function registerClass(string $name, string $className): void
    {
        $this->filterClasses[$name] = $className;
    }

    /**
     * Check if a filter exists.
     *
     * @param string $name Filter name.
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->filters[$name]) || isset($this->filterClasses[$name]);
    }

    /**
     * Get a filter by name (lazy loading).
     *
     * @param string $name Filter name.
     * @return FilterInterface|null
     */
    public function get(string $name): ?FilterInterface
    {
        return $this->resolve($name);
    }

    /**
     * Get all registered filter names.
     *
     * @return array
     */
    public function getAll(): array
    {
        return array_unique(array_merge(
            array_keys($this->filters),
            array_keys($this->filterClasses)
        ));
    }

    /**
     * Get all filters as configuration array.
     *
     * Includes all filter data including backend-only fields.
     * Note: This instantiates all filters.
     *
     * @return array
     */
    public function getAllAsArray(): array
    {
        $result = [];
        foreach ($this->getAll() as $name) {
            $filter = $this->resolve($name);
            if ($filter) {
                $result[$name] = $filter->toArray();
            }
        }
        return $result;
    }

    /**
     * Get all filters for frontend consumption.
     *
     * Excludes backend-only fields (column, joins, type, requirement).
     * Only includes what React needs to render the filter UI.
     * Note: This instantiates all filters.
     *
     * @return array
     */
    public function getAllForFrontend(): array
    {
        $result = [];
        foreach ($this->getAll() as $name) {
            $filter = $this->resolve($name);
            if ($filter) {
                $result[$name] = $filter->toFrontendArray();
            }
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
     * @param array $filterNames Filter WP_Statistics_names to check.
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

    /**
     * Check if any filter requires the events table.
     *
     * @param array $filterNames Filter WP_Statistics_names to check.
     * @return bool
     */
    public function requiresEventsTable(array $filterNames): bool
    {
        foreach ($filterNames as $name) {
            if ($this->getRequirement($name) === 'events') {
                return true;
            }
        }

        return false;
    }
}
