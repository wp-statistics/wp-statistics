<?php

namespace WP_Statistics\Components;

use Exception;
use WP_Statistics\Exception\SystemErrorException;

class View
{
    /**
     * Load a view file and pass data to it.
     *
     * @param string|array $view The view path inside views directory
     * @param array $args An associative array of data to pass to the view.
     * @param bool $return Return the template if requested
     * @param string $baseDir The base directory to load the view, defaults to WP_STATISTICS_DIR
     *
     * @throws Exception if the view file cannot be found.
     */
    public static function load($view, $args = [], $return = false, $baseDir = null)
    {
        // Default to WP_STATISTICS_DIR
        $baseDir = empty($baseDir) ? WP_STATISTICS_DIR : $baseDir;

        try {
            $viewList = is_array($view) ? $view : [$view];

            foreach ($viewList as $view) {
                $viewPath = "$baseDir/views/$view.php";

                if (!file_exists($viewPath)) {
                    throw new SystemErrorException(esc_html__("View file not found: {$viewPath}", 'wp-statistics'));
                }

                if ($return) {
                    return self::renderFile($viewPath, $args);
                }

                if (!empty($args)) {
                    extract($args);
                }

                include $viewPath;
            }
        } catch (Exception $e) {
            \WP_Statistics()->log($e->getMessage(), 'error');
        }
    }

    /**
     * Render an absolute file path with variables and return the output.
     *
     * @param string $file Absolute path to a PHP template.
     * @param array  $args Variables to extract into the template scope.
     * @return string Rendered output, or empty string if the file does not exist.
     */
    public static function renderFile($file, $args = [])
    {
        if (!file_exists($file)) {
            return '';
        }

        ob_start();
        if (!empty($args)) {
            extract($args, EXTR_SKIP);
        }
        include $file;
        return ob_get_clean();
    }

}
