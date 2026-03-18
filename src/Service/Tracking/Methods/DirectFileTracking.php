<?php

namespace WP_Statistics\Service\Tracking\Methods;

use WP_Statistics\Service\Tracking\DirectEndpoint\DirectEndpointManager;

/**
 * Direct File delivery method.
 *
 * Uses a SHORTINIT mu-plugin endpoint for minimal-bootstrap hit recording.
 * Hit and batch requests both go to the same mu-plugin URL.
 *
 * @since 15.1.0
 */
class DirectFileTracking extends BaseTracking
{
    /**
     * @var DirectEndpointManager
     */
    private $endpointManager;

    /**
     * Cached endpoint URL — avoids repeated filesystem checks.
     *
     * @var string|null
     */
    private $cachedUrl;

    public function __construct()
    {
        $this->endpointManager = new DirectEndpointManager();
    }

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        $this->endpointManager->ensureInstalled();
    }

    /**
     * {@inheritDoc}
     */
    public function getHitUrl(): string
    {
        if ($this->cachedUrl !== null) {
            return $this->cachedUrl;
        }

        $this->cachedUrl = $this->endpointManager->isInstalled()
            ? $this->endpointManager->getEndpointUrl()
            : '';

        return $this->cachedUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function getBatchUrl(): string
    {
        return $this->getHitUrl();
    }

    /**
     * {@inheritDoc}
     */
    public function getRoute(): ?string
    {
        return null;
    }
}
