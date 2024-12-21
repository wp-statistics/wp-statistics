<?php

namespace WP_Statistics\Service\Debugger;

use WP_Statistics\Service\Debugger\Provider\ErrorDetectorProvider;
use WP_Statistics\Service\Debugger\Provider\OptionsProvider;
use WP_Statistics\Service\Debugger\Provider\TrackerProvider;
use WP_Statistics\Service\Debugger\Provider\VisitorProvider;

/**
 * Factory class for creating debugger service providers.
 *
 * This class provides methods to create instances of various debugger service providers
 * and retrieve all available providers.
 */
class DebuggerFactory
{
    /**
     * Creates an instance of the OptionsProvider.
     *
     * @return DebuggerServiceProviderInterface An instance of OptionsProvider.
     */
    public function createOptionsProvider(): DebuggerServiceProviderInterface
    {
        return new OptionsProvider();
    }

    /**
     * Creates an instance of the TrackerProvider.
     *
     * @return DebuggerServiceProviderInterface An instance of TrackerProvider.
     */
    public function createTrackerProvider(): DebuggerServiceProviderInterface
    {
        return new TrackerProvider();
    }

    /**
     * Creates an instance of the VisitorProvider.
     *
     * @return DebuggerServiceProviderInterface An instance of VisitorProvider.
     */
    public function createVisitorProvider(): DebuggerServiceProviderInterface
    {
        return new VisitorProvider();
    }

    /**
     * Creates an instance of the ErrorDetectorProvider.
     *
     * @return DebuggerServiceProviderInterface An instance of ErrorDetectorProvider.
     */
    public function createErrorDetectorProvider(): DebuggerServiceProviderInterface
    {
        return new ErrorDetectorProvider();
    }

    /**
     * Retrieves all available debugger service providers.
     *
     * @return array An associative array of debugger service providers.
     */
    public function getAllProviders(): array
    {
        return [
            'options' => $this->createOptionsProvider(),
            'tracker' => $this->createTrackerProvider(),
            'visitor' => $this->createVisitorProvider(),
            'errors' => $this->createErrorDetectorProvider(),
        ];
    }
}