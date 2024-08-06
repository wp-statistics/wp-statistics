<?php

namespace WP_Statistics\Components;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;

/**
 * Ofuscates/Randomizes assets file names.
 */
class AssetNameObfuscator
{
    /**
     * Option that contains information about all hashed files.
     *
     * @var string
     */
    private $optionName = 'hashed_assets';

    /**
     * All hashed files.
     *
     * @var array
     */
    private $hashedAssetsArray = [];

    /**
     * Hashed file's key in options (which is its path relative to `WP_STATISTICS_DIR`).
     *
     * @var string
     */
    private $hashedFileOptionKey;

    /**
     * @var string
     */
    private $inputFileDir;

    /**
     * WordPress /plugins/ directory.
     *
     * @var string
     */
    private $pluginsRoot;

    /**
     * MD5 hashed string of plugin's version + actual file name.
     *
     * @var string
     */
    private $hashedFileName;

    /**
     * Root of the hash files dir.
     *
     * @var string
     */
    private $hashedFilesRootDir;

    /**
     * Full dir of the hashed file.
     *
     * @var string
     */
    private $hashedFileDir;

    /**
     * @param   string  $file   Full path of the input file. 
     * Pass `null` if you only want to use `deleteAllHashedFiles` and `deleteDatabaseOption` methods. (e.g. When uninstalling the plugin)
     *
     * @return  void
     */
    public function __construct($file = null)
    {
        // Handle slashes
        $this->inputFileDir = wp_normalize_path($file);
        $this->pluginsRoot  = wp_normalize_path(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR);

        if (stripos($this->inputFileDir, $this->pluginsRoot) === false) {
            $this->inputFileDir = path_join($this->pluginsRoot, $this->inputFileDir);
        }

        if (!is_file($this->inputFileDir)) return;

        $this->initializeVariables();
        $this->obfuscateFileName();
    }

    /**
     * Initializes class variables.
     *
     * @return  void
     */
    private function initializeVariables()
    {
        $this->hashedAssetsArray   = Option::getOptionGroup($this->optionName, null, []);
        $this->hashedFileOptionKey = str_replace($this->pluginsRoot, '', $this->inputFileDir);

        if (empty($this->hashedAssetsArray[$this->hashedFileOptionKey])) {
            $this->hashedAssetsArray[$this->hashedFileOptionKey]            = [];
            $this->hashedAssetsArray[$this->hashedFileOptionKey]['version'] = WP_STATISTICS_VERSION;
        }

        $this->hashedFileName     = $this->generateShortHash(WP_STATISTICS_VERSION . $this->hashedFileOptionKey);
        $this->hashedFileName     .= '.' . pathinfo($this->inputFileDir, PATHINFO_EXTENSION);
        $this->hashedFileName     = apply_filters('wp_statistics_hashed_asset_name', $this->hashedFileName, $this->inputFileDir);
        $this->hashedFilesRootDir = apply_filters('wp_statistics_hashed_asset_root', Helper::get_uploads_dir());

        if (!is_dir($this->hashedFilesRootDir)) {
            // Try to make the filtered dir if it not exists
            if (!mkdir($this->hashedFilesRootDir, 0700)) {
                // Revert back to default uploads folder if the filtered dir is invalid
                $this->hashedFilesRootDir = Helper::get_uploads_dir();
            }
        }

        $this->hashedFileDir = $this->isHashedFileExists() ? $this->hashedAssetsArray[$this->hashedFileOptionKey]['dir'] : path_join($this->hashedFilesRootDir, $this->hashedFileName);
        $this->hashedFileDir = apply_filters('wp_statistics_hashed_asset_dir', $this->hashedFileDir, $this->hashedFilesRootDir, $this->hashedFileName);
    }

    private function generateShortHash($input)
    {
        $hash = wp_hash($input);
        return substr($hash, 0, 10);
    }

    /**
     * Obfuscate/Randomize file name.
     *
     * @return  void
     */
    private function obfuscateFileName()
    {
        // Return if the hashed file for this version exists
        if ($this->isHashedFileExists()) return;

        // Delete old file
        $this->deleteHashedFile($this->hashedAssetsArray, $this->hashedFileOptionKey);

        // Copy and randomize the name of the input file
        if (!copy($this->inputFileDir, $this->getHashedFileDir())) {
            \WP_Statistics::log("Unable to copy hashed file to {$this->getHashedFileDir()}!", 'warning');
            return;
        }

        $this->hashedAssetsArray[$this->hashedFileOptionKey]['version'] = WP_STATISTICS_VERSION;
        $this->hashedAssetsArray[$this->hashedFileOptionKey]['dir']     = $this->getHashedFileDir();
        Option::saveOptionGroup($this->hashedFileOptionKey, $this->hashedAssetsArray[$this->hashedFileOptionKey], $this->optionName);
    }

    /**
     * Checks to see if a hashed/randomized file for this version already exists or not.
     *
     * @return  bool
     */
    private function isHashedFileExists()
    {
        return $this->hashedAssetsArray[$this->hashedFileOptionKey]['version'] === WP_STATISTICS_VERSION &&
            !empty($this->hashedAssetsArray[$this->hashedFileOptionKey]['dir']) &&
            file_exists($this->hashedAssetsArray[$this->hashedFileOptionKey]['dir']);
    }

    /**
     * Returns hashed file name.
     *
     * @return  string
     */
    public function getHashedFileName()
    {
        return $this->hashedFileName;
    }

    /**
     * Returns hashed files root dir.
     *
     * @return  string
     */
    public function getHashedFilesRootDir()
    {
        return $this->hashedFilesRootDir;
    }

    /**
     * Returns full path (DIR) of the hashed file.
     *
     * @return  string
     */
    public function getHashedFileDir()
    {
        return $this->hashedFileDir;
    }

    /**
     * Returns full URL of the hashed file.
     *
     * @return  string
     */
    public function getHashedFileUrl()
    {
        return Helper::dirToUrl($this->hashedFileDir);
    }

    /**
     * Deletes a hashed file.
     *
     * @param   array   $assetsArray    All hashed files.
     * @param   string  $key            Hashed file's key (which is its path relative to `WP_STATISTICS_DIR`).
     *
     * @return  void
     */
    private function deleteHashedFile($assetsArray, $key)
    {
        if (!empty($assetsArray[$key]) && !empty($assetsArray[$key]['dir']) && file_exists($assetsArray[$key]['dir'])) {
            unlink($assetsArray[$key]['dir']);
        }
    }

    /**
     * Deletes all hashed files.
     *
     * @return  void
     */
    public function deleteAllHashedFiles()
    {
        // Method was called from uninstall probably, initialize the array again
        $hashedAssetsArray = Option::getOptionGroup($this->optionName, null, []);

        foreach ($hashedAssetsArray as $key => $asset) {
            $this->deleteHashedFile($hashedAssetsArray, $key);
        }
    }

    /**
     * Deletes `wp_statistics_hashed_assets` option from the database.
     *
     * @return  void
     */
    public function deleteDatabaseOption()
    {
        delete_option('wp_statistics_hashed_assets');
    }
}
