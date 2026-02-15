<?php

namespace WP_Statistics\Utils;

use RuntimeException;

/**
 * Sends a file for HTTP download.
 *
 * Centralizes the duplicated download-header logic from ImportExport endpoints.
 *
 * @since 15.0.0
 */
class FileDownloadHelper
{
    /**
     * Send a file for download and exit.
     *
     * @param string $filePath    Absolute path to the file.
     * @param string $filename    Download filename shown to the user.
     * @param bool   $deleteAfter Whether to delete the file after sending.
     * @return never
     * @throws RuntimeException If the file does not exist.
     */
    public static function send(string $filePath, string $filename, bool $deleteAfter = false): void
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException(__('File not found.', 'wp-statistics'));
        }

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($filePath);

        if ($deleteAfter) {
            @unlink($filePath);
        }

        exit;
    }
}
