<?php

namespace WP_Statistics\Service\Debugger\Provider;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Debugger\AbstractDebuggerProvider;
use WP_STATISTICS\User;

/**
 * Provider for handling WordPress Statistics plugin options
 *
 * This class is responsible for managing and providing access to various
 * WP Statistics plugin options and settings.
 */
class OptionsProvider extends AbstractDebuggerProvider
{
    /**
     * Stores all WordPress Statistics options from the database
     *
     * @var array
     */
    private $savedOptions = [];

    /**
     * Stores specific formatted options for the debugger
     *
     * @var array
     */
    private $options;

    /**
     * Initialize the provider with necessary options
     */
    public function __construct()
    {
        $this->setSavedOptions();
    }

    /**
     * Sets saved options from WordPress database
     * Only fetches options if they haven't been loaded yet
     */
    private function setSavedOptions()
    {
        if (empty($this->savedOptions)) {
            $this->savedOptions = Option::getOptions();
        }
    }

    /**
     * Retrieves all saved WordPress Statistics options
     *
     * @return array All plugin options from database
     */
    public function getSavedOptions()
    {
        return $this->savedOptions;
    }

    /**
     * Get a specific option value by its index
     *
     * @param string $index The option index to retrieve
     * @param mixed $default Default value if option doesn't exist
     * @return mixed The option value or default if not found
     */
    public function getOption($index, $default = false)
    {
        if (empty($this->savedOptions)) {
            return false;
        }

        return $this->savedOptions[$index] ?? $default;
    }

    /**
     * Retrieves formatted options for debugging
     *
     * @return array Formatted options array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Formats provided values as HTML elements
     * Each value is wrapped in specified HTML tags for display
     *
     * @param array $values Array of values to be formatted
     * @param string $tag HTML tag name to wrap each value
     * @param string $class Element CSS class
     * @return string Formatted HTML string of values or empty string
     */
    public function formatValuesAsHtml($values, $tag, $class = '')
    {
        $values = array_slice($values, 0, 10);
        $html = '';

        foreach ($values as $value) {
            $html .= sprintf(
                '<%1$s class="%2$s">%3$s</%1$s>',
                esc_attr($tag),
                esc_attr($class),
                esc_html($value)
            );
        }

        if (count($values) >= 10) {
            $html .= sprintf(
                '<%1$s class="%2$s">...</%1$s>',
                esc_attr($tag),
                esc_attr($class)
            );
        }

        return wp_kses_post($html);
    }

    /**
     * Gets list of excluded IP addresses from saved options
     *
     * @return array Array of excluded IP addresses, empty if none set
     */
    public function getExcludedIPs()
    {
        return !empty($this->savedOptions['exclude_ip'])
            ? explode("\n", $this->savedOptions['exclude_ip'])
            : [];
    }

    /**
     * Retrieves the list of excluded URLs.
     *
     * @return array An array of excluded URLs.
     */
    public function getExcludedUrls()
    {
        return !empty($this->savedOptions['excluded_urls'])
            ? explode("\n", $this->savedOptions['excluded_urls'])
            : [];
    }

    /**
     * Retrieves the list of excluded user roles.
     *
     * @return array An array of excluded user roles.
     */
    public function getUserRoleExclusions()
    {
        $excludeRoles = [];

        foreach (User::get_role_list() as $role) {
            $optionName = 'exclude_' . str_replace(" ", "_", strtolower($role));

            $translatedRoleName = ($role === 'Anonymous Users') ? __('Anonymous Users', 'wp-statistics') : translate_user_role($role);

            if ($this->getOption($optionName)) {
                $excludeRoles[] = $translatedRoleName;
            }
        }

        return $excludeRoles;
    }

    /**
     * Get array of excluded countries from options.
     * 
     * @return array Array of excluded country codes in uppercase format
     */
    public function getExcludedCountries()
    {
        $countries = $this->getOption('excluded_countries');

        if (empty($countries)) {
            return [];
        }

        $excluded_countries = explode("\n", strtoupper(str_replace("\r\n", "\n", $countries)));
        return array_filter($excluded_countries);
    }

    /**
     * Get array of included countries from options.
     *
     * @return array Array of included country codes in uppercase format
     */
    public function getIncludedCountries()
    {
        $countries = $this->getOption('included_countries');

        if (empty($countries)) {
            return [];
        }

        $included_countries_string = trim(strtoupper(str_replace("\r\n", "\n", $countries)));

        if ($included_countries_string == '') {
            return [];
        }

        $included_countries = explode("\n", $included_countries_string);
        return array_filter($included_countries);
    }
}
