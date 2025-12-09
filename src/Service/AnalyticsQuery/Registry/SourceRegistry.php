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

/**
 * Registry for analytics sources.
 *
 * @since 15.0.0
 */
class SourceRegistry implements RegistryInterface
{
    /**
     * Registered sources.
     *
     * @var array<string, SourceInterface>
     */
    private $sources = [];

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
     * Register default sources.
     *
     * @return void
     */
    private function registerDefaults(): void
    {
        $defaults = [
            new VisitorsSource(),
            new ViewsSource(),
            new SessionsSource(),
            new BounceRateSource(),
            new AvgSessionDurationSource(),
            new PagesPerSessionSource(),
            new AvgTimeOnPageSource(),
            new TotalDurationSource(),
        ];

        foreach ($defaults as $source) {
            $this->register($source->getName(), $source);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $name, $item): void
    {
        if (!$item instanceof SourceInterface) {
            throw new \InvalidArgumentException('Item must implement SourceInterface');
        }

        $this->sources[$name] = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return isset($this->sources[$name]);
    }

    /**
     * {@inheritdoc}
     *
     * @return SourceInterface|null
     */
    public function get(string $name): ?SourceInterface
    {
        return $this->sources[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return array_keys($this->sources);
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
     * @param array $sources Source names.
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
