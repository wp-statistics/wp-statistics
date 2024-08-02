<?php

namespace WP_Statistics\Service\Geolocation\Provider;

abstract class AbstractGeoIPProvider implements GeoServiceProviderInterface
{
    /**
     * @var string
     */
    protected $databasePath;

    /**
     * Get the path to the GeoIP database.
     *
     * @return string
     */
    public function getDatabasePath(): string
    {
        return $this->databasePath;
    }

    /**
     * Delete the existing GeoIP database.
     *
     * @return bool
     */
    public function deleteDatabase(): bool
    {
        if (file_exists($this->databasePath)) {
            if (unlink($this->databasePath)) {
                return true;
            }

            return false;
        }

        return true; // If the file does not exist, treat it as already deleted
    }

    /**
     * Get information about the GeoIP database file.
     *
     * @return array|null
     */
    public function getDatabaseFileInfo(): ?array
    {
        if (file_exists($this->databasePath)) {
            $sizeInBytes     = filesize($this->databasePath);
            $sizeInMegabytes = $sizeInBytes / (1024 * 1024); // Convert bytes to megabytes

            return [
                'size'          => round($sizeInMegabytes, 2) . ' MB', // File size in megabytes
                'last_modified' => date('Y-m-d H:i:s', filemtime($this->databasePath)), // Last modified timestamp formatted
            ];
        }

        return null; // Return null if the file does not exist
    }

    /**
     * Check if the GeoIP database exists.
     *
     * @return bool
     */
    public function databaseExists(): bool
    {
        return file_exists($this->databasePath);
    }

    /**
     * Backup the existing GeoIP database.
     *
     * @return bool
     */
    public function backupDatabase(): bool
    {
        if ($this->databaseExists()) {
            $backupPath = $this->databasePath . '.bak';
            return copy($this->databasePath, $backupPath);
        }

        return false;
    }

    /**
     * Validate the GeoIP database integrity.
     *
     * @return bool
     */
    public function validateDatabase(): bool
    {
        return $this->databaseExists() && is_readable($this->databasePath);
    }
}
