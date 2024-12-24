<?php

namespace WP_Statistics\Service\Debugger;

use WP_Statistics\Service\Debugger\DebuggerServiceProviderInterface;

/**
 * Abstract base class for debugger service providers
 *
 * This abstract class provides default implementations of the DebuggerServiceProviderInterface.
 * Classes extending this can override these methods to provide specific functionality.
 * All methods return empty arrays by default to ensure a consistent interface.
 */
abstract class AbstractDebuggerProvider implements DebuggerServiceProviderInterface
{
    /**
     * Get the debugger options
     *
     * @return array An empty array as default implementation
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * Get the tracker status
     *
     * @return array An empty array as default implementation
     */
    public function getTrackerStatus()
    {
        return [];
    }

    /**
     * Get the visitor data
     *
     * @return array An empty array as default implementation
     */
    public function getVisitorData()
    {
        return [];
    }

    /**
     * Get the logs data
     *
     * @return array An empty array as default implementation
     */
    public function getErrors()
    {
        return [];
    }
}
