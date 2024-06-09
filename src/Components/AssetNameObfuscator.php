<?php

namespace WP_Statistics\Components;

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
     * Hashed file's key (which is its path relative to `WP_STATISTICS_DIR`) in options.
     *
     * @var string
     */
    private $hashedFileOptionKey;

    /**
     * @var string
     */
    private $inputFileDir;

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
     * @param   string  $file   Path of the input file relative to the plugin.
     *
     * @return  void
     */
    public function __construct($file)
    {
        $this->inputFileDir = $file;
        if (stripos($this->inputFileDir, WP_STATISTICS_DIR) === false) {
            $this->inputFileDir = WP_STATISTICS_DIR . $this->inputFileDir;
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
        $this->hashedAssetsArray = Option::getOptionGroup($this->optionName, '', []);

        $this->hashedFileOptionKey = str_replace(WP_STATISTICS_DIR, '', $this->inputFileDir);
        if (empty($this->hashedAssetsArray[$this->hashedFileOptionKey])) {
            $this->hashedAssetsArray[$this->hashedFileOptionKey] = [];
            $this->hashedAssetsArray[$this->hashedFileOptionKey]['version'] = WP_STATISTICS_VERSION;
        }

        $this->hashedFileName  = md5(WP_STATISTICS_VERSION . basename($this->inputFileDir));
        $this->hashedFileName .= '.' . pathinfo($this->inputFileDir, PATHINFO_EXTENSION);
        $this->hashedFileName  = apply_filters('wp_statistics_hashed_asset_name', $this->hashedFileName, $this->inputFileDir);

        $this->hashedFilesRootDir = apply_filters('wp_statistics_hashed_asset_root', wp_upload_dir()['basedir']);
        if (!is_dir($this->hashedFilesRootDir)) {
            // Try to make the filtered dir if it not exists
            if (!mkdir($this->hashedFilesRootDir, 0700)) {
                // Revert back to default uploads folder if the filtered dir is invalid
                $this->hashedFilesRootDir = wp_upload_dir()['basedir'];
            }
        }

        $this->hashedFileDir = $this->isHashedFileExists() ?
            $this->hashedAssetsArray[$this->hashedFileOptionKey]['dir'] :
            path_join($this->hashedFilesRootDir, $this->hashedFileName);
        $this->hashedFileDir = apply_filters('wp_statistics_hashed_asset_dir', $this->hashedFileDir, $this->hashedFilesRootDir, $this->hashedFileName);
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
        if (
            !empty($this->hashedAssetsArray[$this->hashedFileOptionKey]['dir']) &&
            file_exists($this->hashedAssetsArray[$this->hashedFileOptionKey]['dir'])
        ) {
            unlink($this->hashedAssetsArray[$this->hashedFileOptionKey]['dir']);
        }

        // Copy and randomize the name of the input file
        if (!copy($this->inputFileDir, $this->getHashedFileDir())) {
            \WP_Statistics::log("Unable to copy hashed file to {$this->getHashedFileDir()}!");
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
            !empty($this->hashedAssetsArray[$this->hashedFileOptionKey]['dir']);
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
     *
     * @source  https://wordpress.stackexchange.com/a/264870/
     */
    public function getHashedFileUrl()
    {
        return esc_url_raw(str_replace(
            wp_normalize_path(untrailingslashit(ABSPATH)),
            site_url(),
            wp_normalize_path($this->hashedFileDir)
        ));
    }

    private function deleteHashedFiles()
    {
        // todo
    }
}