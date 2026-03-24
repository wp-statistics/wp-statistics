<?php

namespace WP_Statistics\Service\Tracking\Methods\HybridMode;

use WP_Statistics\Service\Tracking\Methods\BaseTracker;

/**
 * Hybrid Mode tracking method.
 *
 * Uses a SHORTINIT mu-plugin endpoint for minimal-bootstrap hit recording.
 * Hit and batch requests both go to the same mu-plugin URL.
 *
 * @since 15.1.0
 */
class HybridModeTracker extends BaseTracker
{
    /**
     * @var HybridModeHandler
     */
    private $handler;

    public function __construct()
    {
        $this->handler = new HybridModeHandler();
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
            'hitEndpoint'  => '/mu-plugins/' . HybridModeHandler::ENDPOINT_FILE,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodType(): string
    {
        return 'hybrid';
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
