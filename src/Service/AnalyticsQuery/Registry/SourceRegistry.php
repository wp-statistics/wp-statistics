<?php

namespace WP_Statistics\Service\AnalyticsQuery\Registry;

use WP_Statistics\Service\AnalyticsQuery\Contracts\SourceInterface;
use WP_Statistics\Service\AnalyticsQuery\Contracts\RegistryInterface;
use WP_Statistics\Service\AnalyticsQuery\Sources\VisitorsSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\ViewsSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\SessionsSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\BounceRateSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\AvgSessionDurationSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\PagesPerSessionSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\AvgTimeOnPageSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\TotalDurationSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\VisitorStatusSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\SearchesSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\EventsSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\ExclusionsSource;
use WP_Statistics\Service\AnalyticsQuery\Sources\OnlineVisitorsSource;

/**
 * Registry for analytics sources.
 *
 * Uses lazy loading to only instantiate source objects when they are first accessed.
 * This improves performance by deferring object creation until actually needed.
 *
 * @since 15.0.0
 */
class SourceRegistry implements RegistryInterface
{
    /**
     * Registered source instances (lazy loaded).
     *
     * @var array<string, SourceInterface>
     */
    private $sources = [];

    /**
     * Registered source class names for lazy loading.
     *
     * @var array<string, string>
     */
    private $sourceClasses = [];

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
     * Register default sources using class names for lazy loading.
     *
     * @return void
     */
    private function registerDefaults(): void
    {
        if ($this->defaultsRegistered) {
            return;
        }

        // Register class names for lazy instantiation
        $this->sourceClasses = [
            'visitors'             => VisitorsSource::class,
            'views'                => ViewsSource::class,
            'sessions'             => SessionsSource::class,
            'bounce_rate'          => BounceRateSource::class,
            'avg_session_duration' => AvgSessionDurationSource::class,
            'pages_per_session'    => PagesPerSessionSource::class,
            'avg_time_on_page'     => AvgTimeOnPageSource::class,
            'total_duration'       => TotalDurationSource::class,
            'visitor_status'       => VisitorStatusSource::class,
            'searches'             => SearchesSource::class,
            'events'               => EventsSource::class,
            'exclusions'           => ExclusionsSource::class,
            'online_visitors'      => OnlineVisitorsSource::class,
        ];

        $this->defaultsRegistered = true;
    }

    /**
     * Resolve a source instance (lazy loading).
     *
     * @param string $name Source name.
     * @return SourceInterface|null
     */
    private function resolve(string $name): ?SourceInterface
    {
        // Already instantiated
        if (isset($this->sources[$name])) {
            return $this->sources[$name];
        }

        // Create instance from class name
        if (isset($this->sourceClasses[$name])) {
            $this->sources[$name] = new $this->sourceClasses[$name]();
            return $this->sources[$name];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $name, $item): void
    {
        if (!$item instanceof SourceInterface) {
            throw new \InvalidArgumentException('Item must implement SourceInterface');
        }

        // Remove from class registry if it was there (instance takes precedence)
        unset($this->sourceClasses[$name]);

        $this->sources[$name] = $item;
    }

    /**
     * Register a source class for lazy loading.
     *
     * @param string $name      Source name.
     * @param string $className Fully qualified class name.
     * @return void
     */
    public function registerClass(string $name, string $className): void
    {
        $this->sourceClasses[$name] = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return isset($this->sources[$name]) || isset($this->sourceClasses[$name]);
    }

    /**
     * {@inheritdoc}
     *
     * @return SourceInterface|null
     */
    public function get(string $name): ?SourceInterface
    {
        return $this->resolve($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return array_unique(array_merge(
            array_keys($this->sources),
            array_keys($this->sourceClasses)
        ));
    }

    /**
     * Get SQL expression for a source.
     *
     * @param string $name Source name.
     * @return string
     */
    public function getExpression(string $name): string
    {
        $source = $this->get($name);
        return $source ? $source->getExpressionWithAlias() : '';
    }

    /**
     * Get table for a source.
     *
     * @param string $name Source name.
     * @return string
     */
    public function getTable(string $name): string
    {
        $source = $this->get($name);
        return $source ? $source->getTable() : 'sessions';
    }

    /**
     * Get type for a source.
     *
     * @param string $name Source name.
     * @return string
     */
    public function getType(string $name): string
    {
        $source = $this->get($name);
        return $source ? $source->getType() : 'integer';
    }

    /**
     * Get format for a source.
     *
     * @param string $name Source name.
     * @return string
     */
    public function getFormat(string $name): string
    {
        $source = $this->get($name);
        return $source ? $source->getFormat() : 'number';
    }

    /**
     * Determine primary table for sources.
     *
     * @param array $sources Source WP_Statistics_names.
     * @return string
     */
    public function determinePrimaryTable(array $sources): string
    {
        foreach ($sources as $name) {
            if ($this->getTable($name) === 'views') {
                return 'views';
            }
        }

        return 'sessions';
    }
}
