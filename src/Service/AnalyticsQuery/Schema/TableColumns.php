<?php

namespace WP_Statistics\Service\AnalyticsQuery\Schema;

/**
 * Defines the available columns for each table in the analytics query system.
 *
 * This class provides standardized column names and aliases for consistent
 * data access across the analytics query builder.
 *
 * @since 15.0.0
 */
class TableColumns
{
    /**
     * Column definitions for countries table.
     */
    public const COUNTRIES = [
        'country_id'             => 'countries.ID',
        'country_code'           => 'countries.code',
        'country_name'           => 'countries.name',
        'country_continent_code' => 'countries.continent_code',
        'country_continent'      => 'countries.continent',
    ];

    /**
     * Column definitions for cities table.
     */
    public const CITIES = [
        'city_id'          => 'cities.ID',
        'city_country_id'  => 'cities.country_id',
        'city_region_code' => 'cities.region_code',
        'city_region_name' => 'cities.region_name',
        'city_name'        => 'cities.city_name',
    ];

    /**
     * Column definitions for device_types table.
     */
    public const DEVICE_TYPES = [
        'device_type_id'   => 'device_types.ID',
        'device_type_name' => 'device_types.name',
    ];

    /**
     * Column definitions for device_browsers table.
     */
    public const DEVICE_BROWSERS = [
        'browser_id'   => 'device_browsers.ID',
        'browser_name' => 'device_browsers.name',
    ];

    /**
     * Column definitions for device_browser_versions table.
     */
    public const DEVICE_BROWSER_VERSIONS = [
        'browser_version_id'         => 'device_browser_versions.ID',
        'browser_version_browser_id' => 'device_browser_versions.browser_id',
        'browser_version'            => 'device_browser_versions.version',
    ];

    /**
     * Column definitions for device_oss table.
     */
    public const DEVICE_OSS = [
        'os_id'   => 'device_oss.ID',
        'os_name' => 'device_oss.name',
    ];

    /**
     * Column definitions for resolutions table.
     */
    public const RESOLUTIONS = [
        'resolution_id'     => 'resolutions.ID',
        'resolution_width'  => 'resolutions.width',
        'resolution_height' => 'resolutions.height',
    ];

    /**
     * Column definitions for languages table.
     */
    public const LANGUAGES = [
        'language_id'     => 'languages.ID',
        'language_code'   => 'languages.code',
        'language_name'   => 'languages.name',
        'language_region' => 'languages.region',
    ];

    /**
     * Column definitions for timezones table.
     */
    public const TIMEZONES = [
        'timezone_id'     => 'timezones.ID',
        'timezone_name'   => 'timezones.name',
        'timezone_offset' => 'timezones.offset',
        'timezone_is_dst' => 'timezones.is_dst',
    ];

    /**
     * Column definitions for referrers table.
     */
    public const REFERRERS = [
        'referrer_id'      => 'referrers.ID',
        'referrer_channel' => 'referrers.channel',
        'referrer_name'    => 'referrers.name',
        'referrer_domain'  => 'referrers.domain',
    ];

    /**
     * Column definitions for visitors table.
     */
    public const VISITORS = [
        'visitor_id'         => 'visitors.ID',
        'visitor_hash'       => 'visitors.hash',
        'visitor_created_at' => 'visitors.created_at',
    ];

    /**
     * Column definitions for sessions table.
     */
    public const SESSIONS = [
        'session_id'                        => 'sessions.ID',
        'session_visitor_id'                => 'sessions.visitor_id',
        'session_ip'                        => 'sessions.ip',
        'session_referrer_id'               => 'sessions.referrer_id',
        'session_country_id'                => 'sessions.country_id',
        'session_city_id'                   => 'sessions.city_id',
        'session_initial_view_id'           => 'sessions.initial_view_id',
        'session_last_view_id'              => 'sessions.last_view_id',
        'session_total_views'               => 'sessions.total_views',
        'session_device_type_id'            => 'sessions.device_type_id',
        'session_device_os_id'              => 'sessions.device_os_id',
        'session_device_browser_id'         => 'sessions.device_browser_id',
        'session_device_browser_version_id' => 'sessions.device_browser_version_id',
        'session_started_at'                => 'sessions.started_at',
        'session_ended_at'                  => 'sessions.ended_at',
        'session_duration'                  => 'sessions.duration',
        'session_user_id'                   => 'sessions.user_id',
        'session_timezone_id'               => 'sessions.timezone_id',
        'session_language_id'               => 'sessions.language_id',
        'session_resolution_id'             => 'sessions.resolution_id',
    ];

    /**
     * Column definitions for views table.
     */
    public const VIEWS = [
        'view_id'              => 'views.ID',
        'view_session_id'      => 'views.session_id',
        'view_resource_uri_id' => 'views.resource_uri_id',
        'view_resource_id'     => 'views.resource_id',
        'view_viewed_at'       => 'views.viewed_at',
        'view_next_view_id'    => 'views.next_view_id',
        'view_duration'        => 'views.duration',
    ];

    /**
     * Column definitions for resources table.
     */
    public const RESOURCES = [
        'resource_id'               => 'resources.ID',
        'resource_type'             => 'resources.resource_type',
        'resource_wp_id'            => 'resources.resource_id',
        'resource_cached_title'     => 'resources.cached_title',
        'resource_cached_terms'     => 'resources.cached_terms',
        'resource_cached_author_id' => 'resources.cached_author_id',
        'resource_cached_date'      => 'resources.cached_date',
        'resource_meta'             => 'resources.resource_meta',
        'resource_language'         => 'resources.language',
        'resource_is_deleted'       => 'resources.is_deleted',
    ];

    /**
     * Column definitions for resource_uris table.
     */
    public const RESOURCE_URIS = [
        'resource_uri_id'          => 'resource_uris.ID',
        'resource_uri_resource_id' => 'resource_uris.resource_id',
        'resource_uri'             => 'resource_uris.uri',
    ];

    /**
     * Column definitions for parameters table.
     */
    public const PARAMETERS = [
        'parameter_id'              => 'parameters.ID',
        'parameter_session_id'      => 'parameters.session_id',
        'parameter_resource_uri_id' => 'parameters.resource_uri_id',
        'parameter_view_id'         => 'parameters.view_id',
        'parameter_name'            => 'parameters.parameter',
        'parameter_value'           => 'parameters.value',
    ];

    /**
     * Column definitions for summary table.
     */
    public const SUMMARY = [
        'summary_id'              => 'summary.ID',
        'summary_date'            => 'summary.date',
        'summary_resource_uri_id' => 'summary.resource_uri_id',
        'summary_visitors'        => 'summary.visitors',
        'summary_sessions'        => 'summary.sessions',
        'summary_views'           => 'summary.views',
        'summary_total_duration'  => 'summary.total_duration',
        'summary_bounces'         => 'summary.bounces',
    ];

    /**
     * Column definitions for summary_totals table.
     */
    public const SUMMARY_TOTALS = [
        'summary_totals_id'             => 'summary_totals.ID',
        'summary_totals_date'           => 'summary_totals.date',
        'summary_totals_visitors'       => 'summary_totals.visitors',
        'summary_totals_sessions'       => 'summary_totals.sessions',
        'summary_totals_views'          => 'summary_totals.views',
        'summary_totals_total_duration' => 'summary_totals.total_duration',
        'summary_totals_bounces'        => 'summary_totals.bounces',
    ];

    /**
     * Column definitions for events table.
     */
    public const EVENTS = [
        'event_id'              => 'events.ID',
        'event_date'            => 'events.date',
        'event_resource_uri_id' => 'events.resource_uri_id',
        'event_session_id'      => 'events.session_id',
        'event_user_id'         => 'events.user_id',
        'event_name'            => 'events.event_name',
        'event_data'            => 'events.event_data',
    ];

    /**
     * Column definitions for exclusions table.
     */
    public const EXCLUSIONS = [
        'exclusion_id'     => 'exclusions.ID',
        'exclusion_date'   => 'exclusions.date',
        'exclusion_reason' => 'exclusions.reason',
        'exclusion_count'  => 'exclusions.count',
    ];

    /**
     * Get columns for a specific table.
     *
     * @param string $table Table name (e.g., 'countries', 'sessions').
     * @return array Array of column definitions [alias => expression].
     */
    public static function getColumns(string $table): array
    {
        $constantName = strtoupper(str_replace('-', '_', $table));
        $fullConstant = self::class . '::' . $constantName;

        if (defined($fullConstant)) {
            return constant($fullConstant);
        }

        return [];
    }

    /**
     * Get a specific column expression by alias.
     *
     * @param string $table Table name.
     * @param string $alias Column alias.
     * @return string|null Column expression or null if not found.
     */
    public static function getColumn(string $table, string $alias): ?string
    {
        $columns = self::getColumns($table);
        return $columns[$alias] ?? null;
    }

    /**
     * Get a column expression with its alias for SELECT.
     *
     * @param string $table Table name.
     * @param string $alias Column alias.
     * @return string|null Column expression with alias or null if not found.
     */
    public static function getColumnWithAlias(string $table, string $alias): ?string
    {
        $expression = self::getColumn($table, $alias);

        if ($expression === null) {
            return null;
        }

        return $expression . ' AS ' . $alias;
    }

    /**
     * Get multiple columns with aliases for SELECT.
     *
     * @param string $table  Table name.
     * @param array  $aliases Array of column aliases.
     * @return array Array of column expressions with aliases.
     */
    public static function getColumnsWithAliases(string $table, array $aliases): array
    {
        $result = [];

        foreach ($aliases as $alias) {
            $column = self::getColumnWithAlias($table, $alias);
            if ($column !== null) {
                $result[] = $column;
            }
        }

        return $result;
    }

    /**
     * Get all column aliases for a table.
     *
     * @param string $table Table name.
     * @return array Array of column aliases.
     */
    public static function getAliases(string $table): array
    {
        return array_keys(self::getColumns($table));
    }
}
