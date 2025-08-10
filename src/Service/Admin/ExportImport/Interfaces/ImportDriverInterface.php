<?php

namespace WP_Statistics\Service\Admin\ExportImport\Interfaces;

use WP_REST_Request;

/**
 * Interface ImportDriveInterface
 *
 * Defines a contract for importing data from an external drive or source.
 * Classes implementing this interface must define the import method.
 *
 * @package WP_Statistics\Interfaces
 */
interface ImportDriverInterface
{
    /**
     * Import data from a drive or source.
     *
     * This method should contain the logic to import data into the system,
     * whether from a file, API, or other external source.
     *
     * @param WP_REST_Request $request
     *
     * @return array
     */
    public function import(WP_REST_Request $request);
}