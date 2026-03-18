<?php

namespace WP_Statistics\Service\Tracking\Methods;

use WP_Statistics\Service\Tracking\DirectEndpoint\DirectEndpointManager;

/**
 * Direct File tracking method.
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
    public function getTrackerConfig(): array
    {
        return [
            'baseUrl'          => content_url(),
            'hitEndpoint'      => '/mu-plugins/' . DirectEndpointManager::ENDPOINT_FILE,
            'batchEndpoint'    => '/mu-plugins/' . DirectEndpointManager::ENDPOINT_FILE,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRoute(): ?string
    {
        return null;
    }
}
