<?php

namespace WP_Statistics\Service\Tracking\Methods\DirectFile;

use WP_Statistics\Service\Tracking\Methods\BaseTracking;

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
     * @var DirectFileHandler
     */
    private $handler;

    public function __construct()
    {
        $this->handler = new DirectFileHandler();
    }

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        $this->handler->ensureInstalled();
    }

    /**
     * {@inheritDoc}
     */
    public function getTrackerConfig(): array
    {
        return [
            'baseUrl'      => content_url(),
            'hitEndpoint'  => '/mu-plugins/' . DirectFileHandler::ENDPOINT_FILE,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRoute(): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function activate(): void
    {
        $this->handler->reinstall();
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate(): void
    {
        $this->handler->uninstall();
    }
}
