<?php

namespace WP_Statistics\Components;

use WP_Statistics;
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
     * @param string $file Full path of the input file.
     * Pass `null` if you only want to use `deleteAllHashedFiles` and `deleteDatabaseOption` methods. (e.g. When uninstalling the plugin)
     *
     * @return  void
     */
    public function __construct($file = null)
    {
        // Handle slashes
        $this->inputFileDir = !empty($file) ? wp_normalize_path($file) : '';
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
        $this->hashedFileName     = $this->cleanHashedFileName($this->hashedFileName);
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

    /**
     * Generates a truncated MD5 hash of the input string.
     *
     * @param string $input The input string to be hashed.
     * @param int $length The length of the truncated hash.
     * @return string The truncated MD5 hash.
     */
    private function generateShortHash($input, $length = 10)
    {
        $hash = wp_hash($input);
        return substr($hash, 0, $length);
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
            WP_Statistics::log("Unable to copy hashed file to {$this->getHashedFileDir()}!", 'warning');
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
        return Helper::get_upload_url() . '/' . $this->hashedFileName;
    }

    /**
     * Generates a dynamic query parameter based on the hashed domain URL.
     * This helps to avoid conflicts with other plugins and prevents ad-blocking issues.
     *
     * @return string The dynamic query parameter.
     */
    public function getDynamicAssetKey()
    {
        return $this->generateShortHash(home_url(), 6);
    }

    /**
     * Generates a URL to serve the asset through a proxy.
     *
     * @return string
     */
    public function getUrlThroughProxy()
    {
        return esc_url(home_url('?' . $this->getDynamicAssetKey() . '=' . $this->hashedFileName));
    }

    /**
     * Deletes a hashed file.
     *
     * @param array $assetsArray All hashed files.
     * @param string $key Hashed file's key (which is its path relative to `WP_STATISTICS_DIR`).
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

    /**
     * Proxies requested asset files through PHP to serve them securely.
     *
     * @param string $asset
     *
     * @return void
     */
    public function serveAssetByHash($asset)
    {
        $asset             = $this->cleanHashedFileName($asset);
        $hashedAssetsArray = Option::getOptionGroup($this->optionName, null, []);
        $originalFilePath  = $this->getHashedAssetPath($asset, $hashedAssetsArray);

        if ($originalFilePath && file_exists($originalFilePath)) {
            $extension   = pathinfo($originalFilePath, PATHINFO_EXTENSION);
            $mimeTypes   = [
                'js'  => 'application/javascript',
                'css' => 'text/css',
            ];
            $contentType = isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : 'application/octet-stream';

            header("Content-Type: $contentType");
            header('Cache-Control: public, max-age=86400');

            readfile($originalFilePath);

            exit();
        } else {
            wp_die(__('File not found.', 'wp-statistics'), __('404 Not Found', 'wp-statistics'), array('response' => 404));
        }
    }

    /**
     * Retrieves the original file path based on a hashed file name.
     *
     * @param string $hashedFileName
     *
     * @param array $hashedAssetsArray
     *
     * @return string|null
     */
    private function getHashedAssetPath($hashedFileName, $hashedAssetsArray)
    {
        if (!empty($hashedAssetsArray)) {
            foreach ($hashedAssetsArray as $originalPath => $info) {
                if (isset($info['dir']) && basename($info['dir']) === $hashedFileName) {
                    return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $originalPath;
                }
            }
        }

        return null;
    }

    /**
     * Clean the file name by removing any extra data
     *
     * @param string $hashedFileName
     *
     * @return string
     */
    private function cleanHashedFileName($hashedFileName)
    {
        $posJs = strpos($hashedFileName, '.js');
        if ($posJs !== false) {
            return substr($hashedFileName, 0, $posJs + 3);
        }

        $posCss = strpos($hashedFileName, '.css');
        if ($posCss !== false) {
            return substr($hashedFileName, 0, $posCss + 4);
        }

        return $hashedFileName;
    }
}
