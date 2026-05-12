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
     * Field names inside each option entry.
     */
    private const KEY_VERSION = 'version';
    private const KEY_DIR     = 'dir';
    private const KEY_NAME    = 'name';

    /**
     * Option that contains information about all hashed files.
     *
     * @var string
     */
    private $optionName = 'hashed_assets';

    /**
     * Memoized result of Helper::get_uploads_dir() for this request.
     *
     * @var string
     */
    private $uploadsDir = '';

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
     * @param string $file Full path of the input file.
     * Pass `null` if you only want to use `deleteAllHashedFiles` and `deleteDatabaseOption` methods. (e.g. When uninstalling the plugin)
     *
     * @return  void
     */
    public function __construct($file = null)
    {
        $this->inputFileDir = !empty($file) ? wp_normalize_path($file) : '';

        if (defined('WP_STATISTICS_MAIN_FILE')) {
            $this->pluginsRoot = wp_normalize_path(plugin_dir_path(WP_STATISTICS_MAIN_FILE));
        } elseif (defined('WP_STATISTICS_DIR')) {
            $this->pluginsRoot = wp_normalize_path(WP_STATISTICS_DIR . DIRECTORY_SEPARATOR);
        } else {
            $this->pluginsRoot = wp_normalize_path(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR);
        }

        if ($this->inputFileDir && !is_file($this->inputFileDir)) {
            $this->inputFileDir = path_join($this->pluginsRoot, $this->inputFileDir);
        }

        if (!$this->inputFileDir || !is_file($this->inputFileDir)) {
            return;
        }

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
            $this->hashedAssetsArray[$this->hashedFileOptionKey] = [
                self::KEY_VERSION => WP_STATISTICS_VERSION,
            ];
        }

        $this->hashedFileName = $this->generateShortHash(WP_STATISTICS_VERSION . $this->hashedFileOptionKey);
        $this->hashedFileName .= '.' . pathinfo($this->inputFileDir, PATHINFO_EXTENSION);
        $this->hashedFileName = $this->cleanHashedFileName($this->hashedFileName);
        $this->hashedFileName = apply_filters('wp_statistics_hashed_asset_name', $this->hashedFileName, $this->inputFileDir);

        $this->uploadsDir = Helper::get_uploads_dir();

        // Fire deprecated filters only when something is listening, so we
        // skip arg construction (and the global no-op) on every other call.
        if (has_filter('wp_statistics_hashed_asset_root')) {
            apply_filters_deprecated('wp_statistics_hashed_asset_root', [$this->uploadsDir], '14.16.7', '', 'The obfuscator no longer writes a copy of the tracker into wp-content/uploads/.');
        }

        if (has_filter('wp_statistics_hashed_asset_dir')) {
            apply_filters_deprecated('wp_statistics_hashed_asset_dir', [path_join($this->uploadsDir, $this->hashedFileName), $this->uploadsDir, $this->hashedFileName], '14.16.7', '', 'The obfuscator no longer writes a copy of the tracker into wp-content/uploads/.');
        }
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
     * Records the original-path -> hashed-name mapping in the option.
     */
    private function obfuscateFileName()
    {
        if ($this->isHashedFileExists()) return;

        // Clean up any legacy uploads/<hash>.js copy from before the v3 refactor.
        $this->deleteHashedFile($this->hashedAssetsArray, $this->hashedFileOptionKey);

        $this->hashedAssetsArray[$this->hashedFileOptionKey][self::KEY_VERSION] = WP_STATISTICS_VERSION;
        $this->hashedAssetsArray[$this->hashedFileOptionKey][self::KEY_DIR]     = $this->inputFileDir;
        $this->hashedAssetsArray[$this->hashedFileOptionKey][self::KEY_NAME]    = $this->hashedFileName;
        Option::saveOptionGroup($this->hashedFileOptionKey, $this->hashedAssetsArray[$this->hashedFileOptionKey], $this->optionName);
    }

    /**
     * Checks to see if a hashed/randomized file for this version already exists or not.
     *
     * @return  bool
     */
    private function isHashedFileExists()
    {
        $entry = $this->hashedAssetsArray[$this->hashedFileOptionKey] ?? [];

        if (($entry[self::KEY_VERSION] ?? null) !== WP_STATISTICS_VERSION) {
            return false;
        }

        $dir = $entry[self::KEY_DIR] ?? '';

        if (empty($dir) || !file_exists($dir)) {
            return false;
        }

        // Force regeneration of pre-refactor entries that still point into uploads/.
        return !$this->isLegacyUploadsPath($dir);
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
        $dir = $assetsArray[$key][self::KEY_DIR] ?? '';

        // Only unlink files we created in the legacy uploads/ location.
        // The current 'dir' stores the original plugin file path, which must never be removed.
        if (empty($dir) || !file_exists($dir) || !$this->isLegacyUploadsPath($dir)) {
            return;
        }

        unlink($dir);
    }

    /**
     * Whether the given path is inside the legacy uploads/ root used by the
     * v1 obfuscator. Anything outside it is treated as belonging to the
     * plugin and never written or deleted by this class.
     */
    private function isLegacyUploadsPath($path)
    {
        if (empty($this->uploadsDir)) {
            $this->uploadsDir = Helper::get_uploads_dir();
        }

        $uploadsRoot = wp_normalize_path(untrailingslashit($this->uploadsDir)) . '/';
        $normalized  = wp_normalize_path($path);

        return strpos($normalized, $uploadsRoot) === 0;
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
        if (empty($hashedAssetsArray)) {
            return null;
        }

        foreach ($hashedAssetsArray as $originalKey => $info) {
            // Fast path: post-refactor entries store the hashed name explicitly
            // and 'dir' is the original plugin file (always present on disk).
            if (isset($info[self::KEY_NAME]) && $info[self::KEY_NAME] === $hashedFileName) {
                if (!empty($info[self::KEY_DIR]) && file_exists($info[self::KEY_DIR])) {
                    return $info[self::KEY_DIR];
                }
            }

            // Legacy entries stored the hashed name as basename of an uploads/
            // copy that may now be gone. Recover by resolving the original
            // plugin file from the option key.
            if (empty($info[self::KEY_DIR]) || basename($info[self::KEY_DIR]) !== $hashedFileName) {
                continue;
            }

            foreach ($this->legacyFallbackCandidates($originalKey, $info[self::KEY_DIR]) as $candidate) {
                if (file_exists($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * @param string $originalKey
     * @param string $dir
     * @return string[]
     */
    private function legacyFallbackCandidates($originalKey, $dir)
    {
        $candidates = [$dir];

        if (!empty($originalKey)) {
            $candidates[] = $originalKey;
            if (!empty($this->pluginsRoot)) {
                $candidates[] = path_join($this->pluginsRoot, $originalKey);
            }
        }

        return $candidates;
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