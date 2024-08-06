<?php

namespace WP_Statistics\Components;

use WP_Statistics\Exception\SystemErrorException;

class View
{
    /**
     * Load a view file and pass data to it.
     *
     * @param string|array $view The view path inside views directory
     * @param array $args An associative array of data to pass to the view.
     * @throws Exception if the view file cannot be found.
     */
    public static function load($view, $args = [], $return = false)
    {
        try {
            $viewList = is_array($view) ? $view : [$view];

            foreach ($viewList as $view) {
                $viewPath = WP_STATISTICS_DIR . '/views/' . $view . '.php';

                if (!file_exists($viewPath)) {
                    throw new SystemErrorException(esc_html__("View file not found: {$viewPath}", 'wp-statistics'));
                }

                if (!empty($args)) {
                    extract($args);
                }

                // Return the template if requested
                if ($return) {
                    ob_start();
                    include $viewPath;
                    return ob_get_clean();
                }

                include $viewPath;
            }
        } catch (\Exception $e) {
            \WP_Statistics::log($e->getMessage(), 'error');
        }
    }

}
