<?php

namespace WP_Statistics\Components;

/**
 * Handles template loading and rendering functionality
 *
 * Provides a lightweight template loader for WordPress admin pages.
 * This class handles the rendering of template files, including the
 * extraction of variables and the inclusion or return of rendered markup.
 *
 * @package WP_Statistics\Components
 * @since   15.0.0
 */
class Template
{
    /**
     * Locate and render a template file.
     *
     * @param string $template Template slug without the `.php` extension.
     * @param string $base Directory used to look for a *Templates/* folder. Empty string triggers auto‑discovery.
     * @param array $args Associative array extracted into the template scope (`extract($args, EXTR_SKIP)`).
     * @param bool $return `true` to return rendered markup, `false` to echo it.
     *
     * @return string|void Rendered HTML when `$return` is true, otherwise void.
     */
    public static function get($template, $base = '', $args = [], $return = false)
    {
        if (!empty($args)) {
            extract($args);
        }

        // Use v15 service templates when base is provided
        if (!empty($base)) {
            $file = self::getPath($template, $base);
        } else {
            // Fallback to views directory
            $file = WP_STATISTICS_DIR . "views/{$template}.php";
        }

        if (!file_exists($file)) {
            return;
        }

        if ($return) {
            ob_start();
            require $file;

            return ob_get_clean();
        }

        include $file;
    }

    /**
     * Echo a template directly.
     *
     * @param string $template Template slug without extension.
     * @param string $base Optional directory for service‑specific templates.
     *
     * @return void
     */
    public static function load($template, $base = '')
    {
        $file = self::getPath($template, $base);

        if (empty($file)) {
            return;
        }

        include $file;
    }

    /**
     * Build an absolute file path for the requested template.
     *
     * @param string $template Template slug without extension.
     * @param string $base Service path whose *Templates/* sub‑folder should be checked first.
     *
     * @return string|null Absolute path or null when not found.
     */
    public static function getPath($template, $base = '')
    {
        $file = WP_STATISTICS_DIR . 'src/Service/Admin/' . $base . '/Templates/' . $template . '.php';

        if (!file_exists($file)) {
            return;
        }

        return $file;
    }

    /**
     * Normalise a path for consistent directory separators.
     *
     * @param string $path Raw file path.
     * @return string Normalised path.
     */
    public static function normalizePath($path)
    {
        return wp_normalize_path($path);
    }
}