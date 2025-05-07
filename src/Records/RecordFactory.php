<?php

namespace WP_Statistics\Records;

/**
 * Factory class to create record instances tied to database tables.
 */
class RecordFactory
{
    /**
     * Create a new ParameterRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return ParameterRecord
     */
    public static function parameter($record = null)
    {
        return new ParameterRecord($record);
    }

    /**
     * Create a new ResourceRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return ResourceRecord
     */
    public static function resource($record = null)
    {
        return new ResourceRecord($record);
    }

    /**
     * Create a new ViewRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return ViewRecord
     */
    public static function view($record = null)
    {
        return new ViewRecord($record);
    }

    /**
     * Create a new CountryRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return CountryRecord
     */
    public static function country($record = null)
    {
        return new CountryRecord($record);
    }

    /**
     * Create a new CityRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return CityRecord
     */
    public static function city($record = null)
    {
        return new CityRecord($record);
    }

    /**
     * Create a new DeviceTypeRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return DeviceTypeRecord
     */
    public static function deviceType($record = null)
    {
        return new DeviceTypeRecord($record);
    }

    /**
     * Create a new DeviceBrowserVersionRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return DeviceBrowserVersionRecord
     */
    public static function deviceBrowserVersion($record = null)
    {
        return new DeviceBrowserVersionRecord($record);
    }

    /**
     * Create a new DeviceBrowserRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return DeviceBrowserRecord
     */
    public static function deviceBrowser($record = null)
    {
        return new DeviceBrowserRecord($record);
    }

    /**
     * Create a new DeviceOsRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return DeviceOsRecord
     */
    public static function deviceOs($record = null)
    {
        return new DeviceOsRecord($record);
    }

    /**
     * Create a new ResolutionRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return ResolutionRecord
     */
    public static function resolution($record = null)
    {
        return new ResolutionRecord($record);
    }

    /**
     * Create a new LanguageRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return LanguageRecord
     */
    public static function language($record = null)
    {
        return new LanguageRecord($record);
    }

    /**
     * Create a new TimezoneRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return TimezoneRecord
     */
    public static function timezone($record = null)
    {
        return new TimezoneRecord($record);
    }

    /**
     * Create a new ReferrerRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return ReferrerRecord
     */
    public static function referrer($record = null)
    {
        return new ReferrerRecord($record);
    }

    /**
     * Create a new VisitorRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return VisitorRecord
     */
    public static function visitor($record = null)
    {
        return new VisitorRecord($record);
    }

    /**
     * Create a new SessionRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return SessionRecord
     */
    public static function session($record = null)
    {
        return new SessionRecord($record);
    }

    /**
     * Create a new ReportRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return ReportRecord
     */
    public static function report($record = null)
    {
        return new ReportRecord($record);
    }

    /**
     * Create a new SummaryRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return SummaryRecord
     */
    public static function summary($record = null)
    {
        return new SummaryRecord($record);
    }

    /**
     * Create a new SummaryTotalsRecord instance.
     *
     * @param object|null $record Optional existing record to wrap.
     * @return SummaryTotalRecord
     */
    public static function summaryTotals($record = null)
    {
        return new SummaryTotalRecord($record);
    }
}
