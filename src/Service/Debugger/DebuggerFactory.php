<?php

namespace WP_Statistics\Service\Debugger;

use WP_Statistics\Service\Debugger\Provider\ErrorsDetectorProvider;
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
    public function createOptionsProvider()
    {
        return new OptionsProvider();
    }

    /**
     * Creates an instance of the TrackerProvider.
     *
     * @return DebuggerServiceProviderInterface An instance of TrackerProvider.
     */
    public function createTrackerProvider()
    {
        return new TrackerProvider();
    }

    /**
     * Creates an instance of the VisitorProvider.
     *
     * @return DebuggerServiceProviderInterface An instance of VisitorProvider.
     */
    public function createVisitorProvider()
    {
        return new VisitorProvider();
    }

    /**
     * Creates an instance of the ErrorsDetectorProvider.
     *
     * @return DebuggerServiceProviderInterface An instance of ErrorsDetectorProvider.
     */
    public function createErrorDetectorProvider()
    {
        return new ErrorsDetectorProvider();
    }

    /**
     * Retrieves all available debugger service providers.
     *
     * @return array An associative array of debugger service providers.
     */
    public function getAllProviders()
    {
        return [
            'options' => $this->createOptionsProvider(),
            'tracker' => $this->createTrackerProvider(),
            'visitors' => $this->createVisitorProvider(),
            'errors' => $this->createErrorDetectorProvider(),
        ];
    }
}
