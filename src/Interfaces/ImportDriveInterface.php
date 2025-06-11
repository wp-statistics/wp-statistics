<?php

namespace WP_Statistics\Interfaces;

/**
 * Interface ImportDriveInterface
 *
 * Defines a contract for importing data from an external drive or source.
 * Classes implementing this interface must define the import method.
 *
 * @package WP_Statistics\Interfaces
 */
interface ImportDriveInterface
{
    /**
     * Import data from a drive or source.
     *
     * This method should contain the logic to import data into the system,
     * whether from a file, API, or other external source.
     *
     * @return array
     */
    public function import();
}