<?php

namespace WP_Statistics\Interfaces;

/**
 * Interface ExportDriveInterface
 *
 * Defines a contract for exporting data to an external drive or destination.
 * Classes implementing this interface must define the export method that returns JSON data.
 *
 * @package WP_Statistics\Interfaces
 */
interface ExportDriveInterface
{
    /**
     * Export data to a drive or destination.
     *
     * This method should contain the logic to export data from the system,
     * returning the result as a JSON-decoded associative array.
     *
     * @return array
     */
    public function export();
}