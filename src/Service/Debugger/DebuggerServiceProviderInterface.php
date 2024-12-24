<?php

namespace WP_Statistics\Service\Debugger;

/**
 * Interface for debugger service providers.
 *
 * This interface defines the methods that debugger service providers must implement
 * to provide options, tracker status, and visitor data for debugging purposes.
 */
interface DebuggerServiceProviderInterface
{
    /**
     * Retrieves the debugger options.
     *
     * @return array An array of debugger options.
     */
    public function getOptions();

    /**
     * Retrieves the tracker status.
     *
     * @return array An array representing the tracker status.
     */
    public function getTrackerStatus();

    /**
     * Retrieves the visitor data.
     *
     * @return array An array containing visitor data.
     */
    public function getVisitorData();

    /**
     * Retrieves the log data.
     *
     * @return array An array containing logs data.
     */
    public function getErrors();
}
